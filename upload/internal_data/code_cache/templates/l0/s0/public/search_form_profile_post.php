<?php
// FROM HASH: ff5f2bb8d0c9f525ec1ddf70118e0a96
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Search profile posts');
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'search_form_macros::keywords', array(
		'input' => $__vars['input'],
		'canTitleLimit' => false,
	), $__vars) . '

' . $__templater->callMacro(null, 'search_form_macros::user', array(
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'c[profile_users]',
		'value' => $__vars['input']['c']['profile_users'],
		'ac' => 'true',
	), array(
		'label' => 'Posted on the profile of member',
		'explain' => 'You may enter multiple names here.',
	)) . '

' . $__templater->callMacro(null, 'search_form_macros::date', array(
		'input' => $__vars['input'],
	), $__vars);
	return $__finalCompiled;
}
);