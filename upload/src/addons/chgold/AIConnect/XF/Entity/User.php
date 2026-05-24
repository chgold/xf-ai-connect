<?php

namespace chgold\AIConnect\XF\Entity;

/**
 * Extends the core XF User entity with helpers used by the
 * AI Connect navigation entries.
 */
class User extends XFCP_User
{
    /**
     * True when this user has at least one ACTIVE (non-revoked, non-expired)
     * AI Connect token. Cached per request via the helper.
     */
    public function hasAiConnectActiveTokens(): bool
    {
        return \chgold\AIConnect\Helper\Nav::userHasActiveTokens((int)$this->user_id);
    }
}
