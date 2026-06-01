<?php
// FROM HASH: afeba7c3791818ab84fa5abaff7725d9
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'conversation_message_macros::message', array(
		'message' => $__vars['message'],
		'conversation' => $__vars['conversation'],
	), $__vars);
	return $__finalCompiled;
}
);