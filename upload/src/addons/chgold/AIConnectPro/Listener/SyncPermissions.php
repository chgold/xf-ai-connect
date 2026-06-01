<?php

namespace chgold\AIConnectPro\Listener;

class SyncPermissions
{
    public static function aiConnectSyncToolPermissions(array &$toolDefs, array &$packageDefs): void
    {
        $packageDefs['pro'] = [
            'label'         => 'AI Connect — Pro Tools',
            'display_order' => 320,
            'modules'       => [
                'xenforo_pro' => [
                    'getForumList'     => 'Tool: Get forum list',
                    'createThread'     => 'Tool: Create thread',
                    'replyToThread'    => 'Tool: Reply to thread',
                    'editPost'         => 'Tool: Edit post',
                    'sendConversation' => 'Tool: Send conversation',
                ],
            ],
        ];
    }
}
