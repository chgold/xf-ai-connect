<?php
// FROM HASH: 0f696619f7bbbf9ecb14822049dbf7ac
return array(
'macros' => array('article' => array(
'extends' => 'featured_content_item::article',
'arguments' => function($__templater, array $__vars) { return array(
		'feature' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('links' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__vars['feature']['content_link']) . '">' . 'View full post' . ' &raquo;</a>
	';
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	$__vars['post'] = $__vars['content']['FirstPost'];
	$__finalCompiled .= '

		<li>
			<a href="' . $__templater->func('link', array('threads/post', $__vars['content'], array('post_id' => $__vars['post']['post_id'], ), ), true) . '" class="u-concealed" rel="nofollow"
				aria-label="' . $__templater->filter('Share', array(array('for_attr', array()),), true) . '"
				data-xf-init="share-tooltip" data-href="' . $__templater->func('link', array('posts/share', $__vars['post'], ), true) . '">

				' . $__templater->fontAwesome('fa-share-alt', array(
	)) . '
			</a>
		</li>

		';
	if ($__vars['xf']['options']['embedCodeShare'] AND $__templater->func('method_exists', array($__vars['post'], 'getEmbedCodeHtml', ), false)) {
		$__finalCompiled .= '
			<li class="u-hidden js-embedCopy">
				' . $__templater->callMacro(null, 'share_page_macros::share_clipboard_text', array(
			'class' => 'u-concealed',
			'text' => $__templater->method($__vars['content'], 'getEmbedCodeHtml', array()),
			'successText' => 'Embed code HTML copied to clipboard.',
		), $__vars) . '
			</li>
		';
	}
	$__finalCompiled .= '

		';
	if ($__vars['content']['reply_count']) {
		$__finalCompiled .= '
			<li class="articlePreview-replies">
				<a href="' . $__templater->escape($__vars['feature']['content_link']) . '" title="' . $__templater->filter('Replies' . $__vars['xf']['language']['label_separator'], array(array('for_attr', array()),), true) . ' ' . $__templater->filter($__vars['content']['reply_count'], array(array('number', array()),), true) . '">
					' . $__templater->fontAwesome('fa-comment', array(
			'class' => 'u-spaceAfter',
		)) . '<span class="articlePreview-repliesLabel">' . 'Replies' . $__vars['xf']['language']['label_separator'] . ' </span>' . $__templater->filter($__vars['content']['reply_count'], array(array('number', array()),), true) . '
				</a>
			</li>
		';
	}
	$__finalCompiled .= '
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('links', $__vars, $__extensions) . '

	' . $__templater->renderExtension('meta', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'simple' => array(
'extends' => 'featured_content_item::simple',
'arguments' => function($__templater, array $__vars) { return array(
		'feature' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('extra' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		<div class="contentRow-minor contentRow-minor--hideLinks">
			' . 'Replies' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->filter($__vars['content']['reply_count'], array(array('number_short', array()),), true) . '
		</div>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('extra', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'carousel' => array(
'extends' => 'featured_content_item::carousel',
'arguments' => function($__templater, array $__vars) { return array(
		'feature' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		<li>' . 'Replies' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->filter($__vars['content']['reply_count'], array(array('number', array()),), true) . '</li>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('meta', $__vars, $__extensions) . '
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