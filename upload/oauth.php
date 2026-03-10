<?php

$fileDir = __DIR__;
require $fileDir . '/src/XF.php';
XF::start($fileDir);

$app = XF::setupApp('XF\Pub\App');
$app->start();

$request = $app->request();
$visitor = \XF::visitor();

$clientId = $request->filter('client_id', 'str');
$redirectUri = $request->filter('redirect_uri', 'str');
$responseType = $request->filter('response_type', 'str');
$scope = $request->filter('scope', 'str');
$state = $request->filter('state', 'str');
$codeChallenge = $request->filter('code_challenge', 'str');
$codeChallengeMethod = $request->filter('code_challenge_method', 'str');

if ($responseType !== 'code') {
    die('Error: Only authorization code flow is supported');
}

if (empty($clientId) || empty($redirectUri) || empty($codeChallenge)) {
    die('Error: Missing required OAuth parameters');
}

if ($codeChallengeMethod !== 'S256') {
    die('Error: PKCE required with S256 method');
}

$oauthServer = \XF::service('chgold\AIConnect:OAuthServer');

if (!$oauthServer->validateClient($clientId)) {
    die('Error: Invalid client_id');
}

if (!$oauthServer->validateRedirectUri($clientId, $redirectUri)) {
    die('Error: Invalid redirect_uri');
}

$scopes = !empty($scope) ? explode(' ', $scope) : ['read'];

if (!$oauthServer->validateScopes($clientId, $scopes)) {
    die('Error: Invalid scope requested');
}

if ($request->isPost()) {
    $approve = $request->filter('approve', 'bool');
    $deny = $request->filter('deny', 'bool');
    
    if (!$visitor->user_id) {
        die('Error: Not logged in');
    }
    
    $submittedToken = $request->filter('_xfToken', 'str');
    
    if (!$submittedToken) {
        die('Error: CSRF token missing');
    }
    
    $parts = explode(',', $submittedToken);
    if (count($parts) != 2) {
        die('Error: Invalid CSRF token format');
    }
    
    list($tokenTime, $tokenValue) = $parts;
    
    $csrfCookie = $request->getCookie('csrf');
    if (!$csrfCookie) {
        die('Error: CSRF cookie missing');
    }
    
    $csrfValidator = \XF::app()->container('csrf.validator');
    $expectedValue = $csrfValidator($csrfCookie, $tokenTime);
    
    if ($expectedValue !== $tokenValue) {
        die('Error: Invalid CSRF token');
    }
    
    if ($deny) {
        if ($redirectUri === 'urn:ietf:wg:oauth:2.0:oob') {
            die('Authorization denied');
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
            $forumTitle = \XF::options()->boardTitle;
            ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorization Code - <?php echo htmlspecialchars($forumTitle); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        h1 { font-size: 24px; color: #1a1a1a; margin-bottom: 20px; }
        .code-box {
            background: #f9f9f9;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
            font-family: 'Courier New', monospace;
            font-size: 14px;
            word-break: break-all;
            user-select: all;
            color: #28a745;
        }
        .btn {
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn:hover { background: #0056b3; }
    </style>
</head>
<body>
    <div class="container">
        <h1>✓ Authorization Approved</h1>
        <p>Your authorization code:</p>
        <div class="code-box" id="code"><?php echo htmlspecialchars($code); ?></div>
        <button class="btn" onclick="navigator.clipboard.writeText('<?php echo htmlspecialchars($code); ?>'); this.textContent='✓ Copied!';">Copy Code</button>
        <p style="color: #666; font-size: 12px; margin-top: 20px;">Return to your application and paste this code</p>
    </div>
</body>
</html>
            <?php
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

$scopeLabels = [
    'read' => 'Read forum content and your profile',
    'write' => 'Create and modify content',
    'delete' => 'Delete content',
    'admin' => 'Administrative access'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorization Request - <?php echo htmlspecialchars($forumTitle); ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .consent-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .consent-header { text-align: center; margin-bottom: 30px; }
        .consent-header h1 { font-size: 24px; color: #1a1a1a; margin-bottom: 8px; }
        .consent-header p { color: #666; font-size: 14px; }
        .client-info {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .client-name {
            font-size: 20px;
            font-weight: 600;
            color: #1a1a1a;
            margin-bottom: 4px;
        }
        .scope-list {
            list-style: none;
            margin: 20px 0;
        }
        .scope-item {
            display: flex;
            align-items: center;
            padding: 12px;
            margin-bottom: 8px;
            background: #f9f9f9;
            border-radius: 6px;
        }
        .scope-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            color: #28a745;
        }
        .button-group {
            display: flex;
            gap: 12px;
            margin-top: 24px;
        }
        .btn {
            flex: 1;
            padding: 14px;
            border: none;
            border-radius: 6px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-authorize {
            background: #007bff;
            color: white;
        }
        .btn-authorize:hover { background: #0056b3; }
        .btn-deny {
            background: #f8f9fa;
            color: #6c757d;
            border: 1px solid #dee2e6;
        }
        .btn-deny:hover { background: #e2e6ea; }
    </style>
</head>
<body>
    <div class="consent-container" style="margin: auto;">
        <div class="consent-header">
            <h1>Authorization Request</h1>
            <p>An application wants to access your account</p>
        </div>

        <div class="client-info">
            <div class="client-name"><?php echo htmlspecialchars($client['client_name']); ?></div>
            <p style="color: #666; font-size: 14px;">wants to access your <?php echo htmlspecialchars($forumTitle); ?> account</p>
        </div>

        <div>
            <h3 style="margin-bottom: 12px; font-size: 16px;">This application will be able to:</h3>
            <ul class="scope-list">
                <?php foreach ($scopes as $scopeName): ?>
                <li class="scope-item">
                    <svg class="scope-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span><?php echo htmlspecialchars($scopeLabels[$scopeName] ?? ucfirst($scopeName)); ?></span>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <form method="POST" action="">
            <input type="hidden" name="_xfToken" value="<?php echo htmlspecialchars($csrfToken); ?>">
            <input type="hidden" name="client_id" value="<?php echo htmlspecialchars($clientId); ?>">
            <input type="hidden" name="redirect_uri" value="<?php echo htmlspecialchars($redirectUri); ?>">
            <input type="hidden" name="response_type" value="<?php echo htmlspecialchars($responseType); ?>">
            <input type="hidden" name="scope" value="<?php echo htmlspecialchars($scope); ?>">
            <input type="hidden" name="state" value="<?php echo htmlspecialchars($state); ?>">
            <input type="hidden" name="code_challenge" value="<?php echo htmlspecialchars($codeChallenge); ?>">
            <input type="hidden" name="code_challenge_method" value="<?php echo htmlspecialchars($codeChallengeMethod); ?>">
            
            <div class="button-group">
                <button type="submit" name="approve" value="1" class="btn btn-authorize">Authorize</button>
                <button type="submit" name="deny" value="1" class="btn btn-deny">Deny</button>
            </div>
        </form>
    </div>
</body>
</html>
