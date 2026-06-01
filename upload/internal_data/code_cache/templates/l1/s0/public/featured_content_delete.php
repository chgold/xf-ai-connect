<?php
// FROM HASH: 86f86b48dcdef34c23361b44f8efc118
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Unfeature content');
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'Are you sure you want to unfeature this content?' . '
			', array(
		'rowtype' => 'confirm',
	)) . '
		</div>

		' . $__templater->func('redirect_input', array(null, null, true)) . '

		' . $__templater->formSubmitRow(array(
		'submit' => 'Unfeature',
		'icon' => 'delete',
	), array(
		'rowtype' => 'simple',
	)) . '
	</div>
', array(
		'action' => $__vars['confirmUrl'],
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);