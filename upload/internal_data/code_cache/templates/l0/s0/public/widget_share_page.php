<?php
// FROM HASH: 3ce4b3d4c11c939ab94b15c266e9ca9e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
					' . $__templater->callMacro(null, 'share_page_macros::buttons', array(
		'iconic' => $__vars['options']['iconic'],
	), $__vars) . '
				';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
	<div class="block"' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
		<div class="block-container">
			<h3 class="block-minorHeader">' . $__templater->escape($__vars['title']) . '</h3>
			<div class="block-body block-row">
				' . $__compilerTemp1 . '
			</div>
		</div>
	</div>
';
	}
	return $__finalCompiled;
}
);