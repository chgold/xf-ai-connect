<?php

namespace chgold\AIConnect\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class InfoPage extends AbstractController
{
    public function actionIndex(ParameterBag $params)
    {
        $options    = \XF::options();
        $baseUrl    = rtrim(\XF::app()->options()->boardUrl ?? '', '/');
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
