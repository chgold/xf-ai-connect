<?php

namespace chgold\AIConnect\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class InfoPage extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $options    = \XF::options();
        $request    = $this->request();
        $scheme     = $request->getServer('HTTPS') === 'on' ? 'https' : 'http';
        $host       = $request->getServer('HTTP_HOST') ?: $request->getServer('SERVER_NAME');
        $port       = $request->getServer('SERVER_PORT');
        if (($scheme === 'http' && $port == 80) || ($scheme === 'https' && $port == 443) || !$port) {
            $baseUrl = $scheme . '://' . $host;
        } else {
            $baseUrl = $scheme . '://' . $host . ':' . $port;
        }
        $baseUrl    = rtrim($baseUrl, '/');
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

    public function allowUnauthenticatedAccess($action)
    {
        return true;
    }
}
