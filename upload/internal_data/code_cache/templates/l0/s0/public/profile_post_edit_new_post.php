<?php
// FROM HASH: af2d4a137024c1c9993e5c0127ba47a7
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'profile_post_macros::profile_post', array(
		'profilePost' => $__vars['profilePost'],
		'allowInlineMod' => ($__vars['noInlineMod'] ? false : true),
		'attachmentData' => $__vars['attachmentData'],
	), $__vars);
	return $__finalCompiled;
}
);