<?php
// FROM HASH: 390425ac6dc216fe47222c2660053bfc
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'search_form_macros::keywords', array(
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->callMacro(null, 'search_form_macros::user', array(
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->callMacro(null, 'search_form_macros::date', array(
		'input' => $__vars['input'],
	), $__vars) . '

' . $__templater->callMacro(null, 'search_form_macros::order', array(
		'isRelevanceSupported' => $__vars['isRelevanceSupported'],
		'input' => $__vars['input'],
	), $__vars) . '
';
	return $__finalCompiled;
}
);