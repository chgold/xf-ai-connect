<?php
// FROM HASH: 845f17c13f52f480b0f7f7997380dfb2
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'phrase_translate_macros::expanded', array(
		'phrase' => $__vars['phrase'],
		'language' => $__vars['language'],
	), $__vars);
	return $__finalCompiled;
}
);