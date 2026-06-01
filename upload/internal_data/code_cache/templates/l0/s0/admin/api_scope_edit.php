<?php
// FROM HASH: 43e683eb2d9c52531906ec96a82e42fe
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['scope'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add API scope');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit API scope' . ': ' . $__templater->escape($__vars['scope']['api_scope_id']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['scope'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('api-scopes/delete', $__vars['scope'], ), false),
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
		'name' => 'api_scope_id',
		'value' => $__vars['scope'],
		'dir' => 'ltr',
	), array(
		'label' => 'Scope ID',
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'description',
		'value' => $__vars['scope'],
	), array(
		'label' => 'Description',
	)) . '

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'usable_with_oauth_clients',
		'selected' => $__vars['scope']['usable_with_oauth_clients'],
		'label' => 'Allow this scope to be used with OAuth2 clients',
		'_type' => 'option',
	)), array(
	)) . '

			' . $__templater->callMacro(null, 'addon_macros::addon_edit', array(
		'addOnId' => $__vars['scope']['addon_id'],
	), $__vars) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('api-scopes/save', $__vars['scope'], ), false),
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);