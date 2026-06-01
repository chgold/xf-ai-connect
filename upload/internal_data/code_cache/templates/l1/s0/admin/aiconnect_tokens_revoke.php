<?php
// FROM HASH: c9c96a99c192d7d181276bb50c7f6af7
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Revoke token');
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__vars['token']['User']) {
		$__compilerTemp1 .= $__templater->func('username_link', array($__vars['token']['User'], false, array(
		)));
	} else {
		$__compilerTemp1 .= '#' . $__templater->escape($__vars['token']['user_id']);
	}
	$__finalCompiled .= $__templater->form('
    <div class="block-container">
        <div class="block-body">
            <div class="block-row">
                <p>' . 'Are you sure you want to revoke the token starting with "' . $__templater->escape($__vars['token']['token_prefix']) . '"? Any AI agent currently using it will be denied immediately.' . '</p>
                <p><strong>' . 'User' . ':</strong>
                    ' . $__compilerTemp1 . '
                </p>
                <p><strong>' . 'Client' . ':</strong> ' . $__templater->escape($__vars['token']['client_id']) . '</p>
                <p><strong>' . 'Scope' . ':</strong> ' . $__templater->escape($__vars['token']['scope']) . '</p>
            </div>
        </div>
        ' . $__templater->formSubmitRow(array(
		'icon' => 'delete',
		'sticky' => 'true',
	), array(
	)) . '
    </div>
', array(
		'action' => $__templater->func('link', array('ai-connect/tokens/revoke', $__vars['token'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);