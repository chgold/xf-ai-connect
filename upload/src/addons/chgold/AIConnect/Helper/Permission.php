<?php

namespace chgold\AIConnect\Helper;

/**
 * Builds the per-tool permission id used across registration (Setup),
 * manifest filtering and tool execution.
 *
 * xf_permission.permission_id is varbinary(25). The old scheme prefixed the full
 * tool name and blind-truncated to 25 chars, which collided for tools sharing a
 * long common prefix (e.g. deleteUser / deleteUserAvatar), so disabling one in
 * the admin panel silently disabled the other. A short hash of the FULL
 * module+tool name keeps every id unique and well under 25 chars.
 */
class Permission
{
    public static function toolPermId(string $moduleName, string $toolName): string
    {
        return 't_' . substr(md5($moduleName . '_' . $toolName), 0, 12);
    }
}
