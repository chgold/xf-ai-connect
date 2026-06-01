<?php
// FROM HASH: a9e3fbecf415c27776b8f01f357ba2d1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Search direct messages');
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'search_form_macros::keywords', array(
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->callMacro(null, 'search_form_macros::user', array(
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'c[recipients]',
		'value' => $__vars['input']['c']['recipients'],
		'ac' => 'true',
	), array(
		'label' => 'Received by',
		'explain' => 'You may enter multiple names here.',
	)) . '

' . $__templater->callMacro(null, 'search_form_macros::date', array(
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->formNumberBoxRow(array(
		'name' => 'c[min_reply_count]',
		'value' => $__templater->filter($__vars['input']['c']['min_reply_count'], array(array('default', array(0, )),), false),
		'min' => '0',
	), array(
		'label' => 'Minimum number of replies',
	)) . '

';
	if ($__vars['input']['c']['conversation']) {
		$__finalCompiled .= '
	' . $__templater->formCheckBoxRow(array(
		), array(array(
			'name' => 'c[conversation]',
			'value' => $__vars['input']['c']['conversation'],
			'selected' => true,
			'label' => '
			' . 'Restrict search to the specified direct message' . '
		',
			'_type' => 'option',
		)), array(
		)) . '
';
	}
	$__finalCompiled .= '

' . $__templater->callMacro('search_form_macros', 'order', array(
		'isRelevanceSupported' => $__vars['isRelevanceSupported'],
		'options' => array('replies' => 'Most replies', ),
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->callMacro(null, 'search_form_macros::grouped', array(
		'label' => 'Display results as direct messages',
		'input' => $__vars['input'],
	), $__vars);
	return $__finalCompiled;
}
);