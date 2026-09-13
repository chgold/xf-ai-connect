<?php

namespace chgold\AIConnectPro\Module;

/**
 * Notifications bundle: alerts + unread count for the connected user.
 * 2 tools. Read-only, require 'read' scope.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait ProNotificationsTrait
{
    protected function registerNotificationsTools()
    {
        $this->registerTool('getNotifications', [
            'description' => 'List recent alerts/notifications for the current user '
                . '(replies, reactions, mentions, quotes, etc). Returns up to 50 most recent.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'unread_only' => ['type' => 'boolean', 'description' => 'Return only unread alerts (default false)'],
                    'limit'       => ['type' => 'integer', 'description' => 'Max alerts to return (default 20, max 50)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getUnreadNotificationsCount', [
            'description' => 'Return the count of unread alerts for the current user. '
                . 'Lightweight — no alert content returned.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_getNotifications($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        $unreadOnly = !empty($params['unread_only']);
        $limit      = min(50, max(1, (int) ($params['limit'] ?? 20)));

        /** @var \XF\Repository\UserAlertRepository $repo */
        $repo    = \XF::em()->getRepository('XF:UserAlert');
        $finder  = $repo->findAlertsForUser($visitor->user_id);
        if ($unreadOnly) {
            $finder->where('read_date', 0);
        }
        $finder->limit($limit);

        $alerts = [];
        foreach ($finder->fetch() as $a) {
            $alerts[] = [
                'alert_id'    => (int)    $a->alert_id,
                'sender_id'   => (int)    $a->user_id,
                'sender'      => (string) $a->username,
                'content_type'=> (string) $a->content_type,
                'action'      => (string) $a->action,
                'event_date'  => (int)    $a->event_date,
                'read'        => (bool)   $a->read_date,
            ];
        }

        return $this->success([
            'count'  => count($alerts),
            'alerts' => $alerts,
        ]);
    }

    public function execute_getUnreadNotificationsCount($params)
    {
        $visitor = \XF::visitor();
        if (!$visitor->user_id) {
            return $this->error('no_permission', 'Must be authenticated');
        }

        return $this->success([
            'unread_count' => (int) $visitor->alerts_unread,
        ]);
    }
}
