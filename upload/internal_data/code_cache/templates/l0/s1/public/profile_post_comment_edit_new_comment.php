<?php
// FROM HASH: 8d172df75ca06fc3b1f91ab8cf6f219d
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'profile_post_macros::comment', array(
		'profilePost' => $__vars['profilePost'],
		'comment' => $__vars['comment'],
	), $__vars);
	return $__finalCompiled;
}
);