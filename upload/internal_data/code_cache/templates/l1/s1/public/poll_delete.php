<?php
// FROM HASH: 2a4efe37de5589c42af16ba1fc220e50
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Reset poll');
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'poll_macros::delete_block', array(
		'poll' => $__vars['poll'],
	), $__vars);
	return $__finalCompiled;
}
);