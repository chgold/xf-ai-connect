<?php
// FROM HASH: 8e3369cac13e1ff427d1d97167b9884a
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped($__templater->escape($__vars['userGroup']['title']));
	$__finalCompiled .= '

';
	if ($__vars['userGroup']['user_group_id'] === 1) {
		$__finalCompiled .= '
	<div class="blockMessage blockMessage--highlight">
		' . 'Note that these permissions apply to both guests and unconfirmed members, and that guests may be prevented from taking certain actions regardless of the permissions set here.' . '
	</div>
';
	}
	$__finalCompiled .= '

';
	$__templater->includeJs(array(
		'src' => 'chgold/aiconnect/admin-permissions.js',
		'min' => '0',
	));
	$__finalCompiled .= $__templater->form('
	' . $__templater->callMacro(null, 'permission_macros::edit_outer', array(
		'type' => 'global',
	), $__vars) . '

	<div class="block-container">
		' . $__templater->callMacro(null, 'permission_macros::edit_groups', array(
		'interfaceGroups' => $__vars['permissionData']['interfaceGroups'],
		'permissionsGrouped' => $__vars['permissionData']['permissionsGrouped'],
		'values' => $__vars['permissionData']['values'],
	), $__vars) . '

		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
' . '' . '
 ', array(
		'action' => $__templater->func('link', array('permissions/user-groups/save', $__vars['userGroup'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);