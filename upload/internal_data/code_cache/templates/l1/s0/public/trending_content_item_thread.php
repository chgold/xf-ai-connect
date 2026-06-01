<?php
// FROM HASH: 0a8ec26954433969aba7323cfd004c50
return array(
'macros' => array('article' => array(
'extends' => 'trending_content_item::article',
'arguments' => function($__templater, array $__vars) { return array(
		'result' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	if ($__templater->method($__vars['content'], 'getCoverImage', array())) {
		$__finalCompiled .= '
			<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '" class="articlePreview-image" tabindex="-1">
				<img src="' . $__templater->escape($__templater->method($__vars['content'], 'getCoverImage', array())) . '" alt="' . $__templater->escape($__templater->method($__vars['content'], 'getContentTitle', array())) . '" loading="lazy" />
			</a>
		';
	}
	$__finalCompiled .= '
	';
	return $__finalCompiled;
},
'snippet' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		<div class="bbWrapper">
			' . $__templater->func('snippet', array($__vars['content']['FirstPost']['message'], $__vars['snippetLength'], array('stripQuote' => true, ), ), true) . '
		</div>
	';
	return $__finalCompiled;
},
'links' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '">' . 'View full post' . ' &raquo;</a>
	';
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	$__vars['username'] = $__templater->preEscaped($__templater->func('username_link', array($__vars['content']['User'], false, array(
		'defaultname' => $__vars['content']['username'],
		'class' => 'u-concealed',
	))));
	$__finalCompiled .= '
		';
	$__vars['post'] = $__vars['content']['FirstPost'];
	$__finalCompiled .= '

		<li>' . $__templater->func('avatar', array($__vars['content']['User'], 'xxs', false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '</li>
		<li class="articlePreview-by">' . 'By ' . $__templater->escape($__vars['username']) . '' . '</li>
		<li><a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '" class="u-concealed" rel="nofollow">' . $__templater->func('date_dynamic', array($__templater->method($__vars['content'], 'getContentDate', array()), array(
	))) . '</a></li>

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
				<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '" title="' . $__templater->filter('Replies' . $__vars['xf']['language']['label_separator'], array(array('for_attr', array()),), true) . ' ' . $__templater->filter($__vars['content']['reply_count'], array(array('number', array()),), true) . '">
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

	' . $__templater->renderExtension('image', $__vars, $__extensions) . '

	' . $__templater->renderExtension('snippet', $__vars, $__extensions) . '

	' . $__templater->renderExtension('links', $__vars, $__extensions) . '

	' . $__templater->renderExtension('meta', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'simple' => array(
'extends' => 'trending_content_item::simple',
'arguments' => function($__templater, array $__vars) { return array(
		'result' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		' . $__templater->func('avatar', array($__vars['content']['User'], 'xxs', false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '
	';
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		<li>' . ($__templater->escape($__vars['content']['User']['username']) ?: $__templater->escape($__vars['content']['username'])) . '</li>
		<li>' . $__templater->func('date_dynamic', array($__templater->method($__vars['content'], 'getContentDate', array()), array(
	))) . '</li>
	';
	return $__finalCompiled;
},
'extra' => function($__templater, array $__vars, $__extensions = null)
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

	' . $__templater->renderExtension('image', $__vars, $__extensions) . '

	' . $__templater->renderExtension('meta', $__vars, $__extensions) . '

	' . $__templater->renderExtension('extra', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'carousel' => array(
'extends' => 'trending_content_item::carousel',
'arguments' => function($__templater, array $__vars) { return array(
		'result' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		' . $__templater->func('avatar', array($__vars['content']['User'], 'm', false, array(
		'defaultname' => $__vars['content']['username'],
		'notooltip' => 'true',
	))) . '
	';
	return $__finalCompiled;
},
'snippet' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	if ($__vars['snippetLength']) {
		$__finalCompiled .= '
			' . $__templater->func('snippet', array($__vars['content']['FirstPost']['message'], $__vars['snippetLength'], array('stripQuote' => true, ), ), true) . '
		';
	} else {
		$__finalCompiled .= '
			' . $__templater->func('snippet', array($__vars['content']['FirstPost']['message'], $__templater->func('max_length', array($__vars['content']['FirstPost'], 'message', ), false), array('stripQuote' => true, ), ), true) . '
		';
	}
	$__finalCompiled .= '
	';
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		<li>' . $__templater->func('username_link', array($__vars['content']['User'], false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '</li>
		<li>' . $__templater->func('date_dynamic', array($__templater->method($__vars['content'], 'getContentDate', array()), array(
	))) . '</li>
		<li>' . 'Replies' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->filter($__vars['content']['reply_count'], array(array('number', array()),), true) . '</li>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('image', $__vars, $__extensions) . '

	' . $__templater->renderExtension('snippet', $__vars, $__extensions) . '

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