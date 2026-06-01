<?php
// FROM HASH: 8b3386a8572b523733d445ecc1b4744c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped($__templater->escape($__vars['record']['title']));
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'permission_node_macros::list', array(
		'node' => $__vars['record'],
		'isPrivate' => $__vars['isPrivate'],
		'userGroups' => $__vars['userGroups'],
		'users' => $__vars['users'],
		'entries' => $__vars['entries'],
		'routeBase' => $__vars['routePrefix'],
	), $__vars);
	return $__finalCompiled;
}
);