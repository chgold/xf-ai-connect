<?php
// FROM HASH: 6f62a6f3ea6a685bb23527ea458c7614
return array(
'macros' => array('suggestion' => array(
'extends' => 'post_macros::post',
'extensions' => array('main_cell' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	if ($__templater->method($__vars['thread'], 'isContentVotingSupported', array())) {
		$__finalCompiled .= '
			<div class="message-cell message-cell--vote">
				' . $__templater->callMacro(null, 'content_vote_macros::vote_control', array(
			'link' => 'threads/vote',
			'content' => $__vars['thread'],
		), $__vars) . '
			</div>
		';
	}
	$__finalCompiled .= '
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	' . $__templater->renderExtension('main_cell', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';

	return $__finalCompiled;
}
);