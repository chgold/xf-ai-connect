<?php
// FROM HASH: f4f442c393582c8212ce46fd484b4c65
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if (!$__vars['providerData']) {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::explain', array(
			'providerTitle' => $__vars['provider']['title'],
			'keyName' => 'Consumer key',
			'keyValue' => $__vars['provider']['options']['consumer_key'],
		), $__vars) . '
';
	} else {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::success', array(), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::display_name', array(
			'name' => $__vars['providerData']['username'],
			'secondaryName' => $__templater->func('parens', array('@' . $__vars['providerData']['screen_name'], ), false),
		), $__vars) . '

	' . $__templater->callMacro(null, 'connected_account_provider_test_macros::picture', array(
			'url' => $__vars['providerData']['avatar_url'],
		), $__vars) . '
';
	}
	return $__finalCompiled;
}
);