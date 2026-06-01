<?php
// FROM HASH: 51332bb0311cfbb4278d619ad1260844
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, ($__vars['templateOverrides']['thread_list_macro'] ?: 'thread_list_macros::item'), $__templater->combineMacroArgumentAttributes($__vars['templateOverrides']['thread_list_macro_args'], array(
		'thread' => $__vars['thread'],
		'allowInlineMod' => ($__vars['noInlineMod'] ? false : true),
		'forum' => ($__vars['forumName'] ? null : $__vars['forum']),
	)), $__vars) . '
';
	return $__finalCompiled;
}
);