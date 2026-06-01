<?php
// FROM HASH: 73347e8f037902327d71b4567edc0105
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('IP information for online guest');
	$__finalCompiled .= '

';
	$__templater->breadcrumb($__templater->preEscaped('Current visitors'), $__templater->func('link', array('online', ), false), array(
	));
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'helper_ip::ip_block', array(
		'ip' => $__vars['ip'],
	), $__vars);
	return $__finalCompiled;
}
);