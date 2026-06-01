<?php
// FROM HASH: 0db046f6c3a5945e96944d2b7b3543f2
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'tag_macros::list', array(
		'tags' => $__vars['thread']['tags'],
		'tagList' => 'tagList--thread-' . $__vars['thread']['thread_id'],
		'editLink' => ($__templater->method($__vars['thread'], 'canEditTags', array()) ? $__templater->func('link', array('threads/tags', $__vars['thread'], ), false) : ''),
	), $__vars);
	return $__finalCompiled;
}
);