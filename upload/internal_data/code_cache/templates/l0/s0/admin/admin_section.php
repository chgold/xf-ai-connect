<?php
// FROM HASH: 8b0fdf780deb6727fab2d28a63f674ec
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped($__templater->escape($__vars['title']));
	$__finalCompiled .= '

' . $__templater->callMacro(null, 'section_nav_macros::section_nav', array(
		'section' => $__vars['entry']['navigation_id'],
	), $__vars);
	return $__finalCompiled;
}
);