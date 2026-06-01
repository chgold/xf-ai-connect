<?php
// FROM HASH: b3a73ab429ee546b952eac19fbf387ac
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="bbMediaWrapper" data-media-site-id="' . $__templater->escape($__vars['siteId']) . '" data-media-key="' . $__templater->escape($__vars['country']) . '/' . $__templater->escape($__vars['type']) . '/' . $__templater->escape($__vars['id']) . '">
	<div class="bbMediaWrapper-inner bbMediaWrapper-inner--' . $__templater->escape($__vars['height']) . 'px">
		<iframe src="' . $__templater->escape($__vars['url']) . '"
			loading="lazy"
			style="' . $__templater->escape($__vars['style']) . '"
			height="' . $__templater->escape($__vars['height']) . 'px"
			frameborder="0"
			scrolling="' . $__templater->escape($__vars['scrolling']) . '"></iframe>
	</div>
</div>';
	return $__finalCompiled;
}
);