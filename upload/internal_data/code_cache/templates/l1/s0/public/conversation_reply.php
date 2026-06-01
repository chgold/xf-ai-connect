<?php
// FROM HASH: e48e1dfb3fbc80cea46ccd5aca42d410
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Reply to direct message');
	$__finalCompiled .= '

';
	$__templater->breadcrumb($__templater->preEscaped('Direct messages'), $__templater->func('link', array('direct-messages', ), false), array(
	));
	$__finalCompiled .= '
';
	$__templater->breadcrumb($__templater->preEscaped($__templater->escape($__vars['conversation']['title'])), $__templater->func('link', array('direct-messages', $__vars['conversation'], ), false), array(
	));
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__vars['attachmentData']) {
		$__compilerTemp1 .= '
					' . $__templater->callMacro(null, 'helper_attach_upload::upload_block', array(
			'attachmentData' => $__vars['attachmentData'],
			'forceHash' => $__vars['conversation']['draft_reply']['attachment_hash'],
		), $__vars) . '
				';
	}
	$__compilerTemp2 = '';
	if ($__vars['xf']['options']['multiQuote']) {
		$__compilerTemp2 .= '
					' . $__templater->callMacro(null, 'multi_quote_macros::button', array(
			'href' => $__templater->func('link', array('direct-messages/multi-quote', $__vars['conversation'], ), false),
			'messageSelector' => '.js-message',
			'storageKey' => 'multiQuoteConversation',
		), $__vars) . '
				';
	}
	$__finalCompiled .= $__templater->form('

	<div class="block-container">
		<div class="block-body">
			' . $__templater->formEditorRow(array(
		'name' => 'message',
		'value' => $__vars['defaultMessage'],
		'attachments' => ($__vars['attachmentData'] ? $__vars['attachmentData']['attachments'] : array()),
		'placeholder' => 'Write your reply...',
		'data-preview-url' => $__templater->func('link', array('direct-messages/reply-preview', $__vars['conversation'], ), false),
	), array(
		'rowtype' => 'fullWidth noLabel',
		'label' => 'Message',
	)) . '

			' . $__templater->formRow('
				' . $__compilerTemp1 . '

				' . $__compilerTemp2 . '
			', array(
		'rowtype' => 'fullWidth noLabel',
	)) . '

		</div>
		' . $__templater->formSubmitRow(array(
		'submit' => 'Post reply',
		'icon' => 'reply',
		'sticky' => 'true',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('direct-messages/add-reply', $__vars['conversation'], ), false),
		'class' => 'block',
		'ajax' => 'true',
		'draft' => $__templater->func('link', array('direct-messages/draft', $__vars['conversation'], ), false),
		'data-xf-init' => 'attachment-manager',
	));
	return $__finalCompiled;
}
);