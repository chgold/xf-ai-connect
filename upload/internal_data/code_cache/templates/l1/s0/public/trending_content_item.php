<?php
// FROM HASH: fb92f5e12f72cf81ea82888cb25c8d05
return array(
'macros' => array('article' => array(
'extends' => 'content_display_macros::article',
'arguments' => function($__templater, array $__vars) { return array(
		'result' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '">' . $__templater->escape($__templater->method($__vars['content'], 'getContentTitle', array())) . '</a>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('title', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'simple' => array(
'extends' => 'content_display_macros::simple',
'arguments' => function($__templater, array $__vars) { return array(
		'result' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '">' . $__templater->escape($__templater->method($__vars['content'], 'getContentTitle', array())) . '</a>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('title', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'carousel' => array(
'extends' => 'content_display_macros::carousel',
'arguments' => function($__templater, array $__vars) { return array(
		'result' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '">' . $__templater->escape($__templater->method($__vars['content'], 'getContentTitle', array())) . '</a>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('title', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . '

';
	return $__finalCompiled;
}
);