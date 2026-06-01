<?php
// FROM HASH: a9297c02915137d7850b638346d91130
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Cast vote');
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'poll_macros::poll_block', array(
		'poll' => $__vars['poll'],
		'showVoteBlock' => true,
		'simpleDisplay' => $__vars['simpleDisplay'],
	), $__vars) . '
';
	return $__finalCompiled;
}
);