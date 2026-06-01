<?php
// FROM HASH: 8ce905404aac1bb8a29a1e3121b8f8c9
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Leave direct message');
	$__finalCompiled .= '

';
	$__templater->breadcrumb($__templater->preEscaped('Direct messages'), $__templater->func('link', array('direct-messages', ), false), array(
	));
	$__finalCompiled .= '
';
	$__templater->breadcrumb($__templater->preEscaped($__templater->escape($__vars['conversation']['title'])), $__templater->func('link', array('direct-messages', $__vars['conversation'], ), false), array(
	));
	$__finalCompiled .= '

' . $__templater->form('
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
		'submit' => 'Leave',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('direct-messages/leave', $__vars['conversation'], ), false),
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);