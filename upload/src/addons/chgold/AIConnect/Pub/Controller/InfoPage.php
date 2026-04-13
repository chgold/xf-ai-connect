<?php

namespace chgold\AIConnect\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class InfoPage extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        if (!\XF::visitor()->hasPermission('aiconnect', 'viewAiConnect')) {
            return $this->noPermission();
        }

        $options    = \XF::options();
        $request = $this->request();
        $scheme  = $request->getServer('HTTPS') === 'on' ? 'https' : 'http';
        // HTTP_HOST already contains host:port when a non-standard port is used
        $host    = $request->getServer('HTTP_HOST') ?: $request->getServer('SERVER_NAME');
        $baseUrl = rtrim($scheme . '://' . $host, '/');
        $forumTitle = $options->boardTitle ?? \XF::phrase('untitled');

        $manifestUrl       = $baseUrl . '/api/ai-connect/manifest';
        $authorizeUrl      = $baseUrl . '/oauth.php';
        $infoUrl           = 'https://ai-connect.gold-t.co.il/';

        $viewParams = [
            'forumTitle'    => $forumTitle,
            'baseUrl'       => $baseUrl,
            'manifestUrl'   => $manifestUrl,
            'authorizeUrl'  => $authorizeUrl,
            'infoUrl'       => $infoUrl,
        ];

        return $this->view(
            'chgold\AIConnect:InfoPage',
            'aiconnect_info_page',
            $viewParams
        );
    }

    public function actionGenerateToken(ParameterBag $params)
    {
        $this->assertPostOnly();

        $visitor = \XF::visitor();

        if (!$visitor->user_id) {
            return $this->error(\XF::phrase('you_must_be_logged_in_to_do_that'), 403);
        }

        if (!$visitor->hasPermission('aiconnect', 'useTools')) {
            return $this->error(\XF::phrase('do_not_have_permission'), 403);
        }

        /** @var \chgold\AIConnect\Service\OAuthServer $oauthServer */
        $oauthServer = $this->service('chgold\AIConnect:OAuthServer');

        $token = $oauthServer->createAccessToken(
            'claude-ai',
            $visitor->user_id,
            ['read', 'write']
        );

        return $this->view(
            'chgold\AIConnect:InfoPage\GenerateToken',
            '',
            [
                'access_token' => $token['access_token'],
                'token_type'   => 'Bearer',
                'expires_in'   => $token['expires_in'],
            ]
        );
    }

    public function allowUnauthenticatedAccess($action)
    {
        return strtolower($action) === 'index';
    }
}
