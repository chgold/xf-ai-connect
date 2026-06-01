<?php
// FROM HASH: f309387bf0a4c4a4e8efa98e71d6ecba
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['group'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add option group');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit option group' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['group']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['group'], 'isUpdate', array())) {
		$__finalCompiled .= '
	';
		$__templater->breadcrumb($__templater->preEscaped($__templater->escape($__vars['group']['title'])), $__templater->func('link', array('options/groups', $__vars['group'], ), false), array(
		));
		$__finalCompiled .= '

	';
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
		' . $__templater->button('', array(
			'href' => $__templater->func('link', array('options/groups/delete', $__vars['group'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
	');
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">

			' . $__templater->formTextBoxRow(array(
		'name' => 'group_id',
		'value' => $__vars['group']['group_id'],
		'dir' => 'ltr',
	), array(
		'label' => 'Group ID',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['group'],
	), array(
		'label' => 'Title',
	)) . '

			' . $__templater->formTextAreaRow(array(
		'name' => 'description',
		'value' => $__vars['group'],
		'autosize' => 'true',
	), array(
		'label' => 'Description',
		'hint' => 'You may use HTML',
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'icon',
		'value' => $__vars['group']['icon'],
	), array(
		'label' => 'Icon',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->callMacro(null, 'display_order_macros::row', array(
		'value' => $__vars['group'],
	), $__vars) . '

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'advanced',
		'selected' => $__vars['group']['advanced'],
		'label' => 'Show in advanced mode only',
		'_type' => 'option',
	),
	array(
		'name' => 'debug_only',
		'selected' => $__vars['group']['debug_only'],
		'label' => 'Display this group in debug mode only',
		'_type' => 'option',
	)), array(
	)) . '

			' . $__templater->callMacro(null, 'addon_macros::addon_edit', array(
		'addOnId' => $__vars['group']['addon_id'],
	), $__vars) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('options/groups/save', $__vars['group'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);