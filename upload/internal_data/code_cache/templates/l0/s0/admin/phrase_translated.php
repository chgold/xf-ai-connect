<?php
// FROM HASH: 1770a998f9795721ee2cbbbbd7345486
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'phrase_translate_macros::collapsed', array(
		'phrase' => $__vars['phrase'],
		'language' => $__vars['language'],
	), $__vars);
	return $__finalCompiled;
}
);