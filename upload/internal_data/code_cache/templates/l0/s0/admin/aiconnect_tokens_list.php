<?php
// FROM HASH: 4c9999411259b8d0e4cd0965a0497031
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('AI Connect — Token Registry');
	$__finalCompiled .= '

<div class="block">
    <div class="block-container">
        <div class="block-body">
            <div class="block-row block-row--separated">
                <div class="formRow">
                    <div class="formRow-label">' . 'Filter' . '</div>
                    <div class="formRow-content">
                        ' . $__templater->formSelect(array(
		'name' => 'filter',
		'value' => $__vars['filter'],
		'data-xf-init' => 'submit-on-change',
	), array(array(
		'value' => 'active',
		'label' => 'Active only',
		'_type' => 'option',
	),
	array(
		'value' => 'unused',
		'label' => 'Unused (never used)',
		'_type' => 'option',
	),
	array(
		'value' => 'inactive',
		'label' => 'Inactive (30+ days)',
		'_type' => 'option',
	),
	array(
		'value' => 'revoked',
		'label' => 'Revoked only',
		'_type' => 'option',
	),
	array(
		'value' => 'all',
		'label' => 'All',
		'_type' => 'option',
	))) . '
                    </div>
                </div>
            </div>
            <div class="block-row" style="display:flex;gap:8px;flex-wrap:wrap;align-items:center;padding:8px 0;">
                ' . $__templater->form('
                    ' . $__templater->formSubmitRow(array(
		'submit' => 'Revoke all unused (30 days)',
		'icon' => 'delete',
		'class' => 'button--minor',
	), array(
	)) . '
                ', array(
		'action' => $__templater->func('link', array('ai-connect/revoke-unused', ), false),
		'ajax' => 'true',
	)) . '
                ' . $__templater->form('
                    ' . $__templater->formSubmitRow(array(
		'submit' => 'Revoke all inactive (180 days)',
		'icon' => 'delete',
		'class' => 'button--minor',
	), array(
	)) . '
                ', array(
		'action' => $__templater->func('link', array('ai-connect/revoke-inactive', ), false),
		'ajax' => 'true',
	)) . '
                ' . $__templater->form('
                    ' . $__templater->formSubmitRow(array(
		'submit' => 'Revoke ALL active tokens',
		'icon' => 'delete',
		'class' => 'button--danger',
	), array(
	)) . '
                ', array(
		'action' => $__templater->func('link', array('ai-connect/revoke-all', ), false),
		'ajax' => 'true',
	)) . '
            </div>
        </div>
    </div>
</div>

<div class="block">
    <div class="block-container">
        <h2 class="block-tabHeader tabs hScroller" data-xf-init="h-scroller tabs">
            <span class="hScroller-scroll">
                <span class="tabs-tab is-active">' . 'AI Connect — Token Registry' . ' (' . $__templater->filter($__vars['tokens'], array(array('count', array()),), true) . ')</span>
            </span>
        </h2>
        <div class="block-body">
            ';
	if ($__templater->test($__vars['tokens'], 'empty', array())) {
		$__finalCompiled .= '
                <div class="block-row block-row--separated">' . 'No tokens have been recorded in the registry yet.' . '</div>
            ';
	} else {
		$__finalCompiled .= '
                <table class="dataList-table" style="width:100%">
                    <thead>
                        <tr class="dataList-row dataList-row--header">
                            <th class="dataList-cell">' . 'Token prefix' . '</th>
                            <th class="dataList-cell">' . 'User' . '</th>
                            <th class="dataList-cell">' . 'Client' . '</th>
                            <th class="dataList-cell">' . 'Source' . '</th>
                            <th class="dataList-cell">' . 'Scope' . '</th>
                            <th class="dataList-cell">' . 'Issued' . '</th>
                            <th class="dataList-cell">' . 'Last used' . '</th>
                            <th class="dataList-cell">' . 'Last IP' . '</th>
                            <th class="dataList-cell">' . 'Last client' . '</th>
                            <th class="dataList-cell">' . 'Status' . '</th>
                            <th class="dataList-cell">' . 'Actions' . '</th>
                        </tr>
                    </thead>
                    <tbody>
                        ';
		if ($__templater->isTraversable($__vars['tokens'])) {
			foreach ($__vars['tokens'] AS $__vars['token']) {
				$__finalCompiled .= '
                            <tr class="dataList-row">
                                <td class="dataList-cell"><code>' . $__templater->escape($__vars['token']['token_prefix']) . '...</code></td>
                                <td class="dataList-cell">
                                    ';
				if ($__vars['token']['User']) {
					$__finalCompiled .= '
                                        ' . $__templater->func('username_link', array($__vars['token']['User'], false, array(
					))) . '
                                    ';
				} else {
					$__finalCompiled .= '
                                        #' . $__templater->escape($__vars['token']['user_id']) . '
                                    ';
				}
				$__finalCompiled .= '
                                </td>
                                <td class="dataList-cell">' . $__templater->escape($__vars['token']['client_id']) . '</td>
                                <td class="dataList-cell">' . $__templater->escape($__vars['token']['source']) . '</td>
                                <td class="dataList-cell">' . $__templater->escape($__vars['token']['scope']) . '</td>
                                <td class="dataList-cell">' . $__templater->func('date_dynamic', array($__vars['token']['issued_at'], array(
				))) . '</td>
                                <td class="dataList-cell">
                                    ';
				if ($__vars['token']['last_used_at']) {
					$__finalCompiled .= '
                                        ' . $__templater->func('date_dynamic', array($__vars['token']['last_used_at'], array(
					))) . '
                                    ';
				} else {
					$__finalCompiled .= '
                                        —
                                    ';
				}
				$__finalCompiled .= '
                                </td>
                                <td class="dataList-cell">' . ($__templater->escape($__vars['token']['last_used_ip']) ?: '—') . '</td>
                                <td class="dataList-cell" title="' . $__templater->escape($__vars['token']['last_used_ua']) . '">' . ($__vars['token']['last_used_ua'] ? $__templater->func('substr', array($__vars['token']['last_used_ua'], 0, 40, ), true) : '—') . '</td>
                                <td class="dataList-cell">
                                    ';
				if ($__vars['token']['revoked_at']) {
					$__finalCompiled .= '
                                        <span class="label label--red">' . 'Revoked' . '</span>
                                    ';
				} else if ($__vars['token']['expires_at'] < $__vars['xf']['time']) {
					$__finalCompiled .= '
                                        <span class="label label--orange">' . 'Expired' . '</span>
                                    ';
				} else {
					$__finalCompiled .= '
                                        <span class="label label--green">' . 'Active' . '</span>
                                    ';
				}
				$__finalCompiled .= '
                                </td>
                                <td class="dataList-cell">
                                    ';
				if (!$__vars['token']['revoked_at']) {
					$__finalCompiled .= '
                                        ' . $__templater->button('Revoke', array(
						'href' => $__templater->func('link', array('ai-connect/tokens/revoke', $__vars['token'], ), false),
						'overlay' => 'true',
						'class' => 'button--link',
					), '', array(
					)) . '
                                    ';
				}
				$__finalCompiled .= '
                                </td>
                            </tr>
                        ';
			}
		}
		$__finalCompiled .= '
                    </tbody>
                </table>
            ';
	}
	$__finalCompiled .= '
        </div>
    </div>
</div>';
	return $__finalCompiled;
}
);