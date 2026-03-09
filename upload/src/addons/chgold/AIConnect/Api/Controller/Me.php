<?php

namespace chgold\AIConnect\Api\Controller;

use XF\Api\Controller\AbstractController;

class Me extends AbstractController
{
    public function actionGet()
    {
        $visitor = \XF::visitor();
        
        if (!$visitor->user_id) {
            return $this->error('Not authenticated', 401);
        }
        
        return $this->apiSuccess([
            'user_id' => $visitor->user_id,
            'username' => $visitor->username,
            'email' => $visitor->email,
            'user_group_id' => $visitor->user_group_id,
            'is_admin' => $visitor->is_admin,
            'is_moderator' => $visitor->is_moderator,
            'is_banned' => $visitor->is_banned,
            'message_count' => $visitor->message_count,
            'trophy_points' => $visitor->trophy_points,
            'register_date' => $visitor->register_date
        ]);
    }
    
    public function allowUnauthenticatedRequest($action)
    {
        return false;
    }
    
    public function assertRequiredApiInput($keys)
    {
        return [];
    }
}
