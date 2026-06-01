<?php
// FROM HASH: e05217de0059d6693b36ad7345cbad05
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'multi_quote_macros::block', array(
		'quotes' => $__vars['quotes'],
		'messages' => $__vars['messages'],
		'containerRelation' => 'Conversation',
		'dateKey' => 'message_date',
		'bbCodeContext' => 'conversation_message',
	), $__vars) . '
';
	return $__finalCompiled;
}
);