<?php
// FROM HASH: 0423b209aa358d017a9960aa2f565197
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['prompt'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add prompt');
		$__finalCompiled .= '
	';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit prompt' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['prompt']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['prompt'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array($__vars['linkPrefix'] . '/delete', $__vars['prompt'], ), false),
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
		'name' => 'title',
		'value' => $__vars['prompt'],
	), array(
		'label' => 'Title',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->callMacro(null, 'base_prompt_edit_macros::prompt_groups', array(
		'prompt' => $__vars['prompt'],
		'promptGroups' => $__vars['promptGroups'],
	), $__vars) . '

			' . $__templater->callMacro(null, 'display_order_macros::row', array(
		'value' => $__vars['prompt'],
	), $__vars) . '

			<hr class="formRowSep" />

			' . $__templater->filter($__vars['extraOptions'], array(array('raw', array()),), true) . '
		</div>
		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array($__vars['linkPrefix'] . '/save', $__vars['prompt'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);