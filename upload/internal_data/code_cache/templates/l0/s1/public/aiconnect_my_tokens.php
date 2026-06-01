<?php
// FROM HASH: 70f278acf32e42a6f29f9f2450291841
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('My AI Connect Tokens');
	$__finalCompiled .= '

<div class="block">
    <div class="block-container">
        <div class="block-body">
            <div class="block-row">
                <p>' . 'These are the access tokens you have issued for AI agents. Revoke any token to immediately disconnect the associated agent.' . '</p>
            </div>
            <div class="block-row">
                <a href="' . $__templater->func('link', array('ai-connect-tokens', null, array('filter' => 'active', ), ), true) . '" class="button' . (($__vars['filter'] == 'active') ? ' button--primary' : '') . '">' . 'Active only' . '</a>
                <a href="' . $__templater->func('link', array('ai-connect-tokens', null, array('filter' => 'renewable', ), ), true) . '" class="button' . (($__vars['filter'] == 'renewable') ? ' button--primary' : '') . '">' . 'Renewable' . '</a>
                <a href="' . $__templater->func('link', array('ai-connect-tokens', null, array('filter' => 'unused', ), ), true) . '" class="button' . (($__vars['filter'] == 'unused') ? ' button--primary' : '') . '">' . 'Unused (never used)' . '</a>
                <a href="' . $__templater->func('link', array('ai-connect-tokens', null, array('filter' => 'inactive', ), ), true) . '" class="button' . (($__vars['filter'] == 'inactive') ? ' button--primary' : '') . '">' . 'Inactive (30+ days)' . '</a>
                <a href="' . $__templater->func('link', array('ai-connect-tokens', null, array('filter' => 'all', ), ), true) . '" class="button' . (($__vars['filter'] == 'all') ? ' button--primary' : '') . '">' . 'All' . '</a>
            </div>
        </div>
    </div>
</div>

<div class="block">
    <div class="block-container">
        <div class="block-body">
            ';
	if ($__templater->test($__vars['tokens'], 'empty', array())) {
		$__finalCompiled .= '
                <div class="block-row">' . 'You have no tokens matching the selected filter.' . '</div>
            ';
	} else {
		$__finalCompiled .= '
                <table class="dataList-table" style="width:100%">
                    <thead>
                        <tr class="dataList-row dataList-row--header">
                            <th class="dataList-cell">' . 'Token' . '</th>
                            <th class="dataList-cell">' . 'Type' . '</th>
                            <th class="dataList-cell">' . 'Created' . '</th>
                            <th class="dataList-cell">' . 'Last used' . '</th>
                            <th class="dataList-cell">' . 'Last IP' . '</th>
                            <th class="dataList-cell">' . 'State' . '</th>
                            <th class="dataList-cell"></th>
                        </tr>
                    </thead>
                    <tbody>
                        ';
		if ($__templater->isTraversable($__vars['tokens'])) {
			foreach ($__vars['tokens'] AS $__vars['i'] => $__vars['token']) {
				$__finalCompiled .= '
                            <tr class="dataList-row">
                                <td class="dataList-cell"><code>' . $__templater->escape($__vars['token']['token_prefix']) . '...</code></td>
                                <td class="dataList-cell">' . $__templater->escape($__vars['token']['source']) . '</td>
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
                                        ' . 'Never' . '
                                    ';
				}
				$__finalCompiled .= '
                                </td>
                                <td class="dataList-cell">' . ($__templater->escape($__vars['token']['last_used_ip']) ?: '—') . '</td>
                                <td class="dataList-cell">
                                    ';
				if ($__vars['token']['revoked_at']) {
					$__finalCompiled .= '
                                        <span class="label label--red">' . 'Revoked' . '</span>
                                    ';
				} else if (($__vars['token']['expires_at'] <= $__vars['xf']['time']) AND ($__vars['token']['refresh_expires_at'] > $__vars['xf']['time'])) {
					$__finalCompiled .= '
                                        <span class="label label--blue">' . 'Renewable' . '</span>
                                    ';
				} else if (!$__vars['token']['last_used_at']) {
					$__finalCompiled .= '
                                        <span class="label label--warning">' . 'Not used yet' . '</span>
                                    ';
				} else if ($__vars['token']['last_used_at'] < (($__vars['xf']['time'] - 2592000))) {
					$__finalCompiled .= '
                                        <span class="label label--muted">' . 'Inactive' . '</span>
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
						'href' => $__templater->func('link', array('ai-connect-tokens/revoke', $__vars['token'], ), false),
						'overlay' => 'true',
						'class' => 'button--link button--minor',
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
        ';
	if (!$__templater->test($__vars['tokens'], 'empty', array())) {
		$__finalCompiled .= '
        <div class="block-footer">
            ' . $__templater->button('
                ' . 'Revoke all my tokens' . '
            ', array(
			'href' => $__templater->func('link', array('ai-connect-tokens/revoke-all', null, array('filter' => $__vars['filter'], ), ), false),
			'overlay' => 'true',
			'icon' => 'delete',
			'class' => 'button--minor',
		), '', array(
		)) . '
        </div>
        ';
	}
	$__finalCompiled .= '
    </div>
</div>';
	return $__finalCompiled;
}
);