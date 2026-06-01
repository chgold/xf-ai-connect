<?php
// FROM HASH: eb5136dbc7b1fa6aa96fc95f80961078
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('IP address information for ' . $__templater->escape($__vars['user']['username']) . '');
	$__finalCompiled .= '

';
	$__templater->breadcrumb($__templater->preEscaped('Current visitors'), $__templater->func('link', array('online', ), false), array(
	));
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'helper_ip::ip_block', array(
		'ip' => $__vars['activity']['ip'],
		'user' => $__vars['user'],
	), $__vars);
	return $__finalCompiled;
}
);