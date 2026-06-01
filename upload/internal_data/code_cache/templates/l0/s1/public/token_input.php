<?php
// FROM HASH: a85c267b3740eb09a96b778fcd90e54b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeCss('tagify.less');
	$__finalCompiled .= '

';
	$__templater->includeJs(array(
		'prod' => 'xf/token_input-compiled.js',
		'dev' => 'vendor/tagify/tagify.min.js, xf/token_input.js',
	));
	$__finalCompiled .= '

<input type="hidden" name="' . $__templater->escape($__vars['name']) . '" value="' . $__templater->escape($__vars['value']) . '" class="input ' . $__templater->escape($__vars['inputClass']) . '"
	data-xf-init="token-input"
	data-ac-url="' . $__templater->escape($__vars['hrefAttr']) . '"
	data-min-length="' . $__templater->escape($__vars['minLength']) . '"
	' . ($__vars['maxLength'] ? (('data-max-length="' . $__templater->escape($__vars['maxLength'])) . '"') : '') . '
	' . ($__vars['maxTokens'] ? (('data-max-tokens="' . $__templater->escape($__vars['maxTokens'])) . '"') : '') . '
	' . ($__vars['listData'] ? (('data-list-data="' . $__templater->escape($__vars['listData'])) . '"') : '') . '
	' . $__templater->filter($__vars['attrsHtml'], array(array('raw', array()),), true) . ' />

<script class="js-extraPhrases" type="application/json">
{
	"tagify_empty": "' . $__templater->filter('Empty', array(array('escape', array('js', )),), true) . '",
	"tagify_limit_reached": "' . $__templater->filter('Limit reached', array(array('escape', array('js', )),), true) . '",
	"tagify_pattern_mismatch": "' . $__templater->filter('Pattern mismatch', array(array('escape', array('js', )),), true) . '",
	"tagify_already_exists": "' . $__templater->filter('Already exists', array(array('escape', array('js', )),), true) . '",
	"tagify_not_allowed": "' . $__templater->filter('Not allowed', array(array('escape', array('js', )),), true) . '"
}
</script>';
	return $__finalCompiled;
}
);