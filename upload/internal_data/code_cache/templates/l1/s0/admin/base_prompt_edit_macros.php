<?php
// FROM HASH: 9bbc5b3a4d3ad8a4538b0f9dfd1b0570
return array(
'macros' => array('prompt_groups' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'prompt' => '!',
		'promptGroups' => '!',
		'withRow' => '1',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	if ($__vars['withRow']) {
		$__finalCompiled .= '
		';
		$__compilerTemp1 = array(array(
			'value' => '',
			'label' => $__vars['xf']['language']['parenthesis_open'] . 'None' . $__vars['xf']['language']['parenthesis_close'],
			'_type' => 'option',
		));
		$__compilerTemp1 = $__templater->mergeChoiceOptions($__compilerTemp1, $__vars['promptGroups']);
		$__finalCompiled .= $__templater->formSelectRow(array(
			'name' => 'prompt_group_id',
			'value' => $__vars['prompt'],
		), $__compilerTemp1, array(
			'label' => 'Prompt group',
		)) . '
	';
	} else {
		$__finalCompiled .= '
		';
		$__compilerTemp2 = array(array(
			'value' => '',
			'label' => $__vars['xf']['language']['parenthesis_open'] . 'None' . $__vars['xf']['language']['parenthesis_close'],
			'_type' => 'option',
		));
		$__compilerTemp2 = $__templater->mergeChoiceOptions($__compilerTemp2, $__vars['promptGroups']);
		$__finalCompiled .= $__templater->formSelect(array(
			'name' => 'prompt_group_id',
			'value' => $__vars['prompt'],
		), $__compilerTemp2) . '
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';

	return $__finalCompiled;
}
);