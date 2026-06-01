<?php
// FROM HASH: 02cf417af840cfb6176d4aecc96cc16d
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '.shareButtons
{
	.m-clearFix();
}

.shareButtons-buttons
{
	.shareButtons--iconic &
	{
		display: grid;
		grid-template-columns: repeat(auto-fill, minmax(35px, 1fr));
	}
}

.shareButtons-label
{
	float: left;
	margin-right: 3px;
	color: @xf-textColorMuted;
	min-height: 35px;
	line-height: 35px;
}

.shareButtons-button
{
	float: left;
	margin-right: 3px;
	padding: 6px;
	color: @xf-textColorMuted;
	font-size: 20px;
	line-height: 20px;
	white-space: nowrap;
	min-width: 35px;
	border-radius: @xf-borderRadiusSmall;
	background-color: transparent;
	.m-transition();

	&:last-of-type
	{
		margin-right: 0;
	}

	&:hover
	{
		text-decoration: none;
		color: white;
	}

	> i
	{
		display: inline-block;
		vertical-align: middle;

		.m-faBase(\'Pro\');
	}

	&.shareButtons-button--brand
	{
		> i
		{
			.m-faBase(\'Brands\');
		}
	}

	> span
	{
		font-weight: @xf-fontWeightNormal;
		font-size: @xf-fontSizeNormal;
	}

	.shareButtons--iconic &
	{
		text-align: center;

		> i
		{
			min-width: 20px;
		}

		> svg
		{
			vertical-align: middle;
		}

		> span
		{
			.m-visuallyHidden();
		}
	}

	&.shareButtons-button--facebook
	{
		&:hover { background-color: #3B5998; }
	}

	&.shareButtons-button--twitter
	{
		&:hover { background-color: #000000; }
	}

	&.shareButtons-button--pinterest
	{
		&:hover { background-color: #bd081c; }
	}

	&.shareButtons-button--tumblr
	{
		&:hover { background-color: #35465c; }
	}

	&.shareButtons-button--reddit
	{
		&:hover { background-color: #FF4500; }
	}

	&.shareButtons-button--whatsApp
	{
		&:hover { background-color: #25D366; }
	}

	&.shareButtons-button--linkedin
	{
		&:hover { background-color: #0077B5; }
	}

	&.shareButtons-button--email
	{
		&:hover { background-color: #1289ff; }
	}

	&.shareButtons-button--share
	{
		cursor: pointer;
		&:hover { background-color: #787878; }
	}

	&.shareButtons-button--link
	{
		cursor: pointer;
		&:hover { background-color: #787878; }
	}

	&.is-hidden
	{
		display: none;
	}
}

.shareInput
{
	margin-bottom: 5px;

	&:last-child
	{
		margin-bottom: 0;
	}
}

.shareInput-label
{
	font-size: @xf-fontSizeSmall;
	.m-appendColon();
}

.shareInput-button
{
	color: @xf-linkColor;
	cursor: pointer;

	> i
	{
		display: inline-block;
		vertical-align: middle;
		.m-faBase();
	}

	&.is-hidden
	{
		display: none;
	}
}

.shareInput-input
{
	font-size: @xf-fontSizeSmall;

	.m-inputZoomFix();

	.shareInput-button.is-hidden + &
	{
		border-radius: @xf-borderRadiusMedium;
	}
}';
	return $__finalCompiled;
}
);