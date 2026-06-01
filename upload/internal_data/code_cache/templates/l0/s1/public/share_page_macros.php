<?php
// FROM HASH: 49176b95f0d4567c76772a7ed8e1f034
return array(
'macros' => array('buttons' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'iconic' => false,
		'hideLink' => false,
		'label' => '',
		'pageUrl' => '',
		'pageTitle' => '',
		'pageDesc' => '',
		'pageImage' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	$__vars['id'] = $__templater->preEscaped($__templater->func('unique_id', array(), true));
	$__finalCompiled .= '

	';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
					';
	if ($__vars['xf']['options']['facebookLike']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--brand shareButtons-button--facebook" href="#' . $__templater->escape($__vars['id']) . '" data-href="https://www.facebook.com/sharer.php?u={url}">
							' . $__templater->fontAwesome('fab fa-facebook-f', array(
		)) . '
							<span>' . 'Facebook' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['tweet']['enabled']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--brand shareButtons-button--twitter" href="#' . $__templater->escape($__vars['id']) . '" data-href="https://twitter.com/intent/tweet?url={url}&amp;text={title}' . ($__vars['xf']['options']['tweet']['via'] ? ('&amp;via=' . $__templater->escape($__vars['xf']['options']['tweet']['via'])) : '') . ($__vars['xf']['options']['tweet']['related'] ? ('&amp;related=' . $__templater->escape($__vars['xf']['options']['tweet']['related'])) : '') . '">
							' . $__templater->func('x_logo', array(), true) . ' <span>' . 'X (Twitter)' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['linkedinShare']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--brand shareButtons-button--linkedin" href="#' . $__templater->escape($__vars['id']) . '" data-href="https://www.linkedin.com/sharing/share-offsite/?url={url}">
							' . $__templater->fontAwesome('fab fa-linkedin', array(
		)) . '
							<span>' . 'LinkedIn' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['redditShare']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--brand shareButtons-button--reddit" href="#' . $__templater->escape($__vars['id']) . '" data-href="https://reddit.com/submit?url={url}&amp;title={title}">
							' . $__templater->fontAwesome('fab fa-reddit-alien', array(
		)) . '
							<span>' . 'Reddit' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['pinterestShare']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--brand shareButtons-button--pinterest" href="#' . $__templater->escape($__vars['id']) . '" data-href="https://pinterest.com/pin/create/bookmarklet/?url={url}&amp;description={title}&amp;media={image}">
							' . $__templater->fontAwesome('fab fa-pinterest-p', array(
		)) . '
							<span>' . 'Pinterest' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['tumblrShare']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--brand shareButtons-button--tumblr" href="#' . $__templater->escape($__vars['id']) . '" data-href="https://www.tumblr.com/widgets/share/tool?canonicalUrl={url}&amp;title={title}">
							' . $__templater->fontAwesome('fab fa-tumblr', array(
		)) . '
							<span>' . 'Tumblr' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['whatsAppShare']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--brand shareButtons-button--whatsApp" href="#' . $__templater->escape($__vars['id']) . '" data-href="https://api.whatsapp.com/send?text={title}&nbsp;{url}">
							' . $__templater->fontAwesome('fab fa-whatsapp', array(
		)) . '
							<span>' . 'WhatsApp' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['emailShare']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--email" href="#' . $__templater->escape($__vars['id']) . '" data-href="mailto:?subject={title}&amp;body={url}">
							' . $__templater->fontAwesome('far fa-envelope', array(
		)) . '
							<span>' . 'Email' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['webShare']) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--share is-hidden" href="#' . $__templater->escape($__vars['id']) . '"
							data-xf-init="web-share"
							data-title="' . $__templater->escape($__vars['pageTitle']) . '" data-text="' . $__templater->escape($__vars['pageDesc']) . '" data-url="' . $__templater->escape($__vars['pageUrl']) . '"
							data-hide=".shareButtons-button:not(.shareButtons-button--share)">

							' . $__templater->fontAwesome('far fa-share-alt', array(
		)) . '
							<span>' . 'Share' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '

					';
	if ($__vars['xf']['options']['linkShare'] AND (!$__vars['hideLink'])) {
		$__compilerTemp1 .= '
						<a class="shareButtons-button shareButtons-button--link is-hidden" href="#' . $__templater->escape($__vars['id']) . '" data-clipboard="{url}">
							' . $__templater->fontAwesome('far fa-link', array(
		)) . '
							<span>' . 'Link' . '</span>
						</a>
					';
	}
	$__compilerTemp1 .= '
				';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
		';
		$__templater->includeCss('share_controls.less');
		$__finalCompiled .= '

		<div class="shareButtons ' . ($__vars['iconic'] ? 'shareButtons--iconic' : '') . '" data-xf-init="share-buttons" data-page-url="' . $__templater->escape($__vars['pageUrl']) . '" data-page-title="' . $__templater->escape($__vars['pageTitle']) . '" data-page-desc="' . $__templater->escape($__vars['pageDesc']) . '" data-page-image="' . $__templater->escape($__vars['pageImage']) . '">
			<span class="u-anchorTarget" id="' . $__templater->escape($__vars['id']) . '"></span>

			';
		if (!$__templater->test($__vars['label'], 'empty', array())) {
			$__finalCompiled .= '
				<span class="shareButtons-label">' . $__templater->escape($__vars['label']) . '</span>
			';
		}
		$__finalCompiled .= '

			<div class="shareButtons-buttons">
				' . $__compilerTemp1 . '
			</div>
		</div>
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
),
'share_clipboard_input' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'label' => '!',
		'text' => '!',
		'successText' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__templater->includeCss('share_controls.less');
	$__finalCompiled .= '

	';
	$__vars['id'] = $__templater->preEscaped($__templater->func('unique_id', array(), true));
	$__finalCompiled .= '

	<div class="shareInput" data-xf-init="share-input" data-success-text="' . $__templater->escape($__vars['successText']) . '">
		';
	if ($__vars['label']) {
		$__finalCompiled .= '
			<label class="shareInput-label" for="' . $__templater->escape($__vars['id']) . '">' . $__templater->escape($__vars['label']) . '</label>
		';
	}
	$__finalCompiled .= '
		<div class="inputGroup inputGroup--joined">
			<div class="shareInput-button inputGroup-text js-shareButton is-hidden"
				data-xf-init="tooltip" title="' . $__templater->filter('Copy to clipboard', array(array('for_attr', array()),), true) . '">

				' . $__templater->fontAwesome('far fa-copy', array(
	)) . '
			</div>
			' . $__templater->formTextBox(array(
		'class' => 'shareInput-input js-shareInput',
		'value' => $__vars['text'],
		'readonly' => 'true',
		'id' => $__vars['id'],
	)) . '
		</div>
	</div>
';
	return $__finalCompiled;
}
),
'share_clipboard_text' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'text' => '!',
		'successText' => '!',
		'class' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<a href="javascript:"
		data-xf-init="copy-to-clipboard"
		data-copy-text="' . $__templater->escape($__vars['text']) . '"
		data-success="' . $__templater->escape($__vars['successText']) . '"
		class="' . $__templater->escape($__vars['class']) . '">
		' . $__templater->fontAwesome('fa-code', array(
	)) . '
	</a>
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . '

';
	return $__finalCompiled;
}
);