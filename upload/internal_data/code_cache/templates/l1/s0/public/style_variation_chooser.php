<?php
// FROM HASH: 676060fe3cc15a4b52df1ea3de21bd69
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Style variation');
	$__finalCompiled .= '

';
	$__templater->setPageParam('head.' . 'metaNoindex', $__templater->preEscaped('<meta name="robots" content="noindex" />'));
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		<div class="block-body">
			' . $__templater->callMacro(null, 'style_variation_macros::variation_menu', array(
		'style' => $__vars['style'],
	), $__vars) . '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);