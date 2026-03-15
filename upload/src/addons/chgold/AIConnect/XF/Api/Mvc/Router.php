<?php

namespace chgold\AIConnect\XF\Api\Mvc;

use XF\Http\Request;

class Router extends XFCP_Router
{
    public function routeToController($path, ?Request $request = null)
    {
        if (preg_match('#^api/ai-connect/v\d+/tools/([^/?]+)#i', urldecode($path), $m)) {
            $toolName = $m[1];
            $match = parent::routeToController('api/aiconnect-tools/', $request);
            if ($match->getController()) {
                $match->setParam('tool_name', $toolName);
            }
            return $match;
        }

        return parent::routeToController($path, $request);
    }
}
