<?php

namespace chgold\AIConnect\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Tokens extends AbstractController
{
    public function actionIndex()
    {
        $filter = $this->filter('filter', 'str') ?: 'active';
        $time   = \XF::$time;

        /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
        $repo   = $this->repository('chgold\AIConnect:TokenRegistry');
        $finder = $repo->findTokensForList();

        switch ($filter) {
            case 'active':
                $finder->whereOr(
                    ['revoked_at', '=', null],
                    ['revoked_at', '=', 0]
                );
                break;
            case 'revoked':
                $finder->where('revoked_at', '>', 0);
                break;
            case 'unused':
                $finder->where('last_used_at', null);
                $finder->whereOr(['revoked_at', '=', null], ['revoked_at', '=', 0]);
                $finder->where('expires_at', '>', $time);
                break;
            case 'inactive':
                $finder->where('last_used_at', '>', 0);
                $finder->where('last_used_at', '<', $time - 30 * 86400);
                $finder->whereOr(['revoked_at', '=', null], ['revoked_at', '=', 0]);
                break;
        }

        $tokens = $finder->limit(500)->fetch();

        return $this->view(
            'chgold\AIConnect:Tokens\List',
            'aiconnect_tokens_list',
            [
                'tokens' => $tokens,
                'filter' => $filter,
            ]
        );
    }

    public function actionRevoke(ParameterBag $params)
    {
        $tokenId = (int)$params->token_id;
        if (!$tokenId) {
            return $this->error(\XF::phrase('aiconnect_tokens_invalid_id'));
        }

        /** @var \chgold\AIConnect\Entity\TokenRegistry $token */
        $token = $this->assertRecordExists('chgold\AIConnect:TokenRegistry', $tokenId);

        if ($this->isPost()) {
            /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
            $repo = $this->repository('chgold\AIConnect:TokenRegistry');
            $repo->revokeByPrefix($token->token_prefix, \XF::visitor()->user_id ?: null);

            return $this->redirect($this->buildLink('ai-connect/tokens'));
        }

        return $this->view(
            'chgold\AIConnect:Tokens\Revoke',
            'aiconnect_tokens_revoke',
            ['token' => $token]
        );
    }

    public function actionRevokeUnused()
    {
        $this->assertPostOnly();

        /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
        $repo  = $this->repository('chgold\AIConnect:TokenRegistry');
        $count = $repo->revokeUnused(30, \XF::visitor()->user_id ?: null);

        return $this->redirect(
            $this->buildLink('ai-connect/tokens'),
            \XF::phrase('aiconnect_tokens_bulk_revoked_x', ['count' => $count])
        );
    }

    public function actionRevokeInactive()
    {
        $this->assertPostOnly();

        /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
        $repo  = $this->repository('chgold\AIConnect:TokenRegistry');
        $count = $repo->revokeInactive(180, \XF::visitor()->user_id ?: null);

        return $this->redirect(
            $this->buildLink('ai-connect/tokens'),
            \XF::phrase('aiconnect_tokens_bulk_revoked_x', ['count' => $count])
        );
    }

    public function actionRevokeAll()
    {
        $this->assertPostOnly();

        $db    = $this->db();
        $count = $db->update(
            'xf_chgold_aiconnect_token_registry',
            [
                'revoked_at' => \XF::$time,
                'revoked_by' => \XF::visitor()->user_id ?: 0,
            ],
            'revoked_at IS NULL'
        );

        return $this->redirect(
            $this->buildLink('ai-connect/tokens'),
            \XF::phrase('aiconnect_tokens_bulk_revoked_x', ['count' => $count])
        );
    }
}
