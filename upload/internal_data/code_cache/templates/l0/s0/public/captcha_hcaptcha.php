<?php
// FROM HASH: 94b2ac124d4185526aa2d60bb07a65fd
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeJs(array(
		'src' => 'xf/captcha.js',
		'min' => '1',
	));
	$__finalCompiled .= '

<div data-xf-init="h-captcha" data-sitekey="' . $__templater->escape($__vars['siteKey']) . '" data-invisible="' . $__templater->escape($__vars['invisible']) . '"></div>';
	return $__finalCompiled;
}
);