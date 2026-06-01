<?php
// FROM HASH: 3adbf8057aa4d68b1b17f301e2ed98ba
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.menuTrigger.menuTrigger--prefix
{
	text-decoration: none;
}

.menuPrefix,
.menuPrefix.label--hidden
{
	display: block;
	font-size: @xf-fontSizeSmall;
	cursor: default;
	padding: @xf-paddingMedium;
	//margin-bottom: (@xf-paddingMedium * -1);

	&.label--hidden
	{
		border: 1px solid @xf-borderColorFaint;
	}

	&.menuPrefix--none
	{
		color: @xf-textColorMuted;
		text-decoration: none;
	}
}';
	return $__finalCompiled;
}
);