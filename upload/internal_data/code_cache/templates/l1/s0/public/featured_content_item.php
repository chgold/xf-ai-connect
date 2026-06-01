<?php
// FROM HASH: b47cba7b780db95497b1c32419e0989d
return array(
'macros' => array('article' => array(
'extends' => 'content_display_macros::article',
'arguments' => function($__templater, array $__vars) { return array(
		'feature' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	if ($__vars['feature']['image']) {
		$__finalCompiled .= '
			<a href="' . $__templater->escape($__vars['feature']['content_link']) . '" class="articlePreview-image" tabindex="-1">
				<img src="' . $__templater->escape($__vars['feature']['image']) . '" alt="' . $__templater->escape($__vars['feature']['title']) . '" loading="lazy" />
			</a>
		';
	}
	$__finalCompiled .= '
	';
	return $__finalCompiled;
},
'title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__vars['feature']['content_link']) . '">' . $__templater->escape($__vars['feature']['title']) . '</a>
	';
	return $__finalCompiled;
},
'snippet' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		<div class="bbWrapper">
			';
	if ($__vars['snippetLength']) {
		$__finalCompiled .= '
				' . $__templater->func('snippet', array($__vars['feature']['snippet'], $__vars['snippetLength'], ), true) . '
			';
	} else {
		$__finalCompiled .= '
				' . $__templater->escape($__vars['feature']['snippet']) . '
			';
	}
	$__finalCompiled .= '
		</div>
	';
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	$__vars['username'] = $__templater->preEscaped($__templater->func('username_link', array($__vars['feature']['ContentUser'], false, array(
		'defaultname' => $__vars['feature']['content_username'],
		'class' => 'u-concealed',
	))));
	$__finalCompiled .= '

		<li>' . $__templater->func('avatar', array($__vars['feature']['ContentUser'], 'xxs', false, array(
		'defaultname' => $__vars['feature']['content_username'],
	))) . '</li>
		<li class="articlePreview-by">' . 'By ' . $__templater->escape($__vars['username']) . '' . '</li>
		<li><a href="' . $__templater->escape($__vars['feature']['content_link']) . '" class="u-concealed" rel="nofollow">' . $__templater->func('date_dynamic', array($__vars['feature']['content_date'], array(
	))) . '</a></li>
		<li><span data-xf-init="tooltip" title="' . 'Featured on ' . $__templater->func('date', array($__vars['feature']['feature_date'], ), true) . '' . '">' . $__templater->fontAwesome('fa-award', array(
	)) . '</li>
	';
	return $__finalCompiled;
},
'metadata' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		';
	$__vars['ldJson'] = $__templater->method($__vars['feature'], 'getStructuredData', array());
	$__finalCompiled .= '
		';
	if ($__vars['ldJson']) {
		$__finalCompiled .= '
			<script type="application/ld+json">
				' . $__templater->filter($__vars['ldJson'], array(array('json', array(true, )),array('raw', array()),), true) . '
			</script>
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

	' . $__templater->renderExtension('title', $__vars, $__extensions) . '

	' . $__templater->renderExtension('snippet', $__vars, $__extensions) . '

	' . $__templater->renderExtension('meta', $__vars, $__extensions) . '

	' . $__templater->renderExtension('metadata', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'simple' => array(
'extends' => 'content_display_macros::simple',
'arguments' => function($__templater, array $__vars) { return array(
		'feature' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		' . $__templater->func('avatar', array($__vars['feature']['ContentUser'], 'xxs', false, array(
		'defaultname' => $__vars['feature']['content_username'],
	))) . '
	';
	return $__finalCompiled;
},
'title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__vars['feature']['content_link']) . '">' . $__templater->escape($__vars['feature']['title']) . '</a>
	';
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '

		<li>' . ($__templater->escape($__vars['feature']['ContentUser']['username']) ?: $__templater->escape($__vars['feature']['content_username'])) . '</li>
		<li>' . $__templater->func('date_dynamic', array($__vars['feature']['content_date'], array(
	))) . '</li>
		<li><span data-xf-init="tooltip" title="' . 'Featured on ' . $__templater->func('date', array($__vars['feature']['feature_date'], ), true) . '' . '">' . $__templater->fontAwesome('fa-award', array(
	)) . '</li>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('image', $__vars, $__extensions) . '

	' . $__templater->renderExtension('title', $__vars, $__extensions) . '

	' . $__templater->renderExtension('meta', $__vars, $__extensions) . '
';
	return $__finalCompiled;
}
),
'carousel' => array(
'extends' => 'content_display_macros::carousel',
'arguments' => function($__templater, array $__vars) { return array(
		'feature' => '!',
		'content' => '!',
		'snippetLength' => 0,
	); },
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		' . $__templater->func('avatar', array($__vars['feature']['ContentUser'], 'm', false, array(
		'defaultname' => $__vars['feature']['content_username'],
		'notooltip' => 'true',
	))) . '
	';
	return $__finalCompiled;
},
'title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
		$__finalCompiled .= '
		' . $__templater->renderExtensionParent($__vars, null, $__extensions) . '
		<a href="' . $__templater->escape($__vars['feature']['content_link']) . '">' . $__templater->escape($__vars['feature']['title']) . '</a>
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
			' . $__templater->func('snippet', array($__vars['feature']['snippet'], $__vars['snippetLength'], ), true) . '
		';
	} else {
		$__finalCompiled .= '
			' . $__templater->escape($__vars['feature']['snippet']) . '
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

		<li>' . $__templater->func('username_link', array($__vars['feature']['ContentUser'], false, array(
		'defaultname' => $__vars['feature']['content_username'],
	))) . '</li>
		<li>' . $__templater->func('date_dynamic', array($__vars['feature']['content_date'], array(
	))) . '</li>
		<li><span data-xf-init="tooltip" title="' . 'Featured on ' . $__templater->func('date', array($__vars['feature']['feature_date'], ), true) . '' . '">' . $__templater->fontAwesome('fa-award', array(
	)) . '</li>
	';
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	' . $__templater->renderExtension('image', $__vars, $__extensions) . '

	' . $__templater->renderExtension('title', $__vars, $__extensions) . '

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