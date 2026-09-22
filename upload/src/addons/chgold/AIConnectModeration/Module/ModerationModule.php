<?php

namespace chgold\AIConnectModeration\Module;

use chgold\AIConnect\Module\ModuleBase;

/**
 * Moderation tool-set: reports queue, approval queue, warnings, thread/post
 * moderation, and advanced moderation (merge/split).
 *
 * These live in their own add-on (separate from Pro and Admin) because they
 * target moderator-role operations rather than member-facing or board-admin
 * operations. The token must carry the 'admin' OAuth scope (shared with
 * AIConnectAdmin) — moderation actions require elevated permissions.
 *
 * Bundle structure:
 *   moderation    → thread/post moderation (lock/stick/move/delete/edit) — 14 tools
 *   reports       → reports queue (list/get/resolve/reject/reply) — 5 tools
 *   approval      → approval queue (list/approve/reject/deletion-log) — 4 tools
 *   warnings      → issue/list/delete warnings + definitions — 4 tools
 *   advanced_mod  → merge + split posts — 2 tools
 */
class ModerationModule extends ModuleBase
{
    use ProModerationTrait;
    use ProReportsTrait;
    use ProApprovalTrait;
    use ProWarningsTrait;
    use ProAdvancedModTrait;

    protected $moduleName = 'xenforo_mod';

    /** Default fallback package id — tools with no explicit bundle assignment. */
    protected $packageId = 'moderation';

    /**
     * bundle_key → registrar method. Mirrors ProModule::BUNDLE_REGISTRARS so
     * dashboard/permissions can group + toggle the same way.
     */
    public const BUNDLE_REGISTRARS = [
        'moderation'   => ['label' => 'Moderation',                    'method' => 'registerModerationTools'],
        'reports'      => ['label' => 'Reports Queue',                 'method' => 'registerReportsTools'],
        'approval'     => ['label' => 'Approval Queue',                'method' => 'registerApprovalTools'],
        'warnings'     => ['label' => 'Warnings (issue/list/delete)',  'method' => 'registerWarningsTools'],
        'advanced_mod' => ['label' => 'Advanced Mod (merge/split)',    'method' => 'registerAdvancedModTools'],
    ];

    /**
     * Tool names grouped by bundle: [bundleKey => [toolName => humanLabel]].
     *
     * Determined by actually running each trait's registrar on a throwaway
     * instance and diffing the registered tools, rather than by maintaining a
     * hand-written list that would silently rot as tools are added.
     *
     * @return array<string, array<string, string>>
     */
    public function getToolNamesByBundle(): array
    {
        $byBundle = [];
        $seen     = [];

        foreach (self::BUNDLE_REGISTRARS as $bundleKey => $info) {
            $probe = new self(null);
            // Reset so each probe reports only what THIS registrar adds.
            $probe->tools = [];
            $probe->{$info['method']}();

            $names = [];
            foreach ($probe->getToolNames() as $tool => $label) {
                if (isset($seen[$tool])) {
                    continue;
                }
                $seen[$tool] = true;
                $names[$tool] = $label;
            }
            if ($names) {
                $byBundle[$bundleKey] = $names;
            }
        }

        // Anything registered outside a trait belongs with the default package.
        $all = (new self(null))->getToolNames();
        foreach ($all as $tool => $label) {
            if (!isset($seen[$tool])) {
                $byBundle[$this->packageId][$tool] = $label;
            }
        }

        return $byBundle;
    }

    /**
     * The bundle that owns a given tool — each bundle has its own master
     * permission (use_package_{bundle}), so the manifest must test the switch
     * that actually governs this tool rather than a single module-wide one.
     *
     * Falls back to $packageId for anything unmapped, so a newly added tool is
     * never silently ungated.
     */
    public function getPackageIdForTool(string $toolName): ?string
    {
        static $map = null;
        if ($map === null) {
            $map = [];
            foreach ($this->getToolNamesByBundle() as $bundleKey => $tools) {
                foreach (array_keys($tools) as $tool) {
                    $map[$tool] = $bundleKey;
                }
            }
        }
        return $map[$toolName] ?? $this->packageId;
    }

    /**
     * Shared guard: every write/delete moderation tool may call this.
     * Returns an error array to short-circuit on, or null when allowed.
     */
    protected function requireWrite()
    {
        // v1.2.6 — Servio-aligned no-op.
        //
        // Previously enforced checkScope('write') on the token as a hard gate.
        // Per Servio the token is only identity; per-request permission is
        // authoritative. Every write tool that calls requireWrite() also runs
        // its own XF-native per-request check (Thread::canReply(),
        // Post::canEdit(), Conversation::canReply(), etc.) which is the real
        // authorization. The scope gate was defense-in-depth that instead
        // created blocking bugs any time the token's stored scope drifted
        // from what the caller was actually entitled to.
        //
        // Kept as a callable stub so existing execute_* methods don't need
        // to be rewritten; will be repurposed if we later add an explicit
        // "issue me a write-limited token" flow that voluntarily narrows
        // caller capability.
        return null;
    }

    protected function registerTools()
    {
        // Bundle-per-trait gating (BUNDLES-LICENSE-SPEC.md v1.0).
        // The license grants a list of bundle keys; each trait registers its
        // tools only when the current license includes its bundle. The '*'
        // wildcard (default backward-compat verdict) unlocks everything.
        $bundles = \chgold\AIConnectModeration\License\Validator::getBundles();
        $has = static function (string $bundle) use ($bundles): bool {
            return in_array('*', $bundles, true) || in_array($bundle, $bundles, true);
        };

        if ($has('moderation')) {
            $this->registerModerationTools();
        }
        if ($has('reports')) {
            $this->registerReportsTools();
        }
        if ($has('approval')) {
            $this->registerApprovalTools();
        }
        if ($has('warnings')) {
            $this->registerWarningsTools();
        }
        if ($has('advanced_mod')) {
            $this->registerAdvancedModTools();
        }
    }
}
