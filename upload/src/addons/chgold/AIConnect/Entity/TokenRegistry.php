<?php

namespace chgold\AIConnect\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * Token Registry — audit trail for every access token ever issued.
 *
 * Stores only a 16-char prefix of each token, plus metadata (issuer, IP,
 * lifecycle timestamps). Supports soft-delete via `revoked_at` + `revoked_by`
 * so revoked tokens remain auditable.
 *
 * COLUMNS
 * @property int|null    $id
 * @property string      $token_prefix
 * @property int         $user_id
 * @property string      $client_id
 * @property string      $scope
 * @property int         $issued_at
 * @property int         $expires_at
 * @property int|null    $refresh_expires_at
 * @property int|null    $last_used_at
 * @property int|null    $revoked_at
 * @property int|null    $revoked_by
 * @property string      $source
 * @property string|null $ip_address
 * @property string|null $last_used_ip
 * @property string|null $last_used_ua
 *
 * RELATIONS
 * @property-read \XF\Entity\User|null $User
 * @property-read \XF\Entity\User|null $RevokedBy
 */
class TokenRegistry extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table      = 'xf_chgold_aiconnect_token_registry';
        $structure->shortName  = 'chgold\AIConnect:TokenRegistry';
        $structure->primaryKey = 'id';
        $structure->columns    = [
            'id'           => ['type' => self::UINT, 'autoIncrement' => true, 'nullable' => true],
            'token_prefix' => ['type' => self::STR, 'maxLength' => 16, 'required' => true],
            'user_id'      => ['type' => self::UINT, 'required' => true],
            'client_id'    => ['type' => self::STR, 'maxLength' => 80, 'default' => ''],
            'scope'        => ['type' => self::STR, 'maxLength' => 255, 'default' => 'read'],
            'issued_at'    => ['type' => self::UINT, 'default' => \XF::$time],
            'expires_at'   => ['type' => self::UINT, 'required' => true],
            'refresh_expires_at' => ['type' => self::UINT, 'nullable' => true, 'default' => null],
            'last_used_at' => ['type' => self::UINT, 'nullable' => true, 'default' => null],
            'revoked_at'   => ['type' => self::UINT, 'nullable' => true, 'default' => null],
            'revoked_by'   => ['type' => self::UINT, 'nullable' => true, 'default' => null],
            'source'       => [
                'type'        => self::STR,
                'allowedValues' => ['generator', 'oauth', 'refresh'],
                'default'     => 'oauth',
            ],
            'ip_address'   => ['type' => self::STR, 'maxLength' => 45, 'nullable' => true, 'default' => null],
            'last_used_ip' => ['type' => self::STR, 'maxLength' => 45, 'nullable' => true, 'default' => null],
            'last_used_ua' => ['type' => self::STR, 'maxLength' => 255, 'nullable' => true, 'default' => null],
        ];
        $structure->relations = [
            'User' => [
                'entity'     => 'XF:User',
                'type'       => self::TO_ONE,
                'conditions' => 'user_id',
                'primary'    => true,
            ],
            'RevokedBy' => [
                'entity'     => 'XF:User',
                'type'       => self::TO_ONE,
                'conditions' => [['user_id', '=', '$revoked_by']],
                'primary'    => true,
            ],
        ];

        return $structure;
    }
}
