<?php
// FROM HASH: 231b6e8b09196af57c25f0aac2350bf2
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if (!$__vars['providerData']) {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::explain', array(
			'providerTitle' => $__vars['provider']['title'],
			'keyName' => 'Key ID',
			'keyValue' => $__vars['provider']['options']['key_id'],
		), $__vars) . '
';
	} else {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::success', array(), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::email', array(
			'email' => $__vars['providerData']['email'],
		), $__vars) . '
';
	}
	return $__finalCompiled;
}
);