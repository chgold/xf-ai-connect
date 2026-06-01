<?php
// FROM HASH: eae56606f224044ede48e4e6b3835d15
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formTextBoxRow(array(
		'name' => 'options[display_title]',
		'value' => $__vars['options']['display_title'],
	), array(
		'label' => 'Display title',
		'hint' => 'Required',
		'explain' => 'Enter a name for this connected account provider to be shown to users when associating their accounts. Typically, this would be the board title of your other XenForo instance.',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[board_url]',
		'value' => $__vars['options']['board_url'],
	), array(
		'label' => 'Board URL',
		'hint' => 'Required',
		'explain' => 'The primary URL for your other XenForo instance. Do not include a trailing "/" or "index.php". The URL should look similar to this: https://www.example.com/forum',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[client_id]',
		'value' => $__vars['options']['client_id'],
	), array(
		'label' => 'Client ID',
		'hint' => 'Required',
		'explain' => 'To allow users to sign in with their login credentials for another XenForo instance, you must first create an OAuth2 client on that instance and enter the client ID here.',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[client_secret]',
		'value' => $__vars['options']['client_secret'],
	), array(
		'label' => 'Client secret',
		'hint' => 'Required',
		'explain' => 'The client secret for the OAuth2 client that you created for this connected provider on the other XenForo instance.',
	));
	return $__finalCompiled;
}
);