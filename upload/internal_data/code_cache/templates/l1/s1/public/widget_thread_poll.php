<?php
// FROM HASH: f22fe5ba1a55e21374e1567b7f3841e0
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'poll_macros::poll_block', array(
		'poll' => $__vars['poll'],
		'simpleDisplay' => true,
		'forceTitle' => $__vars['title'],
	), $__vars);
	return $__finalCompiled;
}
);