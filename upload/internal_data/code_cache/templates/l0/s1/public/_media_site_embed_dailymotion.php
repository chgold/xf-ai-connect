<?php
// FROM HASH: 5c40bc11e1df9a099afe9472dffa5c8b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="bbMediaWrapper" data-media-site-id="' . $__templater->escape($__vars['siteId']) . '" data-media-key="' . $__templater->escape($__vars['id']) . '">
	<div class="bbMediaWrapper-inner">
		<iframe src="https://www.dailymotion.com/embed/video/' . $__templater->escape($__vars['id']) . '?start=' . $__templater->escape($__vars['start']) . '&width=560&hideInfos=1"
				loading="lazy"
				width="560" height="315"
				allowfullscreen
				frameborder="0"></iframe>
	</div>
</div>';
	return $__finalCompiled;
}
);