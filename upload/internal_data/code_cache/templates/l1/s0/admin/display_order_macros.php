<?php
// FROM HASH: f06e2cca12930b0ff62a7e988ca49a90
return array(
'macros' => array('row' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'name' => 'display_order',
		'value' => '',
		'step' => '1',
		'label' => 'Display order',
		'hint' => '',
		'readOnly' => '',
		'explain' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->formNumberBoxRow(array(
		'name' => $__vars['name'],
		'value' => $__vars['value'],
		'min' => '0',
		'step' => $__vars['step'],
		'readonly' => ($__vars['readOnly'] ? 'readonly' : ''),
	), array(
		'label' => $__templater->escape($__vars['label']),
		'hint' => $__templater->escape($__vars['hint']),
		'explain' => $__templater->escape($__vars['explain']),
	)) . '
';
	return $__finalCompiled;
}
),
'input' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'name' => 'display_order',
		'value' => '',
		'step' => '1',
		'readOnly' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->formNumberBox(array(
		'name' => $__vars['name'],
		'value' => $__vars['value'],
		'min' => '0',
		'step' => $__vars['step'],
		'readonly' => ($__vars['readOnly'] ? 'readonly' : ''),
	)) . '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

';
	return $__finalCompiled;
}
);