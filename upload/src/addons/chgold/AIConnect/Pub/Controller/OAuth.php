<?php

namespace chgold\AIConnect\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class OAuth extends AbstractController
{
    protected $oauthServer;
    
    public function __construct(\XF\App $app, \XF\Http\Request $request)
    {
        parent::__construct($app, $request);
        $this->oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
    }
    
    public function actionAuthorize()
    {
        $request = $this->request;
        
        $clientId = $request->filter('client_id', 'str');
        $redirectUri = $request->filter('redirect_uri', 'str');
        $responseType = $request->filter('response_type', 'str');
        $scope = $request->filter('scope', 'str');
        $state = $request->filter('state', 'str');
        $codeChallenge = $request->filter('code_challenge', 'str');
        $codeChallengeMethod = $request->filter('code_challenge_method', 'str');
        
        if ($responseType !== 'code') {
            return $this->error(\XF::phrase('unsupported_response_type'));
        }
        
        if (!$this->oauthServer->validateClient($clientId)) {
            return $this->error(\XF::phrase('invalid_client'));
        }
        
        if (!$this->oauthServer->validateRedirectUri($clientId, $redirectUri)) {
            return $this->error(\XF::phrase('invalid_redirect_uri'));
        }
        
        if (empty($codeChallenge) || $codeChallengeMethod !== 'S256') {
            return $this->error(\XF::phrase('pkce_required'));
        }
        
        $scopes = !empty($scope) ? explode(' ', $scope) : ['read'];
        
        if (!$this->oauthServer->validateScopes($clientId, $scopes)) {
            return $this->error(\XF::phrase('invalid_scope'));
        }
        
        if ($this->isPost()) {
            if ($this->filter('approve', 'bool')) {
                return $this->handleApproval($clientId, $redirectUri, $codeChallenge, $codeChallengeMethod, $scopes, $state);
            } else if ($this->filter('deny', 'bool')) {
                return $this->handleDenial($redirectUri, $state);
            }
        }
        
        return $this->showConsentScreen($clientId, $redirectUri, $responseType, $scope, $state, $codeChallenge, $codeChallengeMethod, $scopes);
    }
    
    protected function handleApproval($clientId, $redirectUri, $codeChallenge, $codeChallengeMethod, array $scopes, $state)
    {
        $visitor = \XF::visitor();
        
        if (!$visitor->user_id) {
            return $this->noPermission();
        }
        
        $this->assertValidCsrfToken($this->filter('_xfToken', 'str'));
        
        $code = $this->oauthServer->createAuthorizationCode(
            $clientId,
            $visitor->user_id,
            $redirectUri,
            $codeChallenge,
            $codeChallengeMethod,
            $scopes
        );
        
        if ($redirectUri === 'urn:ietf:wg:oauth:2.0:oob') {
            $viewParams = [
                'code' => $code
            ];
            return $this->view('chgold\AIConnect:OAuth\OobCode', 'aiconnect_oauth_oob_code', $viewParams);
        }
        
        $redirectUrl = $redirectUri . (strpos($redirectUri, '?') !== false ? '&' : '?') 
            . 'code=' . urlencode($code) 
            . '&state=' . urlencode($state);
        
        return $this->redirect($redirectUrl);
    }
    
    protected function handleDenial($redirectUri, $state)
    {
        $this->assertValidCsrfToken($this->filter('_xfToken', 'str'));
        
        if ($redirectUri === 'urn:ietf:wg:oauth:2.0:oob') {
            return $this->error(\XF::phrase('authorization_denied'));
        }
        
        $redirectUrl = $redirectUri . (strpos($redirectUri, '?') !== false ? '&' : '?') 
            . 'error=access_denied'
            . '&error_description=' . urlencode('User denied authorization')
            . '&state=' . urlencode($state);
        
        return $this->redirect($redirectUrl);
    }
    
    protected function showConsentScreen($clientId, $redirectUri, $responseType, $scope, $state, $codeChallenge, $codeChallengeMethod, array $scopes)
    {
        $visitor = \XF::visitor();
        
        if (!$visitor->user_id) {
            return $this->redirect(
                $this->buildLink('login', null, [
                    '_xfRedirect' => $this->request->getFullRequestUri()
                ])
            );
        }
        
        $client = $this->oauthServer->getClient($clientId);
        
        $viewParams = [
            'client' => $client,
            'clientId' => $clientId,
            'redirectUri' => $redirectUri,
            'responseType' => $responseType,
            'scope' => $scope,
            'state' => $state,
            'codeChallenge' => $codeChallenge,
            'codeChallengeMethod' => $codeChallengeMethod,
            'scopes' => $scopes
        ];
        
        return $this->view('chgold\AIConnect:OAuth\ConsentScreen', 'aiconnect_oauth_consent', $viewParams);
    }
    
    public static function getActivityDetails(array $activities)
    {
        return \XF::phrase('viewing_oauth_authorization');
    }
}
