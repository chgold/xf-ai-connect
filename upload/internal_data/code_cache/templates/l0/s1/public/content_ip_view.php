<?php
// FROM HASH: 01988abf0af79cb3ebad0a3c770f331a
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('IP address information for content by ' . ($__templater->escape($__vars['ip']['User']['username']) ?: 'Guest') . '');
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'helper_ip::ip_block', array(
		'ip' => $__vars['ip']['ip'],
		'user' => $__vars['ip']['User'],
	), $__vars);
	return $__finalCompiled;
}
);