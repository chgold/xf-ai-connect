<?php
// FROM HASH: fce085cc58a43819f92e0a52ebe42ac9
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if (!$__vars['providerData']) {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::explain', array(
			'providerTitle' => $__vars['provider']['title'],
			'keyName' => 'App ID',
			'keyValue' => $__vars['provider']['options']['app_id'],
		), $__vars) . '
';
	} else {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::success', array(), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::display_name', array(
			'name' => $__vars['providerData']['username'],
		), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::email', array(
			'email' => $__vars['providerData']['email'],
		), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::picture', array(
			'url' => $__vars['providerData']['avatar_url'],
		), $__vars) . '
';
	}
	return $__finalCompiled;
}
);