<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Authorize extends AbstractController
{
    protected $oauthServer;
    
    public function __construct(\XF\App $app, \XF\Mvc\Request $request)
    {
        parent::__construct($app, $request);
        $this->oauthServer = \XF::service('chgold\AIConnect:OAuthServer');
    }
    
    public function actionGet()
    {
        $clientId = $this->filter('client_id', 'str');
        $redirectUri = $this->filter('redirect_uri', 'str');
        $responseType = $this->filter('response_type', 'str');
        $scope = $this->filter('scope', 'str');
        $state = $this->filter('state', 'str');
        $codeChallenge = $this->filter('code_challenge', 'str');
        $codeChallengeMethod = $this->filter('code_challenge_method', 'str');
        
        if ($responseType !== 'code') {
            return $this->error('Unsupported response_type. Only "code" is supported.', 400);
        }
        
        if (!$this->oauthServer->validateClient($clientId)) {
            return $this->error('Invalid client_id', 400);
        }
        
        if (!$this->oauthServer->validateRedirectUri($clientId, $redirectUri)) {
            return $this->error('Invalid redirect_uri', 400);
        }
        
        if (empty($codeChallenge) || $codeChallengeMethod !== 'S256') {
            return $this->error('PKCE required: code_challenge with S256 method', 400);
        }
        
        $scopes = !empty($scope) ? explode(' ', $scope) : ['read'];
        
        if (!$this->oauthServer->validateScopes($clientId, $scopes)) {
            return $this->error('Invalid scope requested', 400);
        }
        
        $visitor = \XF::visitor();
        
        if (!$visitor->user_id) {
            return $this->error('User must be logged in to authorize. Please visit the forum in browser and log in, then try again.', 401);
        }
        
        $client = $this->oauthServer->getClient($clientId);
        
        return $this->apiSuccess([
            'message' => 'Authorization request received. To complete authorization, please approve the request.',
            'client' => [
                'client_id' => $client['client_id'],
                'client_name' => $client['client_name']
            ],
            'scopes' => $scopes,
            'approve_url' => \XF::app()->router('public')->buildLink('canonical:aiconnect-oauth-public/authorize', null, [
                'client_id' => $clientId,
                'redirect_uri' => $redirectUri,
                'response_type' => $responseType,
                'scope' => $scope,
                'state' => $state,
                'code_challenge' => $codeChallenge,
                'code_challenge_method' => $codeChallengeMethod,
                'approve' => 1,
                '_xfToken' => $visitor->csrf_token_page
            ]),
            'note' => 'For now, authorization requires direct database access. Create authorization code with: INSERT INTO xf_ai_connect_oauth_codes ...'
        ]);
    }
    
    public function actionPost()
    {
        $clientId = $this->filter('client_id', 'str');
        $redirectUri = $this->filter('redirect_uri', 'str');
        $codeChallenge = $this->filter('code_challenge', 'str');
        $codeChallengeMethod = $this->filter('code_challenge_method', 'str');
        $scope = $this->filter('scope', 'str');
        $approve = $this->filter('approve', 'bool');
        
        if (!$approve) {
            return $this->error('Authorization denied by user', 403);
        }
        
        $visitor = \XF::visitor();
        
        if (!$visitor->user_id) {
            return $this->error('User must be logged in', 401);
        }
        
        $scopes = !empty($scope) ? explode(' ', $scope) : ['read'];
        
        $code = $this->oauthServer->createAuthorizationCode(
            $clientId,
            $visitor->user_id,
            $redirectUri,
            $codeChallenge,
            $codeChallengeMethod,
            $scopes
        );
        
        return $this->apiSuccess([
            'code' => $code,
            'message' => 'Authorization approved. Use this code to exchange for an access token.'
        ]);
    }
    
    public function allowUnauthenticatedRequest($action)
    {
        return true;
    }
}
