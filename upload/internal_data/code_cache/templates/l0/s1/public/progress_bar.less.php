<?php
// FROM HASH: 990b1def2f4b3169f59f7ba6be67f4c0
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.progressBar
{
	position: relative;
	width: 100%;
	background: linear-gradient(180deg, @xf-blockFooterBg, mix(@xf-blockFooterBg, @xf-contentBg, 50%));
	border: @xf-borderSize solid @xf-borderColorFaint;
	border-radius: @xf-borderRadiusSmall;
	overflow: hidden;
}

.progressBar-progress
{
	background: linear-gradient(180deg, @xf-progressBarColor, xf-diminish(@xf-progressBarColor, 10%));
	width: 0;
	height: 20px;
	font-size: 80%;
	padding: 2px 0;
}

.progressBar-label
{
	position: absolute;
	left: 0;
	right: 0;
	color: @xf-textColor;
	text-align: center;
	overflow: hidden;
}';
	return $__finalCompiled;
}
);