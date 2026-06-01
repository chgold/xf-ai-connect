<?php
// FROM HASH: 176dc925f5675cd009d9954ded56400c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit signature');
	$__finalCompiled .= '

';
	$__templater->wrapTemplate('account_wrapper', $__vars);
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formEditorRow(array(
		'name' => 'signature',
		'value' => $__vars['xf']['visitor']['Profile'],
		'column' => 'signature_',
		'removebuttons' => $__vars['disabledButtons'],
	), array(
		'rowtype' => 'fullWidth noLabel',
		'label' => 'Signature',
	)) . '
		</div>
		' . $__templater->formSubmitRow(array(
		'icon' => 'save',
	), array(
		'rowtype' => 'simple',
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('account/signature', ), false),
		'ajax' => 'true',
		'class' => 'block',
		'data-redirect' => 'off',
	));
	return $__finalCompiled;
}
);