<?php
// FROM HASH: f7c7f2d38784346b445c74c59118ff44
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Leave direct messages');
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__templater->isTraversable($__vars['conversations'])) {
		foreach ($__vars['conversations'] AS $__vars['conversation']) {
			$__compilerTemp1 .= '
		' . $__templater->formHiddenVal('ids[]', $__vars['conversation']['conversation_id'], array(
			)) . '
	';
		}
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'Leaving a direct message will remove it from your direct message list.' . '
			', array(
	)) . '

			' . $__templater->formRadioRow(array(
		'name' => 'recipient_state',
	), array(array(
		'value' => 'deleted',
		'checked' => 'checked',
		'label' => 'Accept future messages',
		'hint' => 'Should this direct message receive further responses in the future, this direct message will be restored to your inbox.',
		'_type' => 'option',
	),
	array(
		'value' => 'deleted_ignored',
		'label' => 'Ignore future messages',
		'hint' => 'You will not be notified of any future responses and the direct message will remain deleted.',
		'_type' => 'option',
	)), array(
		'label' => 'Future message handling',
	)) . '
		</div>
		' . $__templater->formSubmitRow(array(
		'icon' => 'save',
	), array(
	)) . '
	</div>

	' . $__compilerTemp1 . '

	' . $__templater->formHiddenVal('type', 'conversation', array(
	)) . '
	' . $__templater->formHiddenVal('action', 'leave', array(
	)) . '
	' . $__templater->formHiddenVal('confirmed', '1', array(
	)) . '

	' . $__templater->func('redirect_input', array($__vars['redirect'], null, true)) . '
', array(
		'action' => $__templater->func('link', array('inline-mod', ), false),
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);