<?php
// FROM HASH: 5a72ef6ce3f25228e0dbebb050822914
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__vars['xf']['options']['aiconnect_nav_bottom'] AND ($__templater->method($__vars['xf']['visitor'], 'hasPermission', array('aiconnect', 'viewAiConnect', )) AND $__templater->method($__vars['xf']['visitor'], 'hasPermission', array('aiconnect', 'useTools', )))) {
		$__finalCompiled .= '
<li><a href="/ai-connect" class="p-footer-rssLink"><span aria-hidden="true">
	<img src="/js/aiconnect/icon.png" alt="" style="height:16px;width:16px;vertical-align:middle;" /></span></a></li>
';
	}
	return $__finalCompiled;
}
);