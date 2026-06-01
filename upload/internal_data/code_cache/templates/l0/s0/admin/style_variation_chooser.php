<?php
// FROM HASH: dd72f09b3879b1d4315c927c95481060
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Style variation');
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		<div class="block-body">
			' . $__templater->callMacro(null, 'public:style_variation_macros::variation_menu', array(
		'style' => $__vars['style'],
		'routeType' => 'admin',
		'route' => 'account/style-variation',
		'selectedVariation' => $__vars['xf']['visitor']['Admin']['admin_style_variation'],
	), $__vars) . '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);