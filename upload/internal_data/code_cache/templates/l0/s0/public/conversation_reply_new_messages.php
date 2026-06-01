<?php
// FROM HASH: 5b8a2e9b1f864c72259706467fa341be
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__vars['firstUnshownMessage']) {
		$__finalCompiled .= '
	<div class="message">
		<div class="message-inner">
			<div class="message-cell message-cell--alert">
				' . 'There are more messages to display.' . ' <a href="' . $__templater->func('link', array('direct-messages/replies', $__vars['firstUnshownMessage'], ), true) . '">' . 'View them?' . '</a>
			</div>
		</div>
	</div>
';
	}
	$__finalCompiled .= '

';
	if ($__templater->isTraversable($__vars['messages'])) {
		foreach ($__vars['messages'] AS $__vars['message']) {
			$__finalCompiled .= '
	' . $__templater->callMacro(null, 'conversation_message_macros::message', array(
				'message' => $__vars['message'],
				'conversation' => $__vars['conversation'],
			), $__vars) . '
';
		}
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
);