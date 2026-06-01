<?php
// FROM HASH: 9102a0915f7bb99df41c5df639914581
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->isTraversable($__vars['profilePostComments'])) {
		foreach ($__vars['profilePostComments'] AS $__vars['profilePostComment']) {
			$__finalCompiled .= '
	' . $__templater->callMacro(null, 'profile_post_macros::comment', array(
				'profilePost' => $__vars['profilePost'],
				'comment' => $__vars['profilePostComment'],
			), $__vars) . '
';
		}
	}
	return $__finalCompiled;
}
);