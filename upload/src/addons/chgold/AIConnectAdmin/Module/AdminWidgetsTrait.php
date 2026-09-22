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
                    'widget_definition_id' => [
                        'type' => 'string',
                        'description' => 'Widget type — see listWidgetDefinitions',
                    ],
                    'title' => ['type' => 'string'],
                    'positions' => [
                        'type' => 'array',
                        'items' => ['type' => 'string'],
                        'description' => 'Position keys — see listWidgetPositions',
                    ],
                    'display_order' => ['type' => 'integer'],
                    'active' => ['type' => 'boolean'],
                    'display_condition' => [
                        'type' => 'string',
                        'description' => 'XF display-condition expression targeting where the widget shows '
                            . '(maps to the xf_widget.display_condition column, NOT options). Empty string = '
                            . 'always shown. IMPORTANT: content-scoped vars are exposed by XF as $context.{name}, '
                            . 'NOT bare: use "$context.thread.thread_id == 94", "$context.forum.node_id == 5", '
                            . '"$context.user.user_id == 7". Globals stay bare: "$xf.visitor.isMemberOf(3)". A bare '
                            . '"$thread.*"/"$forum.*"/etc. is auto-corrected to "$context.*" (a bare form never '
                            . 'evaluates at render time). Numeric literals like "1" are NOT valid conditions in XF.',
                    ],
                    'options' => [
                        'type' => 'object',
                        'description' => 'Type-specific config (varies per widget_definition_id). For an "html" '
                            . 'widget pass {html: "<p>…</p>"} — the tool auto-creates the backing template and sets '
                            . 'template_title to "_widget_{widget_key}" (mirrors XenForo); a manually-supplied '
                            . 'template_title is intentionally ignored.',
                    ],
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
                    'display_condition' => [
                        'type' => 'string',
                        'description' => 'XF display-condition expression (maps to xf_widget.display_condition '
                            . 'column, NOT options). Pass empty string to clear. Content-scoped vars use the '
                            . '$context prefix: "$context.thread.thread_id == 94", "$context.forum.node_id == 5". '
                            . 'Globals stay bare: "$xf.visitor.isMemberOf(3)". A bare "$thread.*"/"$forum.*"/etc. is '
                            . 'auto-corrected to "$context.*".',
                    ],
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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

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
            if ($filterPos !== '' && !isset($positions[$filterPos])) {
                continue;
            }
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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

        $key = (string) $params['widget_key'];
        $w = \XF::em()->findOne('XF:Widget', ['widget_key' => $key]);
        if (!$w) {
            return $this->error('not_found', "Widget '$key' not found");
        }

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
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

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
        // display_condition is a top-level xf_widget column (NOT NULL), NOT part of options.
        // Auto-normalize content-scoped vars ($thread.* -> $context.thread.*) so the
        // condition actually evaluates at render time (see normalizeDisplayCondition).
        [$condNormalized, $condRewritten] = $this->normalizeDisplayCondition(
            (string) ($params['display_condition'] ?? '')
        );
        $w->display_condition = $condNormalized;
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

        $result = [
            'widget_id'  => $w->widget_id,
            'widget_key' => $w->widget_key,
            'created'    => true,
        ];
        if ($condRewritten) {
            $result['display_condition_normalized'] = $condNormalized;
            $result['note'] = 'display_condition was auto-corrected to the $context.* form '
                . 'XenForo populates at render time (a bare $thread/$forum/... never evaluates).';
        }
        return $this->success($result);
    }

    public function execute_editWidget($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

        $key = (string) $params['widget_key'];
        $w = \XF::em()->findOne('XF:Widget', ['widget_key' => $key]);
        if (!$w) {
            return $this->error('not_found', "Widget '$key' not found");
        }

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
        // display_condition is a top-level xf_widget column (NOT NULL). Update when provided
        // (pass empty string to clear an existing condition). Auto-normalize
        // content-scoped vars ($thread.* -> $context.thread.*) — see createWidget.
        $condRewritten = false;
        $condNormalized = null;
        if (isset($params['display_condition'])) {
            [$condNormalized, $condRewritten] = $this->normalizeDisplayCondition(
                (string) $params['display_condition']
            );
            $w->display_condition = $condNormalized;
        }

        if (!$w->save()) {
            return $this->error('validation_failed', implode(' ', $w->getErrors()));
        }
        // Update title phrase if provided
        if (isset($params['title'])) {
            $this->writeWidgetTitle($w->widget_key, (string) $params['title']);
        }
        $result = ['widget_key' => $key, 'updated' => true];
        if ($condRewritten) {
            $result['display_condition_normalized'] = $condNormalized;
            $result['note'] = 'display_condition was auto-corrected to the $context.* form '
                . 'XenForo populates at render time (a bare $thread/$forum/... never evaluates).';
        }
        return $this->success($result);
    }

    public function execute_deleteWidget($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

        $key = (string) $params['widget_key'];
        $w = \XF::em()->findOne('XF:Widget', ['widget_key' => $key]);
        if (!$w) {
            return $this->error('not_found', "Widget '$key' not found");
        }
        $w->delete();
        return $this->success(['widget_key' => $key, 'deleted' => true]);
    }

    public function execute_listWidgetPositions($params)
    {
        if ($err = $this->requireAdmin()) {
            return $err;
        }
        if ($err = $this->assertPermission('style')) {
            return $err;
        }

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
     * Content-scoped variables that XF exposes to a widget's display_condition
     * ONLY through the position's context bag, i.e. as $context.{name}.{...}.
     * These are the context-* params XF core passes on its widget positions
     * (verified against every <xf:widgetpos ... context-X="{$X}"> in the default
     * templates): thread → thread_view_*, forum → forum_view_sidebar,
     * category → category_view_sidebar, user → member_view_sidebar,
     * conversation → conversation_view_sidebar.
     *
     * A bare "$thread.thread_id == 94" compiles to $__vars['thread'][...],
     * which is ALWAYS empty at widget-render time, so the widget silently never
     * shows. The correct form is "$context.thread.thread_id == 94". Globals such
     * as $xf.visitor / $xf.options are NOT context-scoped and must be left alone.
     */
    private const CONTEXT_VARS = ['thread', 'forum', 'category', 'user', 'conversation', 'node', 'page', 'resource'];

    /**
     * Auto-normalize a display_condition so content-scoped vars use the $context
     * prefix XF actually populates at render time. Rewrites a bare leading
     * "$thread" / "$forum" / ... to "$context.thread" / "$context.forum" / ...,
     * without touching an already-correct "$context.thread", globals like
     * "$xf.visitor", or substrings inside other identifiers.
     *
     * Returns [normalized_condition, was_rewritten].
     *
     * @return array{0:string,1:bool}
     */
    private function normalizeDisplayCondition(string $condition): array
    {
        if ($condition === '') {
            return ['', false];
        }

        $rewritten = false;
        $alt = implode('|', self::CONTEXT_VARS);
        // Match $var only when it's a whole leading token: preceded by a non-word,
        // non-"." char (so "$context.thread" and "$foothread" are untouched),
        // and followed by "." or "->" (a property access — not "$thread" alone,
        // which would never be a useful boolean anyway).
        $pattern = '/(?<![\w.$])\$(' . $alt . ')(?=\s*(?:\.|->))/';
        $normalized = preg_replace_callback(
            $pattern,
            function (array $m) use (&$rewritten) {
                $rewritten = true;
                return '$context.' . $m[1];
            },
            $condition
        );

        return [$normalized ?? $condition, $rewritten];
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
