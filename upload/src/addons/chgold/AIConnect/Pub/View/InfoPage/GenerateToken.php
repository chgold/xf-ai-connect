<?php

namespace chgold\AIConnect\Pub\View\InfoPage;

use XF\Mvc\View;

class GenerateToken extends View
{
    public function renderJson()
    {
        return $this->params;
    }

    public function renderHtml()
    {
        $this->response->header('content-type', 'application/json; charset=utf-8');
        return json_encode($this->params);
    }
}
