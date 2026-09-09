<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — Usergroups bundle: full CRUD on user groups + secondary group
 * membership management. Closes the gap identified in the tool inventory
 * (2026-09-09) where usergroup CREATE/EDIT/DELETE and secondary group
 * assignment were missing.
 *
 * All 5 tools require admin scope + is_admin runtime check (via requireAdmin
 * inherited from AdminModule). Extra guard: mutations on the Administrative
 * group (id=3) or on any group containing super admins are blocked unless the
 * caller is also super admin, mirroring XF-core assertCanManageUser semantics.
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminUsergroupsTrait
{
    protected function registerUsergroupsTools()
    {
        $this->registerTool("createUsergroup", [
            "description" => "Create a new user group. Requires admin scope + is_admin.",
            "input_schema" => [
                "type" => "object",
                "required" => ["title"],
                "properties" => [
                    "title" => ["type" => "string", "description" => "Group title"],
                    "user_title" => ["type" => "string", "description" => "Custom user title override (optional)"],
                    "display_style_priority" => ["type" => "integer", "description" => "Display priority (higher wins on multi-group users)"],
                    "username_css" => ["type" => "string", "description" => "CSS for username styling (optional)"],
                    "banner_text" => ["type" => "string", "description" => "Banner text (optional)"],
                    "banner_css_class" => ["type" => "string", "description" => "Banner CSS class (optional)"],
                ],
                "additionalProperties" => false,
            ],
        ]);

        $this->registerTool("updateUsergroup", [
            "description" => "Edit an existing user group. Provide only fields to change.",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_group_id"],
                "properties" => [
                    "user_group_id" => ["type" => "integer", "description" => "Group ID (must exist)"],
                    "title" => ["type" => "string"],
                    "user_title" => ["type" => "string"],
                    "display_style_priority" => ["type" => "integer"],
                    "username_css" => ["type" => "string"],
                    "banner_text" => ["type" => "string"],
                    "banner_css_class" => ["type" => "string"],
                ],
                "additionalProperties" => false,
            ],
        ]);

        $this->registerTool("deleteUsergroup", [
            "description" => "Delete a user group. Refuses to delete the built-in groups (1-4) and groups containing super admins.",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_group_id"],
                "properties" => [
                    "user_group_id" => ["type" => "integer", "description" => "Group ID to delete"],
                ],
                "additionalProperties" => false,
            ],
        ]);

        $this->registerTool("addSecondaryGroup", [
            "description" => "Add a secondary user group to a member. Idempotent — no-op if already present.",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_id", "user_group_id"],
                "properties" => [
                    "user_id" => ["type" => "integer"],
                    "user_group_id" => ["type" => "integer", "description" => "Group to add as secondary"],
                ],
                "additionalProperties" => false,
            ],
        ]);

        $this->registerTool("removeSecondaryGroup", [
            "description" => "Remove a secondary user group from a member. Idempotent — no-op if not present.",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_id", "user_group_id"],
                "properties" => [
                    "user_id" => ["type" => "integer"],
                    "user_group_id" => ["type" => "integer", "description" => "Group to remove from secondary"],
                ],
                "additionalProperties" => false,
            ],
        ]);
    }

    public function execute_createUsergroup($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission("userGroup")) return $err;

        $group = \XF::em()->create("XF:UserGroup");
        $group->title = (string) $params["title"];
        $this->applyUsergroupParams($group, $params);

        if (!$group->preSave()) {
            return $this->error("validation_failed", implode(" ", $group->getErrors()));
        }
        $group->save();

        return $this->success($this->serializeUsergroup($group));
    }

    public function execute_updateUsergroup($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission("userGroup")) return $err;

        $group = \XF::em()->find("XF:UserGroup", (int) $params["user_group_id"]);
        if (!$group) return $this->error("not_found", "User group not found");

        if ($err = $this->assertCanMutateUsergroup($group)) return $err;

        if (isset($params["title"])) $group->title = (string) $params["title"];
        $this->applyUsergroupParams($group, $params);

        if (!$group->preSave()) {
            return $this->error("validation_failed", implode(" ", $group->getErrors()));
        }
        $group->save();

        return $this->success($this->serializeUsergroup($group));
    }

    public function execute_deleteUsergroup($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission("userGroup")) return $err;

        $groupId = (int) $params["user_group_id"];

        // Refuse the four built-in groups. XF ships Unregistered/Guests(1),
        // Registered(2), Administrative(3), Moderating(4) — deleting any of
        // them corrupts the site.
        if ($groupId >= 1 && $groupId <= 4) {
            return $this->error(
                "no_permission",
                "Refusing to delete built-in user group (id 1-4). Modify title/style via updateUsergroup instead."
            );
        }

        $group = \XF::em()->find("XF:UserGroup", $groupId);
        if (!$group) return $this->error("not_found", "User group not found");

        if ($err = $this->assertCanMutateUsergroup($group)) return $err;

        // Refuse if the group contains any super admin as primary OR secondary,
        // to avoid stripping super-admin association side-effects.
        $superInGroup = (int) \XF::db()->fetchOne(
            "SELECT COUNT(*) FROM xf_user_group_relation r
             INNER JOIN xf_admin a ON a.user_id = r.user_id
             WHERE r.user_group_id = ? AND a.is_super_admin = 1",
            [$groupId]
        );
        if ($superInGroup > 0) {
            return $this->error(
                "no_permission",
                "Refusing to delete group — it contains {$superInGroup} super admin(s). Move them out first."
            );
        }

        $group->delete();
        return $this->success(["user_group_id" => $groupId, "deleted" => true]);
    }

    public function execute_addSecondaryGroup($params)
    {
        if ($err = $this->requireAdmin()) return $err;

        $user = \XF::em()->find("XF:User", (int) $params["user_id"]);
        if (!$user) return $this->error("not_found", "User not found");
        if ($err = $this->assertCanTouchUser($user)) return $err;

        $groupId = (int) $params["user_group_id"];
        $group = \XF::em()->find("XF:UserGroup", $groupId);
        if (!$group) return $this->error("not_found", "User group not found");

        $current = is_array($user->secondary_group_ids) ? $user->secondary_group_ids : [];
        if (in_array($groupId, $current, true)) {
            return $this->success([
                "user_id" => $user->user_id,
                "user_group_id" => $groupId,
                "already_member" => true,
                "secondary_group_ids" => $current,
            ]);
        }
        $current[] = $groupId;
        $user->secondary_group_ids = $current;
        $user->save();

        return $this->success([
            "user_id" => $user->user_id,
            "user_group_id" => $groupId,
            "added" => true,
            "secondary_group_ids" => is_array($user->secondary_group_ids) ? $user->secondary_group_ids : [],
        ]);
    }

    public function execute_removeSecondaryGroup($params)
    {
        if ($err = $this->requireAdmin()) return $err;

        $user = \XF::em()->find("XF:User", (int) $params["user_id"]);
        if (!$user) return $this->error("not_found", "User not found");
        if ($err = $this->assertCanTouchUser($user)) return $err;

        $groupId = (int) $params["user_group_id"];
        $current = is_array($user->secondary_group_ids) ? $user->secondary_group_ids : [];
        if (!in_array($groupId, $current, true)) {
            return $this->success([
                "user_id" => $user->user_id,
                "user_group_id" => $groupId,
                "was_not_member" => true,
                "secondary_group_ids" => $current,
            ]);
        }
        $user->secondary_group_ids = array_values(array_filter($current, fn($g) => (int) $g !== $groupId));
        $user->save();

        return $this->success([
            "user_id" => $user->user_id,
            "user_group_id" => $groupId,
            "removed" => true,
            "secondary_group_ids" => is_array($user->secondary_group_ids) ? $user->secondary_group_ids : [],
        ]);
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private function applyUsergroupParams(\XF\Entity\UserGroup $group, array $params): void
    {
        foreach (["user_title", "display_style_priority", "username_css", "banner_text", "banner_css_class"] as $field) {
            if (isset($params[$field])) {
                $group->$field = $params[$field];
            }
        }
    }

    private function serializeUsergroup(\XF\Entity\UserGroup $g): array
    {
        return [
            "user_group_id" => (int) $g->user_group_id,
            "title" => (string) $g->title,
            "user_title" => (string) $g->user_title,
            "display_style_priority" => (int) $g->display_style_priority,
            "username_css" => (string) $g->username_css,
            "banner_text" => (string) $g->banner_text,
            "banner_css_class" => (string) $g->banner_css_class,
        ];
    }

    /**
     * Guard for group-level mutations. Blocks Administrative(3) unless caller
     * is super admin — modifying the admin group is a privilege-escalation
     * vector even for regular admins.
     */
    private function assertCanMutateUsergroup(\XF\Entity\UserGroup $group): ?array
    {
        $visitor = \XF::visitor();
        if ((int) $group->user_group_id === 3 && !$visitor->is_super_admin) {
            return $this->error(
                "no_permission",
                "Only a super administrator can mutate the Administrative group (id 3)"
            );
        }
        return null;
    }

    /**
     * Guard for user-level mutations coming from a group tool. Same semantics
     * as assertCanManageUser in AdminModule but scoped here to avoid tight
     * cross-trait coupling.
     */
    private function assertCanTouchUser(\XF\Entity\User $user): ?array
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasAdminPermission("user")) {
            return $this->error("no_permission", "The user admin permission is required");
        }
        if ($user->is_super_admin && !$visitor->is_super_admin) {
            return $this->error("no_permission", "Only a super administrator can act on a super administrator");
        }
        return null;
    }
}
