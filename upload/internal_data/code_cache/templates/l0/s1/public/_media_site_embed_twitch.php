<?php
// FROM HASH: 46d7fff03c0524f46b2a03bfe5dd06d1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="bbMediaWrapper" data-media-site-id="' . $__templater->escape($__vars['siteId']) . '" data-media-key="' . $__templater->escape($__vars['id']) . '">
	<div class="bbMediaWrapper-inner">
		<iframe src="' . $__templater->escape($__vars['url']) . '"
			loading="lazy"
			width="560" height="315"
			frameborder="0" allowfullscreen="true"
			scrolling="no"></iframe>
	</div>
</div>';
	return $__finalCompiled;
}
);