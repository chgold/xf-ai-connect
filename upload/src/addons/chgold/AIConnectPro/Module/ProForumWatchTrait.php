<?php

namespace chgold\AIConnectPro\Module;

/**
 * Forum Watch bundle: watch/unwatch forums + list watched forums.
 * 3 tools. watchForum/unwatchForum require 'write' scope (state change).
 * listWatchedForums requires 'read' scope only.
 *
 * XF API: ForumWatchRepository::setWatchState(Forum, User, notifyType)
 *   notifyType = null (unwatch) | 'thread' (alert on new threads) | 'message' (alert on new posts)
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProForumWatchTrait
{
    protected function registerForumWatchTools()
    {
        $this->registerTool('watchForum', [
            'description' => 'Watch a forum to receive notifications when new threads (or posts) are created. '
                . 'notify_on: "thread" = alert on new threads (default), "message" = alert on new posts.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id'   => ['type' => 'integer', 'description' => 'Forum node ID to watch'],
                    'notify_on' => [
                        'type' => 'string',
                        'enum' => ['thread', 'message'],
                        'description' => 'Notification trigger: "thread" (new threads, default) or "message" (new posts)',
                    ],
                    'send_email' => ['type' => 'boolean', 'description' => 'Also send email notifications (default false)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('unwatchForum', [
            'description' => 'Stop watching a forum. Idempotent — success if not currently watching.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['node_id'],
                'properties' => [
                    'node_id' => ['type' => 'integer', 'description' => 'Forum node ID to unwatch'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('listWatchedForums', [
            'description' => 'List all forums the current user is watching, with notification preferences '
                . '(notify_on, send_alert, send_email) and forum title.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_watchForum($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $nodeId = (int) $params['node_id'];
        $node   = \XF::em()->find('XF:Node', $nodeId);
        if (!$node || $node->node_type_id !== 'Forum') {
            return $this->error('not_found', "Forum node $nodeId not found");
        }
        $forum = $node->getDataRelationOrDefault();
        if (!$forum || !$node->canView()) {
            return $this->error('not_found', 'Forum not accessible');
        }

        $notifyOn  = ($params['notify_on'] ?? 'thread') === 'message' ? 'message' : 'thread';
        $sendEmail = !empty($params['send_email']);

        /** @var \XF\Repository\ForumWatchRepository $repo */
        $repo = \XF::em()->getRepository('XF:ForumWatch');
        $repo->setWatchState($forum, $visitor, $notifyOn, true, $sendEmail);

        return $this->success([
            'node_id'    => $nodeId,
            'title'      => (string) $node->title,
            'watching'   => true,
            'notify_on'  => $notifyOn,
            'send_email' => $sendEmail,
        ]);
    }

    public function execute_unwatchForum($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $nodeId = (int) $params['node_id'];
        $node   = \XF::em()->find('XF:Node', $nodeId);
        if (!$node || $node->node_type_id !== 'Forum') {
            return $this->error('not_found', "Forum node $nodeId not found");
        }
        $forum = $node->getDataRelationOrDefault();
        if (!$forum) {
            return $this->error('not_found', 'Forum not found');
        }

        /** @var \XF\Repository\ForumWatchRepository $repo */
        $repo = \XF::em()->getRepository('XF:ForumWatch');
        $repo->setWatchState($forum, $visitor, null); // null = unwatch

        return $this->success([
            'node_id'  => $nodeId,
            'title'    => (string) $node->title,
            'watching' => false,
        ]);
    }

    public function execute_listWatchedForums($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $rows = \XF::db()->fetchAll(
            'SELECT fw.node_id, fw.notify_on, fw.send_alert, fw.send_email,
                    n.title
             FROM xf_forum_watch fw
             JOIN xf_node n ON n.node_id = fw.node_id
             WHERE fw.user_id = ?
             ORDER BY n.title ASC',
            [$visitor->user_id]
        );

        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'node_id'    => (int)    $r['node_id'],
                'title'      => (string) $r['title'],
                'notify_on'  => (string) $r['notify_on'],
                'send_alert' => (bool)   $r['send_alert'],
                'send_email' => (bool)   $r['send_email'],
            ];
        }

        return $this->success(['count' => count($out), 'forums' => $out]);
    }
}
