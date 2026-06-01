<?php
// FROM HASH: ae3573831e409ffbaef4e5fab4f4f719
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formTextBoxRow(array(
		'name' => 'options[client_id]',
		'value' => $__vars['profile']['options']['client_id'],
	), array(
		'label' => 'Client ID',
		'hint' => 'Required',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[secret_key]',
		'value' => $__vars['profile']['options']['secret_key'],
	), array(
		'label' => 'Secret key',
		'hint' => 'Required',
		'explain' => '
		' . 'Enter the client ID and secret key from the relevant application in your <a href="https://developer.paypal.com/dashboard/applications/" target="_blank">PayPal Dashboard</a>. You should also set up a webhook.' . '
	',
	)) . '

<hr class="formRowSep" />

<hr class="formRowSep" />

' . $__templater->formCheckBoxRow(array(
	), array(array(
		'label' => 'Enable webhook verification',
		'name' => 'enable_webhook',
		'selected' => $__vars['profile']['options']['webhook_id'],
		'_type' => 'option',
	)), array(
		'explain' => 'To verify incoming webhook signatures and prevent replay attacks you must enable the checkbox above.',
	)) . '

' . $__templater->formHiddenVal('options[webhook_id]', $__vars['profile']['options']['webhook_id'], array(
	));
	return $__finalCompiled;
}
);