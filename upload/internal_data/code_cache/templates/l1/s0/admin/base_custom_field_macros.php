<?php
// FROM HASH: 4569df0d4a628bc5ddfb58d435fc3232
return array(
'macros' => array('common_options' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'field' => '!',
		'supportsRequired' => true,
		'supportsUserEditable' => false,
		'supportsEditableOnce' => false,
		'supportsModeratorEditable' => false,
		'supportsGroupEditable' => false,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	$__compilerTemp1 = array();
	if ($__vars['supportsRequired']) {
		$__compilerTemp1[] = array(
			'name' => 'required',
			'selected' => $__vars['field']['required'],
			'label' => 'Field is required',
			'_type' => 'option',
		);
	}
	if ($__vars['supportsUserEditable']) {
		$__compilerTemp2 = array();
		if ($__vars['supportsEditableOnce']) {
			$__compilerTemp2[] = array(
				'name' => 'user_editable',
				'value' => 'once',
				'selected' => $__vars['field']['user_editable'] == 'once',
				'label' => 'Editable only once',
				'_type' => 'option',
			);
		}
		$__compilerTemp1[] = array(
			'name' => 'user_editable',
			'value' => 'yes',
			'selected' => $__vars['field']['user_editable'] != 'never',
			'label' => 'User editable',
			'_dependent' => array($__templater->formCheckBox(array(
		), $__compilerTemp2)),
			'_type' => 'option',
		);
	}
	if ($__vars['supportsModeratorEditable']) {
		$__compilerTemp1[] = array(
			'name' => 'moderator_editable',
			'selected' => $__vars['field']['moderator_editable'],
			'label' => 'Moderator editable',
			'_type' => 'option',
		);
	}
	$__finalCompiled .= $__templater->formCheckBoxRow(array(
		'hideempty' => 'true',
	), $__compilerTemp1, array(
	)) . '

	';
	if ($__vars['supportsGroupEditable']) {
		$__finalCompiled .= '
		' . $__templater->callMacro(null, 'helper_user_group_edit::checkboxes', array(
			'label' => 'Editable by user groups',
			'id' => 'editable_user_group',
			'selectedUserGroups' => ($__vars['field']['field_id'] ? $__vars['field']['editable_user_group_ids'] : array(-1, )),
		), $__vars) . '
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';

	return $__finalCompiled;
}
);