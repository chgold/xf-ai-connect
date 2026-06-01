<?php
// FROM HASH: a88ece6538e5d74ed7dd8137289cf474
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['node'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add link forum');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit link forum' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['node']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['node'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('link-forums/delete', $__vars['node'], ), false),
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
			' . $__templater->callMacro(null, 'node_edit_macros::title', array(
		'node' => $__vars['node'],
	), $__vars) . '
			' . $__templater->callMacro(null, 'node_edit_macros::description', array(
		'node' => $__vars['node'],
	), $__vars) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'link_url',
		'value' => $__vars['link'],
	), array(
		'label' => 'Link URL',
		'explain' => 'Users will be redirected to this URL when they click on this link forum.',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->callMacro(null, 'node_edit_macros::node_name', array(
		'node' => $__vars['node'],
	), $__vars) . '
			' . $__templater->callMacro(null, 'node_edit_macros::position', array(
		'node' => $__vars['node'],
		'nodeTree' => $__vars['nodeTree'],
	), $__vars) . '
		</div>
		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('link-forums/save', $__vars['node'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);