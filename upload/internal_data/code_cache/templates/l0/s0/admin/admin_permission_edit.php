<?php
// FROM HASH: 63872c84a55fffba12341342482d93e6
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['permission'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add admin permission');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit admin permission' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['permission']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['permission'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('admin-permissions/delete', $__vars['permission'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
');
	}
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">

			' . $__templater->formTextBoxRow(array(
		'name' => 'admin_permission_id',
		'value' => $__vars['permission'],
		'dir' => 'ltr',
	), array(
		'label' => 'Permission ID',
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['permission'],
	), array(
		'label' => 'Title',
	)) . '

			' . $__templater->callMacro(null, 'display_order_macros::row', array(
		'value' => $__vars['permission'],
	), $__vars) . '

			' . $__templater->callMacro(null, 'addon_macros::addon_edit', array(
		'addOnId' => $__vars['permission']['addon_id'],
	), $__vars) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('admin-permissions/save', $__vars['permission'], ), false),
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);