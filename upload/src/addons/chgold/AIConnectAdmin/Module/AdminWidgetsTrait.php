<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Widgets bundle: list/get/create/edit/delete widgets + list positions.
 * 6 tools, Group A. Widgets are XF-managed JSON config; safe to touch.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminWidgetsTrait
{
    protected function registerWidgetsTools()
    {
        $this->registerTool('listWidgets', [
            'description' => 'List all widgets with their positions and active state. Read-only.',
            'input_schema' => [
                'type' => 'object',
                'properties' => [
                    'position' => ['type' => 'string', 'description' => 'Filter by widget position key (optional)'],
                    'widget_definition_id' => ['type' => 'string', 'description' => 'Filter by widget type (optional)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('getWidget', [
            'description' => 'Get full details of a widget by widget_key.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['widget_key'],
                'properties' => ['widget_key' => ['type' => 'string']],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('createWidget', [
            'description' => 'Create a new widget. Common types: html (freeform), new_threads, new_posts, '
                . 'members_online, forum_statistics, share_page, quick_search.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['widget_key', 'widget_definition_id', 'positions'],
                'properties' => [
                    'widget_key' => ['type' => 'string', 'description' => 'Unique identifier (URL-safe)'],
                    'widget_definition_id' => ['type' => 'string', 'description' => 'Widget type — see listWidgetDefinitions'],
                    'title' => ['type' => 'string'],
                    'positions' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Position keys — see listWidgetPositions',
                    ],
                    'display_order' => ['type' => 'integer'],
                    'active' => ['type' => 'boolean'],
                    'options' => ['type' => 'object', 'description' => 'Type-specific config (varies per widget_definition_id)'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('editWidget', [
            'description' => 'Edit an existing widget by widget_key.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['widget_key'],
                'properties' => [
                    'widget_key' => ['type' => 'string'],
                    'title' => ['type' => 'string'],
                    'positions' => ['type' => 'array', 'items' => ['type' => 'string']],
                    'display_order' => ['type' => 'integer'],
                    'active' => ['type' => 'boolean'],
                    'options' => ['type' => 'object'],
                ],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('deleteWidget', [
            'description' => 'Delete a widget by widget_key.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['widget_key'],
                'properties' => ['widget_key' => ['type' => 'string']],
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('listWidgetPositions', [
            'description' => 'List all widget position keys available in the current templates '
                . '(e.g. forum_list_sidebar, thread_view_sidebar, member_view_sidebar). Read-only.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────────────

    public function execute_listWidgets($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $finder = \XF::finder('XF:Widget')->order('display_order');
        if (!empty($params['widget_definition_id'])) {
            $finder->where('definition_id', (string) $params['widget_definition_id']);
        }

        $filterPos = trim((string) ($params['position'] ?? ''));
        $out = [];
        foreach ($finder->fetch() as $w) {
            $positions = $w->positions ?: [];
            if ($filterPos !== '' && !isset($positions[$filterPos])) continue;
            $out[] = [
                'widget_id'            => (int)    $w->widget_id,
                'widget_key'           => (string) $w->widget_key,
                'widget_definition_id' => (string) $w->definition_id,
                'title'                => (string) $w->title,
                'display_order'        => (int)    $w->display_order,
                'active'               => (bool)   $w->active,
                'positions'            => array_keys($positions),
                'options'              => $w->options ?: new \stdClass(),
            ];
        }
        return $this->success(['count' => count($out), 'widgets' => $out]);
    }

    public function execute_getWidget($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $key = (string) $params['widget_key'];
        $w = \XF::em()->findOne('XF:Widget', ['widget_key' => $key]);
        if (!$w) return $this->error('not_found', "Widget '$key' not found");

        return $this->success([
            'widget_id'            => (int)    $w->widget_id,
            'widget_key'           => (string) $w->widget_key,
            'widget_definition_id' => (string) $w->definition_id,
            'title'                => (string) $w->title,
            'display_order'        => (int)    $w->display_order,
            'active'               => (bool)   $w->active,
            'positions'            => array_keys($w->positions ?: []),
            'options'              => $w->options ?: new \stdClass(),
        ]);
    }

    public function execute_createWidget($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $key = (string) $params['widget_key'];
        $existing = \XF::em()->findOne('XF:Widget', ['widget_key' => $key]);
        if ($existing) {
            return $this->error('validation_failed', "widget_key '$key' already exists");
        }

        $w = \XF::em()->create('XF:Widget');
        $w->widget_key    = $key;
        $w->definition_id = (string) $params['widget_definition_id'];
        $w->title         = (string) ($params['title'] ?? '');
        $w->display_order = (int)    ($params['display_order'] ?? 10);
        $w->active        = (bool)   ($params['active'] ?? true);
        $w->positions     = $this->positionsFromArray((array) $params['positions']);
        $w->options       = (array)  ($params['options'] ?? []);

        if (!$w->save()) {
            return $this->error('validation_failed', implode(' ', $w->getErrors()));
        }
        return $this->success([
            'widget_id'  => $w->widget_id,
            'widget_key' => $w->widget_key,
            'created'    => true,
        ]);
    }

    public function execute_editWidget($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $key = (string) $params['widget_key'];
        $w = \XF::em()->findOne('XF:Widget', ['widget_key' => $key]);
        if (!$w) return $this->error('not_found', "Widget '$key' not found");

        if (isset($params['title']))         $w->title = (string) $params['title'];
        if (isset($params['display_order'])) $w->display_order = (int) $params['display_order'];
        if (isset($params['active']))        $w->active = (bool) $params['active'];
        if (isset($params['positions']))     $w->positions = $this->positionsFromArray((array) $params['positions']);
        if (isset($params['options']))       $w->options = (array) $params['options'];

        if (!$w->save()) {
            return $this->error('validation_failed', implode(' ', $w->getErrors()));
        }
        return $this->success(['widget_key' => $key, 'updated' => true]);
    }

    public function execute_deleteWidget($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $key = (string) $params['widget_key'];
        $w = \XF::em()->findOne('XF:Widget', ['widget_key' => $key]);
        if (!$w) return $this->error('not_found', "Widget '$key' not found");
        $w->delete();
        return $this->success(['widget_key' => $key, 'deleted' => true]);
    }

    public function execute_listWidgetPositions($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission('style')) return $err;

        $positions = \XF::finder('XF:WidgetPosition')->order('position_id')->fetch();
        $out = [];
        foreach ($positions as $p) {
            $out[] = [
                'position_id' => (string) $p->position_id,
                'title'       => (string) $p->title,
                'active'      => (bool)   $p->active,
                'widget_count' => (int) $p->widget_count,
            ];
        }
        return $this->success(['count' => count($out), 'positions' => $out]);
    }

    private function positionsFromArray(array $ids): array
    {
        $out = [];
        foreach ($ids as $id) {
            $out[(string) $id] = ['position_id' => (string) $id, 'display_order' => 10];
        }
        return $out;
    }
}
