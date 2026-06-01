<?php
// FROM HASH: 0a56eb5818992a836d357699205037fc
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Message users');
	$__finalCompiled .= '
';
	$__templater->pageParams['pageDescription'] = $__templater->preEscaped('You can use this form to send a direct message to the users which match the criteria specified below.');
	$__templater->pageParams['pageDescriptionMeta'] = true;
	$__finalCompiled .= '

';
	if ($__vars['sent']) {
		$__finalCompiled .= '
	<div class="blockMessage blockMessage--success blockMessage--iconic">
		' . 'Your direct message was sent to ' . $__templater->filter($__vars['sent'], array(array('number', array()),), true) . ' users.' . '
	</div>
';
	}
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formTextBoxRow(array(
		'name' => 'from_user',
		'value' => $__vars['xf']['visitor']['username'],
		'ac' => 'single',
	), array(
		'label' => 'From user',
		'explain' => '
					<p>' . 'Enter the name of an existing user the direct message should be started by.' . '</p>
					<p><b>' . 'Note' . $__vars['xf']['language']['label_separator'] . '</b> ' . 'You cannot send a direct message to yourself.' . '</p>
				',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formTextBoxRow(array(
		'name' => 'message_title',
		'maxlength' => '100',
		'required' => 'true',
	), array(
		'label' => 'Direct message title',
	)) . '

			' . $__templater->formTextAreaRow(array(
		'name' => 'message_body',
		'rows' => '5',
		'autosize' => 'true',
		'required' => 'true',
	), array(
		'label' => 'Direct message body',
		'hint' => 'You may use BB code',
		'explain' => 'The following placeholders will be replaced in the direct message body: {name}, {email}, {id}.' . ' ' . 'You may also use {phrase:phrase_title} which will be replaced with the phrase text in the recipient\'s language.',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'open_invite',
		'value' => '1',
		'label' => 'Allow anyone in the direct message to invite others',
		'_type' => 'option',
	),
	array(
		'name' => 'conversation_locked',
		'value' => '1',
		'label' => 'Lock direct message (no responses will be allowed)',
		'_type' => 'option',
	)), array(
		'label' => 'Direct message options',
	)) . '

			' . $__templater->formRadioRow(array(
		'name' => 'delete_type',
	), array(array(
		'selected' => true,
		'label' => 'Do not leave direct message',
		'explain' => 'The direct message will remain in your inbox and you will be notified of responses.',
		'_type' => 'option',
	),
	array(
		'value' => 'deleted',
		'label' => 'Leave direct message and accept future responses',
		'explain' => 'Should this direct message receive further responses in the future, this direct message will be restored to your inbox.',
		'_type' => 'option',
	),
	array(
		'value' => 'deleted_ignored',
		'label' => 'Leave direct message and ignore future responses',
		'explain' => 'You will not be notified of any future responses and the direct message will remain deleted.',
		'_type' => 'option',
	)), array(
		'label' => 'Future message handling',
	)) . '
		</div>

		<h2 class="block-formSectionHeader"><span class="block-formSectionHeader-aligner">' . 'User criteria' . '</span></h2>
		<div class="block-body">
			' . $__templater->includeTemplate('helper_user_search_criteria', $__vars) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'submit' => 'Proceed' . $__vars['xf']['language']['ellipsis'],
		'sticky' => 'true',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('users/message/confirm', ), false),
		'class' => 'block',
	));
	return $__finalCompiled;
}
);