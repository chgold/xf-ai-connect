<?php
// FROM HASH: f37c2cbc380e9e4a671494674321e897
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formRow('

	' . $__templater->callMacro(null, 'helper_callback_fields::callback_fields', array(
		'className' => $__vars['formPrefix'] . '[callback_class]',
		'methodName' => $__vars['formPrefix'] . '[callback_method]',
		'classValue' => $__vars['config']['callback']['0'],
		'methodValue' => $__vars['config']['callback']['1'],
	), $__vars) . '
', array(
		'rowtype' => 'input',
		'label' => 'Callback',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => $__vars['formPrefix'] . '[context]',
		'value' => $__vars['config']['context'],
	), array(
		'label' => 'Context value',
		'explain' => 'Any value you enter here will be passed to your callback to provide additional context.',
	));
	return $__finalCompiled;
}
);