<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — Discipline bundle: ban / unban users. Closes the gap identified in
 * the tool inventory (2026-09-09) where no user-discipline endpoint existed.
 *
 * Both tools require admin scope + is_admin. Standard super-admin guard
 * applies: only a super admin can ban another super admin (mirrors XF-core
 * assertCanManageUser semantics, same pattern used across AdminModule).
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminDisciplineTrait
{
    protected function registerDisciplineTools()
    {
        $this->registerTool("banUser", [
            "description" => "Ban a user (permanent by default). Requires admin scope + is_admin. "
                . "Refuses to ban a super admin unless caller is also super admin. "
                . "For temporary ban, pass ends_at (unix timestamp) OR duration_days.",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_id"],
                "properties" => [
                    "user_id" => ["type" => "integer", "description" => "User to ban"],
                    "reason" => ["type" => "string", "description" => "Public reason shown to the banned user (optional)"],
                    "duration_days" => ["type" => "integer", "description" => "Ban length in days (mutually exclusive with ends_at). Omit for permanent."],
                    "ends_at" => ["type" => "integer", "description" => "Unix timestamp when ban expires (mutually exclusive with duration_days). Omit for permanent."],
                ],
                "additionalProperties" => false,
            ],
        ]);

        $this->registerTool("unbanUser", [
            "description" => "Remove an active ban on a user. Idempotent — success if no ban present.",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_id"],
                "properties" => [
                    "user_id" => ["type" => "integer", "description" => "User to unban"],
                ],
                "additionalProperties" => false,
            ],
        ]);
    }

    public function execute_banUser($params)
    {
        if ($err = $this->requireAdmin()) return $err;

        $user = \XF::em()->find("XF:User", (int) $params["user_id"]);
        if (!$user) return $this->error("not_found", "User not found");
        if ($err = $this->assertCanDisciplineUser($user)) return $err;

        // Compute expiration: 0 = permanent
        $endsAt = 0;
        if (isset($params["ends_at"]) && (int) $params["ends_at"] > \XF::$time) {
            $endsAt = (int) $params["ends_at"];
        } elseif (isset($params["duration_days"]) && (int) $params["duration_days"] > 0) {
            $endsAt = \XF::$time + ((int) $params["duration_days"] * 86400);
        }

        $ban = \XF::em()->create("XF:UserBan");
        $ban->user_id = $user->user_id;
        $ban->ban_user_id = \XF::visitor()->user_id;
        $ban->user_reason = isset($params["reason"]) ? (string) $params["reason"] : "";
        $ban->end_date = $endsAt;

        if (!$ban->preSave()) {
            return $this->error("validation_failed", implode(" ", $ban->getErrors()));
        }
        $ban->save();

        // XF flips is_banned via the ban save handler, but refresh entity to be sure
        $user = \XF::em()->find("XF:User", $user->user_id, ["forceRefresh" => true]);

        return $this->success([
            "user_id" => $user->user_id,
            "username" => $user->username,
            "is_banned" => (bool) $user->is_banned,
            "end_date" => $endsAt,
            "permanent" => $endsAt === 0,
            "reason" => $ban->user_reason,
        ]);
    }

    public function execute_unbanUser($params)
    {
        if ($err = $this->requireAdmin()) return $err;
        if ($err = $this->assertPermission("user")) return $err;

        $user = \XF::em()->find("XF:User", (int) $params["user_id"]);
        if (!$user) return $this->error("not_found", "User not found");

        // Fetch the ban (there is at most one active per user)
        $ban = \XF::em()->find("XF:UserBan", $user->user_id);
        if (!$ban) {
            return $this->success([
                "user_id" => $user->user_id,
                "username" => $user->username,
                "was_not_banned" => true,
                "is_banned" => (bool) $user->is_banned,
            ]);
        }

        $ban->delete();
        $user = \XF::em()->find("XF:User", $user->user_id, ["forceRefresh" => true]);

        return $this->success([
            "user_id" => $user->user_id,
            "username" => $user->username,
            "unbanned" => true,
            "is_banned" => (bool) $user->is_banned,
        ]);
    }

    /**
     * Ban is destructive-ish. Only super admin may ban another super admin.
     * Plain admin banning a super admin would silently lock them out of
     * moderation → we mirror the standard AdminModule protection.
     */
    private function assertCanDisciplineUser(\XF\Entity\User $user): ?array
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasAdminPermission("user")) {
            return $this->error("no_permission", "The user admin permission is required to ban members");
        }
        if ($user->is_super_admin && !$visitor->is_super_admin) {
            return $this->error("no_permission", "Only a super administrator can ban another super administrator");
        }
        if ($user->user_id === $visitor->user_id) {
            return $this->error("no_permission", "You cannot ban yourself via API — lockout risk");
        }
        return null;
    }
}
