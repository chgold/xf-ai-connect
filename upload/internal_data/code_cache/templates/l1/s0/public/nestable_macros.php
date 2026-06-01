<?php
// FROM HASH: 43e132288f11d18e22be494bb89a08d5
return array(
'macros' => array('setup' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'includeLess' => true,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	if ($__vars['includeLess']) {
		$__finalCompiled .= '
		';
		$__templater->includeCss('public:nestable.less');
		$__finalCompiled .= '
	';
	}
	$__finalCompiled .= '
	';
	$__templater->includeJs(array(
		'src' => 'xf/nestable.js',
		'min' => '1',
	));
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';

	return $__finalCompiled;
}
);