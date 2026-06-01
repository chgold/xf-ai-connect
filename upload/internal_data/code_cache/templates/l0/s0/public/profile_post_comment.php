<?php
// FROM HASH: fbd330e64722e59e12aa83e55e7fc5d5
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->setPageParam('head.' . 'metaNoindex', $__templater->preEscaped('<meta name="robots" content="noindex" />'));
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		<div class="block-body">
			' . $__templater->callMacro(null, 'profile_post_macros::comment', array(
		'comment' => $__vars['comment'],
		'profilePost' => $__vars['profilePost'],
	), $__vars) . '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);