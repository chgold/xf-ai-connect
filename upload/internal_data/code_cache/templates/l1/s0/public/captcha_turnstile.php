<?php
// FROM HASH: 6fc06f8ee4832d1066f1a78c52c45840
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeJs(array(
		'src' => 'xf/captcha.js',
		'min' => '1',
	));
	$__finalCompiled .= '

<div data-xf-init="turnstile" data-sitekey="' . $__templater->escape($__vars['siteKey']) . '" data-action="' . $__templater->escape($__vars['context']) . '"></div>';
	return $__finalCompiled;
}
);