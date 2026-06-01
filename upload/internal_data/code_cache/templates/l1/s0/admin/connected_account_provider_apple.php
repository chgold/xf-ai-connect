<?php
// FROM HASH: 8ce08b2aabb08810475eb97b917097d1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formInfoRow('To ensure emails are sent to users who use Sign in with Apple but choose to mask their real email addresses, refer to the manual.', array(
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[team_id]',
		'value' => $__vars['options']['team_id'],
	), array(
		'label' => 'Team ID',
		'hint' => 'Required',
		'explain' => 'The 10 character ID that is associated with your Apple Developer membership.',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[services_id]',
		'value' => $__vars['options']['services_id'],
	), array(
		'label' => 'Services ID',
		'hint' => 'Required',
		'explain' => 'The Services ID should have the Sign in with Apple service enabled and be associated to the same App ID as your Key ID below.',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[key_id]',
		'value' => $__vars['options']['key_id'],
	), array(
		'label' => 'Key ID',
		'hint' => 'Required',
		'explain' => 'The Key ID should have the Sign in with Apple service enabled and be associated to the key file below.',
	)) . '

';
	if ($__vars['options']['key_file']) {
		$__finalCompiled .= '
	' . $__templater->formRow('A key file already exists for this provider. Use the upload button below if you wish to replace it.', array(
			'label' => 'Key file',
		)) . '
';
	}
	$__finalCompiled .= '

' . $__templater->formUploadRow(array(
		'name' => 'options[key]',
		'accept' => '.p8,.txt',
	), array(
		'hint' => ($__vars['options']['key_file'] ? '' : 'Required'),
		'label' => ($__vars['options']['key_file'] ? 'Replace key file' : 'Key file'),
		'explain' => 'This should be the key file associated to the Key ID above.',
	));
	return $__finalCompiled;
}
);