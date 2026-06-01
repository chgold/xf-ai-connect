<?php
// FROM HASH: e86d151eee3c32cb99c39cb9da88eea5
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.alert
{
	&.is-unread
	{
		.xf-contentHighlightBase();
	}
}

.alertToggler
{
	text-decoration: none !important;
	padding: @xf-paddingMedium;
	margin-right: (@xf-paddingMedium * -1);

	.alert &
	{
		opacity: 0;
	}

	.alert:hover &,
	.has-touchevents &
	{
		opacity: 1;
	}
}

.alertToggler-icon
{
	& when (@xf-fontAwesomeWeight >= @faWeight-solid)
	{
		.m-faIcon(@fa-var-regular-circle, .75em);
	}
	& when (@xf-fontAwesomeWeight < @faWeight-solid)
	{
		.m-faIcon(@fa-var-circle, .75em);
	}

	.is-unread &
	{
		.m-faIcon(@fa-var-solid-circle, .75em);
	}
}';
	return $__finalCompiled;
}
);