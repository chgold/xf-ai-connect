<?php
// FROM HASH: 89df6e0ae4d274e45654cd39bc1ad822
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '// ########################## GLOBAL BASE SETUP #######################

html
{
	font: @xf-fontSizeNormal / @xf-lineHeightDefault sans-serif;
	font-family: @xf-fontFamilyUi;
	font-weight: @xf-fontWeightNormal;
	color: @xf-textColor;
	margin: 0;
	padding: 0;
	word-wrap: break-word;
	background-color: @xf-pageBg;

	/* // just a reminder that we *might* want this at some point
	-ms-text-size-adjust: none;
	-webkit-text-size-adjust: none;*/

	--js-display: block;
}

button, input, optgroup, select, textarea
{
	font-family: @xf-fontFamilyUi;
	line-height: @xf-lineHeightDefault;
}

img
{
	max-width: 100%;
	height: auto;
}

b, strong
{
	font-weight: @xf-fontWeightHeavy;
}

a
{
	.xf-link();

	&:hover
	{
		.xf-linkHover();
	}
}

svg
{
	fill: currentColor;
}

' . $__templater->includeTemplate('core_setup.less', $__vars) . '
' . $__templater->includeTemplate('core_utilities.less', $__vars) . '
' . $__templater->includeTemplate('core_list.less', $__vars) . '
' . $__templater->includeTemplate('core_categorylist.less', $__vars) . '
' . $__templater->includeTemplate('core_block.less', $__vars) . '
' . $__templater->includeTemplate('core_blockmessage.less', $__vars) . '
' . $__templater->includeTemplate('core_blockstatus.less', $__vars) . '
' . $__templater->includeTemplate('core_blocklink.less', $__vars) . '
' . $__templater->includeTemplate('core_blockend.less', $__vars) . '
' . $__templater->includeTemplate('core_fixedmessage.less', $__vars) . '
' . $__templater->includeTemplate('core_button.less', $__vars) . '
' . $__templater->includeTemplate('core_meter_bar.less', $__vars) . '

// ################################# INPUTS & FORMS #####################

' . $__templater->includeTemplate('core_input.less', $__vars) . '
' . $__templater->includeTemplate('core_formrow.less', $__vars) . '

' . $__templater->includeTemplate('core_collapse.less', $__vars) . '
' . $__templater->includeTemplate('core_badge.less', $__vars) . '
' . $__templater->includeTemplate('core_tooltip.less', $__vars) . '
' . $__templater->includeTemplate('core_menu.less', $__vars) . '
' . $__templater->includeTemplate('core_offcanvas.less', $__vars) . '
' . $__templater->includeTemplate('core_tab.less', $__vars) . '
' . $__templater->includeTemplate('core_overlay.less', $__vars) . '
' . $__templater->includeTemplate('core_globalaction.less', $__vars) . '
' . $__templater->includeTemplate('core_avatar.less', $__vars) . '
' . $__templater->includeTemplate('core_datalist.less', $__vars) . '
' . $__templater->includeTemplate('core_filter.less', $__vars) . '
' . $__templater->includeTemplate('core_contentrow.less', $__vars) . '
' . $__templater->includeTemplate('core_pagenav.less', $__vars) . '
' . $__templater->includeTemplate('core_hscroller.less', $__vars) . '

// FLASH MESSAGES
.flashMessage
{
	display: none;
	position: fixed;
	top: 0;
	left: 0;
	right: 0;
	z-index: @zIndex-9;
	.xf-flashMessage();

	.m-transitionFadeDown();
}

// AUTOCOMPLETE
.autoCompleteList
{
	.m-autoCompleteList();
	margin-top: 2px;

	&.autoCompleteList--inherit
	{
		font-size: inherit;

		li
		{
			padding: @xf-paddingLarge;
			line-height: inherit;
		}
	}

	&.autoCompleteList--fullWidth
	{
		min-width: 100%;
	}
}

// #################################### TAGS ##############################
// note that while this is related to tags, it\'s commonly used so just include it

