<?php
// FROM HASH: 326975307bbb1a1d847d6a2215f8cbef
return array(
'macros' => array('head' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'app' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	' . $__templater->callMacro(null, 'public:helper_js_global::head', array(
		'app' => $__vars['app'],
	), $__vars) . '
	';
	$__templater->includeJs(array(
		'src' => 'xf/admin.js',
		'min' => '1',
	));
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
),
'body' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'jsState' => null,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	' . $__templater->callMacro(null, 'public:helper_js_global::body', array(
		'app' => 'admin',
		'jsState' => $__vars['jsState'],
	), $__vars) . '
	<script' . ($__templater->method($__vars['xf']['request'], 'isRocketLoaderDisabled', array()) ? ' data-cfasync="false"' : '') . '>
		window.addEventListener(\'DOMContentLoaded\', function() {
			XF.extendObject(true, XF.config, {
				job: {
					manualUrl: "' . $__templater->filter($__templater->func('link', array('tools/run-job', ), false), array(array('escape', array('js', )),), true) . '"
				},
				visitorCounts: null
			});
			XF.extendObject(XF.phrases, {
				cancel: "' . $__templater->filter('Cancel', array(array('escape', array('js', )),), true) . '",
				cancelling: "' . $__templater->filter('Cancelling', array(array('escape', array('js', )),), true) . '",
				no_items_matched_your_filter: "' . $__templater->filter('No items matched your filter.', array(array('escape', array('js', )),), true) . '"
			});
		});
	</script>
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

';
	return $__finalCompiled;
}
);