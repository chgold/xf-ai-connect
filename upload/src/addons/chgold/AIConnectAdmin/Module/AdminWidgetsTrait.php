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

        // xf_widget has NO display_order/active columns — those live per-position
        // inside the positions JSON blob. Order by widget_key for stability.
        $finder = \XF::finder('XF:Widget')->order('widget_key');
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
                'title'                => (string) $w->title,  // computed getter (via phrase)
                'positions'            => $positions,  // full per-position map: {position_id: {display_order, ...}}
                'options'              => $w->options ?: new \stdClass(),
                'display_condition'    => (string) $w->display_condition,
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
            'positions'            => $w->positions ?: new \stdClass(),
            'options'              => $w->options ?: new \stdClass(),
            'display_condition'    => (string) $w->display_condition,
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
        // display_order + active are per-position properties (built into positions map)
        $w->positions     = $this->positionsFromArray(
            (array) $params['positions'],
            (int) ($params['display_order'] ?? 10),
            (bool) ($params['active'] ?? true)
        );
        // Save first (need widget_id + widget_key resolved before creating linked template row for Html widgets)
        $w->options = $this->normalizeOptionsForDefinition(
            (string) $params['widget_definition_id'],
            (array) ($params['options'] ?? []),
            $key
        );

        if (!$w->save()) {
            return $this->error('validation_failed', implode(' ', $w->getErrors()));
        }

        // Title lives in a phrase (widget.{widget_key}) — write it via phrase if provided.
        if (!empty($params['title'])) {
            $this->writeWidgetTitle($w->widget_key, (string) $params['title']);
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

        if (isset($params['positions'])) {
            $w->positions = $this->positionsFromArray(
                (array) $params['positions'],
                (int) ($params['display_order'] ?? 10),
                (bool) ($params['active'] ?? true)
            );
        } elseif (isset($params['display_order']) || isset($params['active'])) {
            // Positions is flat map {pos_id: displayOrder}. Update the int value directly.
            $pos = $w->positions ?: [];
            $newOrder = isset($params['display_order']) ? (int) $params['display_order'] : null;
            if (isset($params['active']) && !$params['active']) {
                // Turning off = clear positions
                $w->positions = [];
            } else {
                if ($newOrder !== null) {
                    foreach ($pos as $pid => $_) {
                        $pos[$pid] = $newOrder;
                    }
                }
                $w->positions = $pos;
            }
        }
        if (isset($params['options'])) {
            $w->options = $this->normalizeOptionsForDefinition(
                (string) $w->definition_id,
                (array) $params['options'],
                (string) $w->widget_key
            );
        }

        if (!$w->save()) {
            return $this->error('validation_failed', implode(' ', $w->getErrors()));
        }
        // Update title phrase if provided
        if (isset($params['title'])) {
            $this->writeWidgetTitle($w->widget_key, (string) $params['title']);
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

        // xf_widget_position schema: position_id, active, addon_id (no title/widget_count)
        $positions = \XF::finder('XF:WidgetPosition')->order('position_id')->fetch();
        $out = [];
        foreach ($positions as $p) {
            // Widget count via DB (widgets store positions as JSON array in xf_widget)
            $widgetCount = \XF::db()->fetchOne(
                'SELECT COUNT(*) FROM xf_widget WHERE positions LIKE ?',
                ['%' . $p->position_id . '%']
            );
            $out[] = [
                'position_id'  => (string) $p->position_id,
                'active'       => (bool)   $p->active,
                'addon_id'     => (string) $p->addon_id,
                'widget_count' => (int)    $widgetCount,
            ];
        }
        return $this->success(['count' => count($out), 'positions' => $out]);
    }

    /**
     * XF stores widget positions as a FLAT map: {position_id: displayOrderInt}.
     * See XF\Repository\WidgetRepository::getWidgetCache() line 113 which does
     * `foreach ($widget['positions'] as $positionId => $displayOrder)` and then
     * subtracts $displayOrder values (line 147). Nested structs cause TypeError.
     *
     * `active` is NOT per-position — it's a top-level entity column (which we
     * remove/re-add as needed to include/exclude a widget from a position).
     */
    private function positionsFromArray(array $ids, int $displayOrder = 10, bool $active = true): array
    {
        $out = [];
        foreach ($ids as $id) {
            if ($active) {
                $out[(string) $id] = $displayOrder;  // XF format: flat map
            }
            // when $active=false, simply omit the position — widget invisible there
        }
        return $out;
    }

    /**
     * XF's Widget\Html doesn't store HTML in options — it creates a linked
     * template row (`_widget_{widget_key}`) via verifyOptions() and stores
     * only `template_title` + `advanced_mode` in options. Its render() then
     * renderTemplate('public:' . template_title) which triggers
     * "Template public: is unknown" warnings if template_title is empty or
     * the linked template row doesn't exist.
     *
     * We replicate verifyOptions here for the html definition (input arrives
     * as {html: "<p>..."} from the tool but must be persisted as an
     * xf_template row + template_title pointer). Other definitions store
     * options as-is.
     */
    private function normalizeOptionsForDefinition(string $definitionId, array $options, string $widgetKey): array
    {
        // SECURITY (v1.4.14): only called from execute_createWidget / execute_editWidget
        // which invoke requireAdmin() + assertPermission('style'). See CHECK_XF_002.
        if ($definitionId !== 'html') {
            return $options;
        }

        $templateTitle = '_widget_' . $widgetKey;
        $htmlContent   = (string) ($options['html'] ?? $options['template'] ?? '');

        $existing = \XF::em()->findOne(\XF\Entity\Template::class, [
            'style_id' => 0, 'type' => 'public', 'title' => $templateTitle,
        ]);
        if ($existing) {
            $existing->template = $htmlContent;
            $existing->save();
        } else {
            $t = \XF::em()->create(\XF\Entity\Template::class);
            $t->type     = 'public';
            $t->title    = $templateTitle;
            $t->style_id = 0;
            $t->addon_id = '';
            $t->template = $htmlContent;
            $t->save();
        }

        return [
            'template_title' => $templateTitle,
            'advanced_mode'  => (bool) ($options['advanced_mode'] ?? false),
        ];
    }

    /**
     * Widget titles live in the phrase table (widget.{widget_key}).
     * Write directly via Phrase entity — XF ACP does the same.
     */
    private function writeWidgetTitle(string $widgetKey, string $title): void
    {
        // SECURITY (v1.4.14): only called from execute_createWidget / execute_editWidget
        // which invoke requireAdmin() + assertPermission('style'). See CHECK_XF_002.
        $phraseTitle = 'widget.' . $widgetKey;
        $existing = \XF::em()->findOne('XF:Phrase', [
            'title' => $phraseTitle, 'language_id' => 0,
        ]);
        if ($existing) {
            $existing->phrase_text = $title;
            $existing->save();
        } else {
            $p = \XF::em()->create('XF:Phrase');
            $p->title = $phraseTitle;
            $p->language_id = 0;
            $p->phrase_text = $title;
            $p->global_cache = 0;
            $p->addon_id = '';
            $p->save();
        }
    }
}
