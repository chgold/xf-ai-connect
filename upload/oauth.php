<?php

$fileDir = __DIR__;
require $fileDir . '/src/XF.php';
XF::start($fileDir);

$app = XF::setupApp('XF\Pub\App');
$app->start();

$request = $app->request();
$visitor = \XF::visitor();

function aiConnectError(string $phraseKey, int $httpCode = 400): void
{
    $message = \XF::phrase($phraseKey)->render();
    http_response_code($httpCode);
    header('Content-Type: text/html; charset=UTF-8');
    echo '<!DOCTYPE html><html><head><meta charset="UTF-8"><title>Error</title>'
        . '<style>body{font-family:sans-serif;padding:40px;color:#333}'
        . 'p{font-size:16px}</style></head>'
        . '<body><p>' . htmlspecialchars($message) . '</p></body></html>';
    exit;
}

$clientId = $request->filter('client_id', 'str');
$redirectUri = $request->filter('redirect_uri', 'str');
$responseType = $request->filter('response_type', 'str');
$scope = $request->filter('scope', 'str');
$state = $request->filter('state', 'str');
$codeChallenge = $request->filter('code_challenge', 'str');
$codeChallengeMethod = $request->filter('code_challenge_method', 'str');

if ($responseType !== 'code') {
    aiConnectError('unsupported_response_type');
}

if (empty($clientId) || empty($redirectUri) || empty($codeChallenge)) {
    aiConnectError('aiconnect_error_missing_required_params');
}

if ($codeChallengeMethod !== 'S256') {
    aiConnectError('pkce_required');
}

$oauthServer = \XF::service('chgold\AIConnect:OAuthServer');

if (!$oauthServer->validateClient($clientId)) {
    aiConnectError('invalid_client');
}

if (!$oauthServer->validateRedirectUri($clientId, $redirectUri)) {
    aiConnectError('invalid_redirect_uri');
}

$scopes = !empty($scope) ? explode(' ', $scope) : ['read'];

if (!$oauthServer->validateScopes($clientId, $scopes)) {
    aiConnectError('invalid_scope');
}

if ($request->isPost()) {
    $approve = $request->filter('approve', 'bool');
    $deny = $request->filter('deny', 'bool');

    if (!$visitor->user_id) {
        aiConnectError('aiconnect_error_not_logged_in', 403);
    }

    $submittedToken = $request->filter('_xfToken', 'str');

    if (!$submittedToken) {
        aiConnectError('aiconnect_error_csrf_missing', 403);
    }

    $parts = explode(',', $submittedToken);
    if (count($parts) != 2) {
        aiConnectError('aiconnect_error_csrf_invalid', 403);
    }

    list($tokenTime, $tokenValue) = $parts;

    $csrfCookie = $request->getCookie('csrf');
    if (!$csrfCookie) {
        aiConnectError('aiconnect_error_csrf_missing', 403);
    }

    $csrfValidator = \XF::app()->container('csrf.validator');
    $expectedValue = $csrfValidator($csrfCookie, $tokenTime);

    if ($expectedValue !== $tokenValue) {
        aiConnectError('aiconnect_error_csrf_invalid', 403);
    }

    if ($deny) {
        if ($redirectUri === 'urn:ietf:wg:oauth:2.0:oob') {
            aiConnectError('authorization_denied');
        }

        $redirectUrl = $redirectUri . (strpos($redirectUri, '?') !== false ? '&' : '?')
            . 'error=access_denied'
            . '&error_description=' . urlencode('User denied authorization')
            . '&state=' . urlencode($state);

        header('Location: ' . $redirectUrl);
        exit;
    }

    if ($approve) {
        $code = $oauthServer->createAuthorizationCode(
            $clientId,
            $visitor->user_id,
            $redirectUri,
            $codeChallenge,
            $codeChallengeMethod,
            $scopes
        );

        if ($redirectUri === 'urn:ietf:wg:oauth:2.0:oob') {
            echo $app->templater()->renderTemplate('public:aiconnect_oauth_oob_code', [
                'code' => $code,
                'forumTitle' => \XF::options()->boardTitle,
            ]);
            exit;
        }

        $redirectUrl = $redirectUri . (strpos($redirectUri, '?') !== false ? '&' : '?')
            . 'code=' . urlencode($code)
            . '&state=' . urlencode($state);

        header('Location: ' . $redirectUrl);
        exit;
    }
}

if (!$visitor->user_id) {
    header('Location: ' . $app->router('public')->buildLink('login', null, [
        '_xfRedirect' => $request->getFullRequestUri()
    ]));
    exit;
}

$client = $oauthServer->getClient($clientId);
$forumTitle = \XF::options()->boardTitle;
$csrfToken = \XF::app()->container('csrf.token');

$scopeLabels = [];
foreach ($scopes as $scopeName) {
    $scopeLabels[$scopeName] = \XF::phrase('aiconnect_scope_' . $scopeName)->render();
}

echo $app->templater()->renderTemplate('public:aiconnect_oauth_consent', [
    'client'              => $client,
    'clientId'            => $clientId,
    'redirectUri'         => $redirectUri,
    'responseType'        => $responseType,
    'scope'               => $scope,
    'state'               => $state,
    'codeChallenge'       => $codeChallenge,
    'codeChallengeMethod' => $codeChallengeMethod,
    'scopeLabels'         => $scopeLabels,
    'forumTitle'          => $forumTitle,
    'csrfToken'           => $csrfToken,
]);