.tagList,
.tagList > dt,
.tagList > dd
{
	display: inline;
	padding: 0;
	margin: 0;
}

.tagItem
{
	display: inline-block;
	max-width: 100%;
	padding: 0 6px 1px;
	margin: 0 0 2px;
	border-radius: @xf-borderRadiusMedium;
	font-size: @xf-fontSizeSmaller;
	.xf-chip();

	a&:hover
	{
		text-decoration: none;
		color: @xf-chip--color;
		.xf-chipHover();
	}
}

// ############################# MISC #########################

.recaptcha
{
	&.input
	{
		box-sizing: content-box;
		max-width: 100%;
	}

	img
	{
		max-width: 100%;
	}
}

.likesBar
{
	.m-transitionFadeDown(@display: flex);
	align-items: center;
	.xf-minorBlockContent();
	border-left: @xf-borderSizeMinorFeature solid @xf-borderColorFeature;
	padding: @xf-paddingMedium;
	font-size: @xf-fontSizeSmaller;
	margin-top: @xf-paddingMedium;
}

.likeIcon
{
	&:before
	{
		.m-faBase();
		.m-faContent(@fa-var-thumbs-up); //, 1em
		color: @xf-textColorFeature;
		//margin-right: .2em;
	}
}

.reactionsBar
{
	.m-transitionFadeDown(@display: flex);
	align-items: center;
	.xf-minorBlockContent();
	border-left: @xf-borderSizeMinorFeature solid @xf-borderColorFeature;
	padding: @xf-paddingMedium;
	font-size: @xf-fontSizeSmaller;
	margin-top: @xf-paddingMedium;
}

.reactionsBar-link
{
	padding-left: @xf-paddingSmall;
}

.reactionSummary
{
	.m-listPlain();
	flex-shrink: 0;
	height: 16px;
	margin: 0 -2px;

	> li
	{
		display: inline-block;
		height: 20px;
		width: 20px;
		padding: 2px;
		margin: -2px 0;
		background: @xf-contentBg;
		border-radius: 50%;
		position: relative;
		margin-left: -6px;

		&:nth-child(1)
		{
			z-index: 3;
			margin-left: 0;
		}

		&:nth-child(2)
		{
			z-index: 2;
		}

		&:nth-child(3)
		{
			z-index: 1;
		}
	}

	.reaction
	{
		position: absolute;
		top: 0;

		&.reaction
		{
			// increase specificity to override .reaction.reaction--<size>
			display: block;
		}
	}
}

.reactionsBar,
.message-responseRow
{
	.reactionSummary
	{
		> li
		{
			background: @xf-contentAltBg;
		}

		.reaction
		{
			top: 2px;
		}
	}
}

.reactTooltip
{
	padding: 0;
	display: flex;
	flex-wrap: wrap;
	justify-content: center;
	max-width: 100%;

	.reaction
	{
		margin: @xf-paddingSmall;
		.m-transition(transform);

		&:hover
		{
			.m-transform(scale(1.2));
		}
	}
}

.colorChip
{
	display: inline-block;
	border: @xf-borderSize solid @xf-borderColor;
	border-radius: @xf-borderRadiusMedium;
	padding: 1px;
	width: 100px;
}

.colorChip-inner
{
	display: block;
	background-color: transparent;
	border-radius: @xf-borderRadiusSmall;
	height: 1em;
}

.colorChip-value
{
	display: none;
}

pre.sf-dump
{
	// not ideal, but then again neither is the default of 99999...
	z-index: @zIndex-1 !important;
}

.grecaptcha-badge
{
	z-index: @zIndex-5;
}

// Bookmarking links for 2.1
.bookmarkLink
{
	&:before
	{
		& when (@xf-fontAwesomeWeight >= @faWeight-solid)
		{
			.m-faIcon(@fa-var-regular-bookmark);
		}
		& when (@xf-fontAwesomeWeight < @faWeight-solid)
		{
			.m-faIcon(@fa-var-bookmark);
		}
	}
	&.is-bookmarked
	{
		&:before
		{
			.m-faContent(@fa-var-solid-bookmark);
		}
	}

	&.bookmarkLink--highlightable.is-bookmarked
	{
		color: @xf-textColorAttention;

		&:hover
		{
			color: @xf-textColorAccentContent;
		}
	}

	span
	{
		margin-left: .35em;
	}
}

