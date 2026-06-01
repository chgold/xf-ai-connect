<?php
// FROM HASH: 3a8e6e9bae1b8f73a68ecb5c04f6bbc3
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
	class="has-no-js p-embed-container ' . ($__vars['template'] ? ('template-' . $__templater->escape($__vars['template'])) : '') . '">
<head>
	<meta charset="utf-8" />
	<meta http-equiv="X-UA-Compatible" content="IE=Edge" />
	<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">

	<base target="_blank">

	<title>' . ($__vars['embeddedContent'] ? $__templater->escape($__templater->method($__vars['embeddedContent'], 'getContentTitle', array())) : 'Error') . '</title>

	' . $__templater->callMacro(null, 'public:helper_js_global::head', array(
		'app' => 'public',
	), $__vars) . '
</head>
<body>
' . $__templater->filter($__vars['content'], array(array('raw', array()),), true) . '
' . $__templater->callMacro(null, 'public:helper_js_global::body', array(
		'app' => 'public',
	), $__vars) . '
</body>
</html>';
	return $__finalCompiled;
}
);