<?php
// FROM HASH: c9b5f2c77917bcfe8cedf18d3a56b9bd
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Revoke token');
	$__finalCompiled .= '

' . $__templater->form('
    <div class="block-container">
        <div class="block-body">
            <div class="block-row">
                <p>' . 'Revoke the token starting with "' . $__templater->escape($__vars['token']['token_prefix']) . '"? Any AI agent using it will be disconnected.' . '</p>
                <p><strong>' . 'Type' . ':</strong> ' . $__templater->escape($__vars['token']['source']) . '</p>
                <p><strong>' . 'Created' . ':</strong> ' . $__templater->func('date_dynamic', array($__vars['token']['issued_at'], array(
	))) . '</p>
            </div>
        </div>
        ' . $__templater->formSubmitRow(array(
		'icon' => 'delete',
		'sticky' => 'true',
	), array(
	)) . '
    </div>
', array(
		'action' => $__templater->func('link', array('ai-connect-tokens/revoke', $__vars['token'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);