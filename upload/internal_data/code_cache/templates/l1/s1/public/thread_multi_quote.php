<?php
// FROM HASH: 6e21c4df3ed7aabf8ece90a96681b985
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'multi_quote_macros::block', array(
		'quotes' => $__vars['quotes'],
		'messages' => $__vars['posts'],
		'containerRelation' => 'Thread',
		'dateKey' => 'post_date',
		'bbCodeContext' => 'post',
	), $__vars) . '
';
	return $__finalCompiled;
}
);