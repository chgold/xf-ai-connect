<?php
// FROM HASH: 0801dbd2f1150747960299ac607cf16b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="bbMediaWrapper" data-media-site-id="' . $__templater->escape($__vars['siteId']) . '" data-media-key="' . $__templater->escape($__vars['id']) . '">
	<div class="bbMediaWrapper-inner bbMediaWrapper-inner--4to3">
		<iframe src="https://giphy.com/embed/' . $__templater->escape($__vars['id']) . '"
			loading="lazy"
			width="500"
			height="375"
			frameborder="0"
			allowfullscreen></iframe>
	</div>
</div>';
	return $__finalCompiled;
}
);