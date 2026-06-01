<?php
// FROM HASH: 4c159eebc39f3019df1e8024414a1c2e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'style_variation_macros::variation_input', array(
		'style' => $__vars['style'],
		'value' => $__vars['variation'],
	), $__vars);
	return $__finalCompiled;
}
);