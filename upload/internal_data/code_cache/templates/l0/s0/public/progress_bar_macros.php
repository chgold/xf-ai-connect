<?php
// FROM HASH: 80a82955232c776d2ce534858937fa8d
return array(
'macros' => array('progress_bar' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'percentage' => '!',
		'tooltip' => '',
		'label' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__templater->includeCss('progress_bar.less');
	$__finalCompiled .= '

	<div class="progressBar">
		<div class="progressBar-progress" style="width: ' . $__templater->escape($__vars['percentage']) . '%">
			<span class="progressBar-label"
				data-xf-init="' . ($__vars['tooltip'] ? 'tooltip' : '') . '" data-trigger="' . ($__vars['tooltip'] ? 'hover focus click' : '') . '"
				title="' . ($__templater->escape($__vars['tooltip']) ?: '') . '">

				';
	if ($__vars['label']) {
		$__finalCompiled .= $__templater->escape($__vars['label']);
	}
	$__finalCompiled .= '
				';
	if ($__vars['label'] AND $__vars['tooltip']) {
		$__finalCompiled .= $__templater->fontAwesome('far fa-question-circle', array(
		));
	}
	$__finalCompiled .= '
			</span>
		</div>
	</div>
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