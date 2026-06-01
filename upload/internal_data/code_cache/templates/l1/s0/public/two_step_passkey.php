<?php
// FROM HASH: 7bcf4ce378fbc3d211b02b0ce333c4cf
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeJs(array(
		'src' => 'xf/webauthn.js',
		'min' => '1',
	));
	$__finalCompiled .= '

';
	if ($__vars['context'] == 'login') {
		$__finalCompiled .= '
	' . $__templater->formRow('

		' . $__templater->button('Use passkey or security key', array(
			'class' => 'button--link js-webauthnStart',
		), '', array(
		)) . '
	', array(
			'data-xf-init' => 'webauthn',
			'data-type' => 'get',
			'data-existing-credentials' => $__templater->filter($__vars['existingCredentials'], array(array('json', array()),), false),
			'data-autotrigger' => 'true',
			'data-autosubmit' => 'true',
			'data-verifying' => 'Verifying' . $__vars['xf']['language']['ellipsis'],
		)) . '

	' . $__templater->formHiddenVal('webauthn_payload', '', array(
		)) . '
	' . $__templater->formHiddenVal('webauthn_challenge', ($__vars['passkey'] ? $__templater->method($__vars['passkey'], 'getChallenge', array()) : ''), array(
		)) . '
';
	}
	return $__finalCompiled;
}
);