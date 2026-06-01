<?php
// FROM HASH: e68785c6d2a93cd3a6c7da9b2c07ff17
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->callMacro(null, 'public:code_editor::editor_setup', array(
		'modeConfig' => $__vars['modeConfig'],
	), $__vars);
	return $__finalCompiled;
}
);