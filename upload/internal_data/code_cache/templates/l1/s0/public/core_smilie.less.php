<?php
// FROM HASH: 6a530ca90e74841e97ee5670ffe5c14c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.smilie
{
	vertical-align: text-bottom;
	max-width: none;

	img&.smilie--emoji
	{
		width: 1.467em;
	}

	span&.smilie--emoji
	{
		display: inline-block;
		line-height: 1.2;
		font-size: 1.2em;
		vertical-align: 0;
	}

	&.is-clicked
	{
		transform: rotate(45deg);
		transition: all 0.25s;
	}
}

.contentRow-figure--emoji
{
	font-size: (@xf-fontSizeNormal * 1.2);

	img
	{
		max-width: 24px;
		max-height: 24px;
		vertical-align: top;
	}

	img.smilie--emoji
	{
		width: 22px;
	}

	span.smilie--emoji
	{
		// the default font-size above accounts for this
		font-size: 1em;
	}
}

';
	if ($__vars['smilieSprites']) {
		$__finalCompiled .= '
	';
		if ($__templater->isTraversable($__vars['smilieSprites'])) {
			foreach ($__vars['smilieSprites'] AS $__vars['smilieId'] => $__vars['smilieSprite']) {
				$__finalCompiled .= '
		';
				if ($__vars['smilieSprite']['sprite_css']) {
					$__finalCompiled .= '
			.smilie--sprite.smilie--sprite' . $__templater->escape($__vars['smilieId']) . '
			{
				' . $__templater->filter($__vars['smilieSprite']['sprite_css'], array(array('raw', array()),), true) . '
			}
		';
				}
				$__finalCompiled .= '
	';
			}
		}
		$__finalCompiled .= '
';
	}
	return $__finalCompiled;
}
);