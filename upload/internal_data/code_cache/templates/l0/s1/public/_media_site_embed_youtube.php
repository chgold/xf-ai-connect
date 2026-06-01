<?php
// FROM HASH: 13d0451f3f55043a469fd60d848705f3
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="bbMediaWrapper" data-media-site-id="' . $__templater->escape($__vars['siteId']) . '" data-media-key="' . $__templater->escape($__vars['id']) . '">
	<div class="bbMediaWrapper-inner">
		<iframe src="https://www.youtube.com/embed/' . $__templater->escape($__vars['id']) . '?wmode=opaque' . ($__vars['start'] ? ('&start=' . $__templater->escape($__vars['start'])) : '') . ($__vars['list'] ? ('&list=' . $__templater->escape($__vars['list'])) : '') . '"
				loading="lazy"
				width="560" height="315"
				frameborder="0" allowfullscreen="true"></iframe>
	</div>
</div>';
	return $__finalCompiled;
}
);