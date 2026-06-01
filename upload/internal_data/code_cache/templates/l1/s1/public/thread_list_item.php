<?php
// FROM HASH: fcf63a3242ab27389ecd62b1ca9a69b1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, ($__vars['templateOverrides']['thread_list_macro'] ?: 'thread_list_macros::item'), $__templater->combineMacroArgumentAttributes($__vars['templateOverrides']['thread_list_macro_args'], array(
		'thread' => $__vars['thread'],
		'forum' => $__vars['forum'],
		'forceRead' => $__vars['inlineMode'],
		'allowInlineMod' => false,
	)), $__vars);
	return $__finalCompiled;
}
);