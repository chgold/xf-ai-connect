<?php
// FROM HASH: 16d1bbe905c9f1952ae01ff0bbc0d4f7
return array(
'extends' => function($__templater, array $__vars) { return 'thread_view'; },
'extensions' => array('above_messages' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
	';
	if ($__vars['poll']) {
		$__finalCompiled .= '
		' . $__templater->callMacro(null, 'poll_macros::poll_block', array(
			'poll' => $__vars['poll'],
		), $__vars) . '
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

' . $__templater->renderExtension('above_messages', $__vars, $__extensions);
	return $__finalCompiled;
}
);