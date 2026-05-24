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
        $finder = $this->applyTokenFilter(
            $repo->findTokensForUser((int)$visitor->user_id),
            $filter,
            $time
        );

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
        $visitor = \XF::visitor();
        $filter  = $this->filter('filter', 'str') ?: 'active';
        $time    = \XF::$time;

        /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
        $repo   = $this->repository('chgold\AIConnect:TokenRegistry');
        $finder = $this->applyTokenFilter(
            $repo->findTokensForUser((int)$visitor->user_id),
            $filter,
            $time
        );

        if ($this->isPost()) {
            $tokens   = $finder->fetch();
            $tokenIds = $tokens->keys();

            $count = 0;
            if (!empty($tokenIds)) {
                $count = (int)\XF::db()->update(
                    'xf_chgold_aiconnect_token_registry',
                    [
                        'revoked_at' => $time,
                        'revoked_by' => $visitor->user_id ?: 0,
                    ],
                    'id IN (' . \XF::db()->quote($tokenIds) . ')
                        AND (revoked_at IS NULL OR revoked_at = 0)'
                );
            }

            return $this->redirect(
                $this->buildLink('ai-connect-tokens', null, ['filter' => $filter]),
                \XF::phrase('aiconnect_my_tokens_revoke_all_success_x', ['count' => $count])
            );
        }

        $count = (int)$finder->total();

        return $this->view(
            'chgold\AIConnect:TokenManager\RevokeAll',
            'aiconnect_my_tokens_revoke_all',
            [
                'filter' => $filter,
                'count'  => $count,
            ]
        );
    }

    /**
     * Applies the same filter logic used by actionIndex() to a finder.
     * Keeps the bulk "revoke all" action consistent with the visible list.
     *
     * @return \XF\Mvc\Entity\Finder
     */
    protected function applyTokenFilter(\XF\Mvc\Entity\Finder $finder, string $filter, int $time): \XF\Mvc\Entity\Finder
    {
        switch ($filter) {
            case 'active':
                $finder->whereOr(['revoked_at', '=', null], ['revoked_at', '=', 0]);
                $finder->whereOr(
                    ['expires_at', '>', $time],
                    ['refresh_expires_at', '>', $time]
                );
                break;
            case 'renewable':
                $finder->whereOr(['revoked_at', '=', null], ['revoked_at', '=', 0]);
                $finder->where('expires_at', '<=', $time);
                $finder->where('refresh_expires_at', '>', $time);
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
            // 'all' = no filter
        }
        return $finder;
    }
}
