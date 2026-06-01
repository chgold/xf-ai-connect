<?php
// FROM HASH: 8714fe15d99d7ec892f1e782c32ec2bd
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'prefix_macros::usage_help', array(
		'prefix' => $__vars['prefix'],
		'contentType' => $__vars['contentType'],
	), $__vars);
	return $__finalCompiled;
}
);