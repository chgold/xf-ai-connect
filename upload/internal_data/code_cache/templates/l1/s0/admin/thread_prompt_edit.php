<?php
// FROM HASH: 2db078be7d507f38fa1e883a4cb4a034
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = $__vars;
	$__compilerTemp1['extraOptions'] = $__templater->preEscaped('
		' . $__templater->callMacro(null, 'forum_selection_macros::select_forums', array(
		'nodeIds' => $__vars['nodeIds'],
		'nodeTree' => $__vars['nodeTree'],
	), $__vars) . '
	');
	$__finalCompiled .= $__templater->includeTemplate('base_prompt_edit', $__compilerTemp1);
	return $__finalCompiled;
}
);