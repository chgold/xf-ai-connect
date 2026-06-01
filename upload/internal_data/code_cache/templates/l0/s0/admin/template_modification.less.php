<?php
// FROM HASH: 9a140b6b20cb9f60e49ea392b2301087
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.templateModContents
{
	.xf-input();
	max-height: 250px;
	min-height: 15px;
	margin: 0;
	overflow: auto;
	font-family: @xf-fontFamilyCode;
	direction: ltr;
}

.templateModApply
{
	&.is-active
	{
		font-weight: @xf-fontWeightHeavy;
		font-size: 120%;
	}

	&.templateModApply--ok { color: green; }
	&.templateModApply--notFound { color: grey;}
	&.templateModApply--error { color: red; }
}';
	return $__finalCompiled;
}
);