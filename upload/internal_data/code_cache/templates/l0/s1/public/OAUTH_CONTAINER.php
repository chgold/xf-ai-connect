<?php
// FROM HASH: f5839f988b88a0bea71642565e4f0b98
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<!DOCTYPE html>
<html id="XF" lang="' . $__templater->escape($__vars['xf']['language']['language_code']) . '" dir="' . $__templater->escape($__vars['xf']['language']['text_direction']) . '"
	data-app="public"
	' . ((($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array()) AND $__vars['xf']['visitor']['style_variation'])) ? (('data-variation="' . $__templater->escape($__vars['xf']['visitor']['style_variation'])) . '"') : '') . '
' . ((($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array()) AND $__vars['xf']['visitor']['style_variation'])) ? (('data-color-scheme="' . $__templater->func('property_variation', array('styleType', $__vars['xf']['visitor']['style_variation'], ), true)) . '"') : '') . '
data-template="' . $__templater->escape($__vars['template']) . '"
data-cookie-prefix="' . $__templater->escape($__vars['xf']['cookie']['prefix']) . '"
class="' . ($__vars['template'] ? ('template-' . $__templater->escape($__vars['template'])) : '') . '">

<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1">

	<title>' . 'Authorize access to your account' . ' | ' . $__templater->escape($__vars['xf']['options']['boardTitle']) . '</title>

	';
	if ($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array())) {
		$__finalCompiled .= '
		';
		if ($__vars['xf']['visitor']['style_variation']) {
			$__finalCompiled .= '
			<meta name="theme-color" content="' . $__templater->func('parse_less_color', array($__templater->func('property_variation', array('metaThemeColor', $__vars['xf']['visitor']['style_variation'], ), false), ), true) . '" />
			';
		} else {
			$__finalCompiled .= '
			';
			if ($__templater->method($__vars['xf']['style'], 'hasAlternateStyleTypeVariation', array())) {
				$__finalCompiled .= '
				<meta name="theme-color" media="(prefers-color-scheme: ' . $__templater->escape($__templater->method($__vars['xf']['style'], 'getDefaultStyleType', array())) . ')" content="' . $__templater->func('parse_less_color', array($__templater->func('property_variation', array('metaThemeColor', 'default', ), false), ), true) . '" />
				<meta name="theme-color" media="(prefers-color-scheme: ' . $__templater->escape($__templater->method($__vars['xf']['style'], 'getAlternateStyleType', array())) . ')" content="' . $__templater->func('parse_less_color', array($__templater->func('property_variation', array('metaThemeColor', $__templater->method($__vars['xf']['style'], 'getAlternateStyleTypeVariation', array()), ), false), ), true) . '" />
				';
			} else {
				$__finalCompiled .= '
				<meta name="theme-color" content="' . $__templater->func('parse_less_color', array($__templater->func('property_variation', array('metaThemeColor', 'default', ), false), ), true) . '" />
			';
			}
			$__finalCompiled .= '
		';
		}
		$__finalCompiled .= '
		';
	} else {
		$__finalCompiled .= '
		<meta name="theme-color" content="' . $__templater->func('parse_less_color', array($__templater->func('property_variation', array('metaThemeColor', 'default', ), false), ), true) . '" />
	';
	}
	$__finalCompiled .= '

	';
	if ($__templater->isTraversable($__vars['head'])) {
		foreach ($__vars['head'] AS $__vars['headTag']) {
			$__finalCompiled .= '
		' . $__templater->escape($__vars['headTag']) . '
	';
		}
	}
	$__finalCompiled .= '

	' . $__templater->callMacro(null, 'helper_js_global::head', array(
		'app' => 'public',
	), $__vars) . '
</head>

<body data-template="' . $__templater->escape($__vars['template']) . '">
<div>
	<h1>' . $__templater->func('page_h1', array($__vars['xf']['options']['boardTitle'])) . '</h1>

	<div>
		' . $__templater->filter($__vars['content'], array(array('raw', array()),), true) . '
	</div>
</div>

</body>
</html>';
	return $__finalCompiled;
}
);