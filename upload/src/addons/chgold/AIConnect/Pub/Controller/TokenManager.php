<?php

namespace chgold\AIConnect\Pub\Controller;

use XF\Mvc\ParameterBag;
use XF\Pub\Controller\AbstractController;

class TokenManager extends AbstractController
{
    protected function preDispatchController($action, ParameterBag $params)
    {
        $this->assertRegistrationRequired();
    }

    public function actionIndex()
    {
        $visitor = \XF::visitor();
        $filter  = $this->filter('filter', 'str') ?: 'active';
        $time    = \XF::$time;

        $userId = (int)$visitor->user_id;
        $where  = 'user_id = ' . $userId;

        switch ($filter) {
            case 'active':
                $where .= ' AND revoked_at IS NULL AND expires_at > ' . $time;
                break;
            case 'unused':
                $where .= ' AND last_used_at IS NULL AND revoked_at IS NULL';
                break;
            case 'inactive':
                $where .= ' AND last_used_at IS NOT NULL AND last_used_at < ' . ($time - 30 * 86400)
                    . ' AND revoked_at IS NULL';
                break;
            case 'revoked':
                $where .= ' AND revoked_at IS NOT NULL';
                break;
        }

        $tokens = $this->db()->fetchAll(
            "SELECT * FROM xf_chgold_aiconnect_token_registry WHERE {$where} ORDER BY issued_at DESC LIMIT 200"
        );

        return $this->view(
            'chgold\AIConnect:TokenManager\Index',
            'aiconnect_my_tokens',
            [
                'tokens' => $tokens,
                'filter' => $filter,
            ]
        );
    }

    public function actionRevoke(ParameterBag $params)
    {
        $visitor = \XF::visitor();
        $tokenId = (int)$params->token_id;

        if (!$tokenId) {
            return $this->error(\XF::phrase('aiconnect_tokens_invalid_id'));
        }

        /** @var \chgold\AIConnect\Entity\TokenRegistry $token */
        $token = $this->assertRecordExists('chgold\AIConnect:TokenRegistry', $tokenId);

        if ((int)$token->user_id !== (int)$visitor->user_id) {
            return $this->noPermission();
        }

        if ($this->isPost()) {
            /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
            $repo = $this->repository('chgold\AIConnect:TokenRegistry');
            $repo->revokeById($tokenId, $visitor->user_id);

            return $this->redirect(
                $this->buildLink('ai-connect-tokens'),
                \XF::phrase('aiconnect_my_tokens_revoked_success')
            );
        }

        return $this->view(
            'chgold\AIConnect:TokenManager\Revoke',
            'aiconnect_my_tokens_revoke',
            ['token' => $token]
        );
    }

    public function actionRevokeAll()
    {
        $this->assertPostOnly();

        $visitor = \XF::visitor();

        /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
        $repo = $this->repository('chgold\AIConnect:TokenRegistry');
        $repo->revokeAllForUser($visitor->user_id, $visitor->user_id);

        return $this->redirect(
            $this->buildLink('ai-connect-tokens'),
            \XF::phrase('aiconnect_my_tokens_revoke_all_success')
        );
    }
}
