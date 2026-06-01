<?php
// FROM HASH: c7965a7172c14a3e43f79d1fb8396fb1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__vars['userGroup']) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped($__templater->escape($__vars['userGroup']['title']));
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped($__templater->escape($__vars['user']['username']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	$__templater->breadcrumb($__templater->preEscaped($__templater->escape($__vars['record']['title'])), $__templater->func('link', array($__vars['routePrefix'], $__vars['record'], ), false), array(
	));
	$__finalCompiled .= '

';
	if ($__vars['userGroup']['user_group_id'] === 1) {
		$__finalCompiled .= '
	<div class="blockMessage blockMessage--iconic blockMessage--highlight">
		' . 'Note that these permissions apply to both guests and unconfirmed members, and that guests may be prevented from taking certain actions regardless of the permissions set here.' . '
	</div>
';
	}
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'permission_node_macros::edit', array(
		'node' => $__vars['record'],
		'permissionData' => $__vars['permissionData'],
		'typeEntries' => $__vars['typeEntries'],
		'routeBase' => $__vars['routePrefix'],
		'saveParams' => $__vars['saveParams'],
	), $__vars);
	return $__finalCompiled;
}
);