<?php
// FROM HASH: d4cdfb77e9a4fa6e1d265510ad2a2e49
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__vars['lightbox']) {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'lightbox_macros::single_image', array(
			'canViewAttachments' => true,
			'src' => $__vars['imageUrl'],
			'dataUrl' => $__vars['validUrl'],
			'alt' => ($__vars['alt'] ?: $__vars['altFallback']),
			'title' => $__vars['alt'],
			'styleAttr' => $__vars['styleAttr'],
			'alignClass' => $__vars['alignClass'],
			'width' => $__vars['width'],
			'height' => $__vars['height'],
		), $__vars) . '
';
	} else {
		$__finalCompiled .= '
	<img src="' . $__templater->escape($__vars['imageUrl']) . '" data-url="' . $__templater->escape($__vars['validUrl']) . '" class="bbImage ' . $__templater->escape($__vars['alignClass']) . '" loading="lazy"
		' . ($__vars['alt'] ? ((((('alt="' . $__templater->filter($__vars['alt'], array(array('for_attr', array()),), true)) . '" title="') . $__templater->filter($__vars['alt'], array(array('for_attr', array()),), true)) . '"')) : '') . ' style="' . $__templater->escape($__vars['styleAttr']) . '" width="' . $__templater->escape($__vars['width']) . '" height="' . $__templater->escape($__vars['height']) . '" />
';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
);