<?php
// FROM HASH: 0ba21bddd2de69ed2a98ad5356beb4b7
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= 'body
{
	max-width: @xf-pageWidthMax;
	padding: 75px 10vw;
	margin: 0 auto;
}

.application
{
	display: grid;
	grid-template-columns: 100px 1fr;
	grid-template-areas:
		\'logo title\'
		\'logo desc\';
	gap: @xf-paddingLarge;
	padding: @xf-paddingLargest;
	background-color: @xf-contentAltBg;
	margin: 1em 0;

	img
	{
		grid-area: logo;
	}

	h2
	{
		grid-area: title;
		margin: 0;
	}

	p
	{
		grid-area: desc;
		margin: 0;
	}
}

h5.app-info
{
	margin-bottom: .5em;
}';
	return $__finalCompiled;
}
);