.p-breadcrumbs > li > a,
a.ui
{
	--ui-pad: .2em;
	--ui-pad-h: 2;
	display: inline-block;
	padding: var(--ui-pad) calc(var(--ui-pad) * var(--ui-pad-h));
	margin: calc(var(--ui-pad) * -1) calc(var(--ui-pad) * var(--ui-pad-h) * -1);
	border-radius: 5px;
	background-color: transparent;
	.m-transition(background-color);

	&:hover
	{
		background-color: fade(@xf-paletteNeutral3, 5%);
		text-decoration: none;
	}
}

// used in H2-H4 anchors
.hoverLink
{
	font-weight: normal;
	font-size: 75%;
	opacity: 0;
	.m-transition(color opacity);

	&:not(:hover)
	{
		color: inherit;
	}

	&:after
	{
		.m-faBase();
		.m-faContent(@fa-var-link);
		position: relative;
		left: .5em;
		bottom: .1em;
	}

	*:hover > &
	{
		opacity: 0.5;

		&:hover
		{
			opacity: 1;
		}
	}

	&:first-child:after
	{
		left: 0;
		right: .5em;
		background: pink;
	}
}

.dragHandle
{
	cursor: move;
	touch-action: none;

	&:before
	{
		.m-faBase();
		.m-faContent(@fa-var-bars);
	}

	.is-undraggable &
	{
		visibility: hidden;
		cursor: default;
	}
}

.memberProfileBanner
{
	background-size: cover !important;
	background-position-x: center !important;
	background-repeat: no-repeat !important;

	&.memberProfileBanner--small
	{
		height: 150px;
		margin-bottom: @xf-paddingMedium;
	}

	&.memberProfileBanner--empty
	{
		display: none;
	}
}

.solutionIcon
{
	display: inline-flex;
	padding: @xf-paddingMedium;
	font-size: @xf-fontSizeLargest;
	line-height: 1;
	color: @xf-textColorMuted;
	.m-transition();

	a&
	{
		color: @xf-textColorMuted;
		text-decoration: none;
	}

	&:before
	{
		& when (@xf-fontAwesomeWeight >= @faWeight-solid)
		{
			.m-faIcon(@fa-var-regular-check-circle);
		}
		& when (@xf-fontAwesomeWeight < @faWeight-solid)
		{
			.m-faIcon(@fa-var-check-circle);
		}
	}

	&.is-solution
	{
		color: @xf-votePositiveColor;
		opacity: 1;

		&:before
		{
			.m-faContent(@fa-var-solid-check-circle);
		}
	}
}

' . $__templater->includeTemplate('core_action_bar.less', $__vars) . '
' . $__templater->includeTemplate('core_labels.less', $__vars) . '
' . $__templater->includeTemplate('core_reaction.less', $__vars) . '
' . $__templater->includeTemplate('core_smilie.less', $__vars) . '
' . $__templater->includeTemplate('core_bbcode.less', $__vars) . '

// RESOLUTION OUTPUT

.debugResolution
{
	.debugResolution-output:before
	{
		content: "Full @{xf-responsiveWide} - @{xf-pageWidthMax}";
		@media (min-width: @xf-pageWidthMax) { content: "Max > @{xf-pageWidthMax}"; }
		@media (max-width: @xf-responsiveWide) { content: "Wide < @{xf-responsiveWide}"; }
		@media (max-width: @xf-responsiveMedium) { content: "Medium < @{xf-responsiveMedium}"; }
		@media (max-width: @xf-responsiveNarrow) { content: "Narrow < @{xf-responsiveNarrow}"; }
	}
}';
	return $__finalCompiled;
}
);