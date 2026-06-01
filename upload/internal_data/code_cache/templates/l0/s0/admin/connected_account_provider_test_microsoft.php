<?php
// FROM HASH: cd8f0e2316ea5312dac33e3d6955c06c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if (!$__vars['providerData']) {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::explain', array(
			'providerTitle' => $__vars['provider']['title'],
			'keyName' => 'Client ID',
			'keyValue' => $__vars['provider']['options']['client_id'],
		), $__vars) . '
';
	} else {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::success', array(), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::display_name', array(
			'name' => $__vars['providerData']['username'],
		), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::picture', array(
			'url' => $__vars['providerData']['avatar_url'],
		), $__vars) . '
';
	}
	return $__finalCompiled;
}
);