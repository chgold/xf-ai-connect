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

        /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
        $repo   = $this->repository('chgold\AIConnect:TokenRegistry');
        $finder = $repo->findTokensForUser((int)$visitor->user_id);

        switch ($filter) {
            case 'active':
                $finder->whereOr(
                    ['revoked_at', '=', null],
                    ['revoked_at', '=', 0]
                )->where('expires_at', '>', $time);
                break;
            case 'unused':
                $finder->where('last_used_at', null);
                $finder->whereOr(['revoked_at', '=', null], ['revoked_at', '=', 0]);
                break;
            case 'inactive':
                $finder->where('last_used_at', '>', 0)
                       ->where('last_used_at', '<', $time - 30 * 86400);
                $finder->whereOr(['revoked_at', '=', null], ['revoked_at', '=', 0]);
                break;
            case 'revoked':
                $finder->where('revoked_at', '>', 0);
                break;
        }

        $tokens = $finder->limit(200)->fetch();

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
        $tokenId = (int)($params->id ?: $params->token_id);

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
