<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — Styles bundle: list + set default style (minimal management).
 *
 * 2 tools. Full template editing is intentionally out of scope — that's a
 * multi-thousand-template surface with complex parent/child inheritance
 * best done via ACP. This bundle covers only what's practical for AI
 * automation: enumerate styles + set the site default.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminStylesTrait
{
    protected function registerStylesTools()
    {
        $this->registerTool('listStyles', [
            'description' => 'List all styles (style_id, title, parent_id, user_selectable, is_default). No arguments.',
            'input_schema' => [
                'type' => 'object',
                'properties' => new \stdClass(),
                'additionalProperties' => false,
            ],
        ]);

        $this->registerTool('setDefaultStyle', [
            'description' => 'Set the site default style. Users who have not chosen a style see this. '
                . 'Persists to option "defaultStyleId" via XF OptionRepository.',
            'input_schema' => [
                'type' => 'object',
                'required' => ['style_id'],
                'properties' => [
                    'style_id' => ['type' => 'integer', 'description' => 'Style ID to set as site default'],
                ],
                'additionalProperties' => false,
            ],
        ]);
    }

    public function execute_listStyles($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertStylePermission()) return $err;

        $defaultId = (int) \XF::options()->defaultStyleId;
        $styles = \XF::em()->findAll('XF:Style', ['title', 'ASC']);

        $out = [];
        foreach ($styles as $s) {
            $out[] = [
                'style_id' => (int) $s->style_id,
                'title' => (string) $s->title,
                'parent_id' => (int) $s->parent_id,
                'user_selectable' => (bool) $s->user_selectable,
                'is_default' => (int) $s->style_id === $defaultId,
            ];
        }
        return $this->success(['styles' => $out, 'count' => count($out), 'default_style_id' => $defaultId]);
    }

    public function execute_setDefaultStyle($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertStylePermission()) return $err;

        $styleId = (int) $params['style_id'];
        $style = \XF::em()->find('XF:Style', $styleId);
        if (!$style) return $this->error('not_found', "Style ID $styleId not found");

        /** @var \XF\Repository\OptionRepository $repo */
        $repo = \XF::em()->getRepository('XF:Option');
        $repo->updateOptions(['defaultStyleId' => $styleId]);

        return $this->success([
            'style_id' => $styleId,
            'title' => (string) $style->title,
            'is_default' => true,
        ]);
    }

    private function assertStylePermission(): ?array
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasAdminPermission('style')) {
            return $this->error('no_permission', 'The "style" admin permission is required');
        }
        return null;
    }
}
