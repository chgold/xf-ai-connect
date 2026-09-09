<?php

namespace chgold\AIConnectAdmin\Module;

/**
 * Admin — User credentials bundle: change email + password.
 *
 * High-privilege bundle (edits sensitive auth data). Guarded by the same
 * assertCanTouchUser pattern used across AdminModule:
 *   - admin scope + is_admin (base requirement, requireAdmin)
 *   - "user" admin permission (matches XF-native ACP requirement)
 *   - super-admin target requires super-admin caller
 *   - refuses self-edit of password/email via API (must use standard flow)
 *
 * phpcs:disable Squiz.NamingConventions.ValidFunctionName.NotCamelCaps
 * phpcs:disable PSR1.Methods.CamelCapsMethodName.NotCamelCaps
 */
trait AdminUserCredentialsTrait
{
    protected function registerUserCredentialsTools()
    {
        $this->registerTool("changeUserEmail", [
            "description" => "Change another users email address. Refuses self-edit (use standard email-change flow). "
                . "By default marks the email as unconfirmed and puts user into email_confirm state; "
                . "pass mark_confirmed:true to bypass (admin flag).",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_id", "email"],
                "properties" => [
                    "user_id" => ["type" => "integer", "description" => "User whose email to change"],
                    "email" => ["type" => "string", "description" => "New email address (must be valid RFC-5322)"],
                    "mark_confirmed" => ["type" => "boolean", "description" => "If true, mark the new address as already-confirmed (skip email_confirm state). Default false = user must confirm via email."],
                ],
                "additionalProperties" => false,
            ],
        ]);

        $this->registerTool("changeUserPassword", [
            "description" => "Set a new password for another user. Refuses self-edit (use standard password reset). "
                . "Returns the new password value ONLY if generated_password was requested (temporary). "
                . "Otherwise no plaintext is ever returned.",
            "input_schema" => [
                "type" => "object",
                "required" => ["user_id"],
                "properties" => [
                    "user_id" => ["type" => "integer"],
                    "new_password" => ["type" => "string", "description" => "Explicit new password. Mutually exclusive with generate_password. Min 6 chars."],
                    "generate_password" => ["type" => "boolean", "description" => "If true, generate a strong random password and return it. Mutually exclusive with new_password."],
                ],
                "additionalProperties" => false,
            ],
        ]);
    }

    public function execute_changeUserEmail($params)
    {
        if ($err = $this->requireAdmin()) return $err;

        $user = \XF::em()->find("XF:User", (int) $params["user_id"]);
        if (!$user) return $this->error("not_found", "User not found");
        if ($err = $this->assertCanTouchCredentials($user)) return $err;

        $newEmail = trim((string) $params["email"]);
        if (!filter_var($newEmail, FILTER_VALIDATE_EMAIL)) {
            return $this->error("validation_failed", "Invalid email address");
        }

        // Reject email collision with another user (XF enforces at DB level too)
        $existing = \XF::em()->findOne("XF:User", ["email" => $newEmail]);
        if ($existing && $existing->user_id !== $user->user_id) {
            return $this->error("validation_failed", "Email already in use by another account");
        }

        $user->email = $newEmail;
        if (empty($params["mark_confirmed"])) {
            $user->user_state = "email_confirm";
        }
        if (!$user->preSave()) {
            return $this->error("validation_failed", implode(" ", $user->getErrors()));
        }
        $user->save();

        return $this->success([
            "user_id" => $user->user_id,
            "email" => $user->email,
            "user_state" => $user->user_state,
        ]);
    }

    public function execute_changeUserPassword($params)
    {
        if ($err = $this->requireAdmin()) return $err;

        $user = \XF::em()->find("XF:User", (int) $params["user_id"]);
        if (!$user) return $this->error("not_found", "User not found");
        if ($err = $this->assertCanTouchCredentials($user)) return $err;

        $hasNew = isset($params["new_password"]) && $params["new_password"] !== "";
        $doGen  = !empty($params["generate_password"]);

        if ($hasNew === $doGen) {
            return $this->error(
                "validation_failed",
                "Provide exactly ONE of new_password or generate_password:true"
            );
        }

        $newPassword = $hasNew ? (string) $params["new_password"] : $this->generateStrongPassword();
        if (strlen($newPassword) < 6) {
            return $this->error("validation_failed", "Password must be at least 6 characters");
        }

        /** @var \XF\Service\User\PasswordChangeService $service */
        $service = \XF::service("XF:User\PasswordChange", $user);
        $service->setNewPassword($newPassword);
        $service->save();

        $out = [
            "user_id" => $user->user_id,
            "username" => $user->username,
            "changed" => true,
        ];
        // Only return the plaintext if the caller asked us to generate it
        if ($doGen) {
            $out["generated_password"] = $newPassword;
            $out["note"] = "Deliver this to the user via a secure channel; it is not shown again.";
        }
        return $this->success($out);
    }

    // ── helpers ─────────────────────────────────────────────────────────

    private function assertCanTouchCredentials(\XF\Entity\User $user): ?array
    {
        $visitor = \XF::visitor();
        if (!$visitor->hasAdminPermission("user")) {
            return $this->error("no_permission", "The user admin permission is required");
        }
        if ($user->is_super_admin && !$visitor->is_super_admin) {
            return $this->error("no_permission", "Only a super administrator can change credentials of a super administrator");
        }
        if ($user->user_id === $visitor->user_id) {
            return $this->error(
                "no_permission",
                "Refusing to change your own credentials via API. Use the standard change-email / password-reset flow (which has proper re-auth)."
            );
        }
        return null;
    }

    private function generateStrongPassword(int $length = 16): string
    {
        $alphabet = "abcdefghijkmnopqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#%^&*";
        $max = strlen($alphabet) - 1;
        $out = "";
        for ($i = 0; $i < $length; $i++) {
            $out .= $alphabet[random_int(0, $max)];
        }
        return $out;
    }
}
