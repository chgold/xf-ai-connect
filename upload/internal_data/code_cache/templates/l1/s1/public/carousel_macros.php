<?php
// FROM HASH: 9aabefe250b303059fc60794aee1eed4
return array(
'macros' => array('setup' => array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__templater->includeCss('carousel.less');
	$__finalCompiled .= '

	';
	$__templater->includeJs(array(
		'prod' => 'xf/carousel-compiled.js',
		'dev' => 'vendor/fancyapps/carousel/carousel.umd.js, vendor/fancyapps/carousel/carousel.autoplay.umd.js',
	));
	$__finalCompiled .= '
	';
	$__templater->includeJs(array(
		'src' => 'xf/carousel.js',
		'min' => '1',
	));
	$__finalCompiled .= '

	';
	$__templater->inlineJs('
		XF.extendObject(XF.phrases, {
			next_slide: "' . $__templater->filter('Next slide', array(array('escape', array('js', )),), false) . '",
			previous_slide: "' . $__templater->filter('Previous slide', array(array('escape', array('js', )),), false) . '",
			go_to_slide_x: "' . $__templater->filter('Go to slide #%d', array(array('escape', array('js', )),), false) . '"
		});
	', true);
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