<?php
// FROM HASH: cf9b63237670cd986637f2c7b44e45c9
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add passkey');
	$__finalCompiled .= '

';
	$__templater->includeJs(array(
		'src' => 'xf/webauthn.js',
		'min' => '1',
	));
	$__finalCompiled .= '

' . $__templater->form('

	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'If you have not been prompted automatically to create a passkey for this device, start by clicking the "Add" button below. After creating the key, give it a name and click "Save".' . '
			', array(
	)) . '
			' . $__templater->formRow('
				' . $__templater->button('Add', array(
		'class' => 'button--link js-webauthnStart',
	), '', array(
	)) . '
			', array(
		'label' => 'Register this passkey',
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'passkey_name',
	), array(
		'label' => 'Name this passkey',
		'hint' => 'Optional',
	)) . '
		</div>
		
		' . $__templater->formHiddenVal('webauthn_payload', '', array(
	)) . '
		' . $__templater->formHiddenVal('webauthn_challenge', ($__vars['newPasskey'] ? $__templater->method($__vars['newPasskey'], 'getChallenge', array()) : ''), array(
	)) . '

		' . $__templater->formSubmitRow(array(
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('account/passkey/add', ), false),
		'ajax' => 'true',
		'class' => 'block',
		'data-xf-init' => 'webauthn',
		'data-existing-credentials' => $__templater->filter($__vars['existingCredentials'], array(array('json', array()),), false),
		'data-rp-name' => $__vars['xf']['options']['boardTitle'],
		'data-user-id' => $__vars['xf']['visitor']['secret_key'],
		'data-user-name' => $__vars['xf']['visitor']['email'],
		'data-user-display-name' => $__vars['xf']['visitor']['username'],
		'data-verifying' => 'Verifying' . $__vars['xf']['language']['ellipsis'],
	));
	return $__finalCompiled;
}
);