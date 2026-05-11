<?php

namespace chgold\AIConnect\Admin\Controller;

use XF\Admin\Controller\AbstractController;
use XF\Mvc\ParameterBag;

class Tokens extends AbstractController
{
    public function actionIndex()
    {
        $filter = $this->filter('filter', 'str') ?: 'active';

        /** @var \chgold\AIConnect\Repository\TokenRegistry $repo */
        $repo   = $this->repository('chgold\AIConnect:TokenRegistry');
        $finder = $repo->findTokensForList();

        if ($filter === 'active') {
            $finder->whereOr(
                ['revoked_at', '=', null],
                ['revoked_at', '=', 0]
            );
        } elseif ($filter === 'revoked') {
            $finder->where('revoked_at', '>', 0);
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
        $tokenId = (int) $params->token_id;
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
}
