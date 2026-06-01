<?php
// FROM HASH: a4744c77bcf5fb0b494f27583586caa4
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'prefix_macros::select', array(
		'name' => 'na',
		'prefixes' => $__vars['prefixes'],
		'type' => 'thread',
	), $__vars) . '
';
	return $__finalCompiled;
}
);