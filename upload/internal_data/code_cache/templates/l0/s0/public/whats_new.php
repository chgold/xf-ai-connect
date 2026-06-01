<?php
// FROM HASH: 9da6ec3781be3df861f3d4489df68b05
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('What\'s new');
	$__finalCompiled .= '

';
	$__compilerTemp1 = $__vars;
	$__compilerTemp1['pageSelected'] = 'overview';
	$__templater->wrapTemplate('whats_new_wrapper', $__compilerTemp1);
	$__finalCompiled .= '

';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
		';
	$__templater->modifySidebarHtml('_xfWidgetPositionSidebar0d77db5647a7b6f430d74c3edcd89238', $__templater->widgetPosition('whats_new_sidebar', array()), 'replace');
	$__compilerTemp2 .= '

		' . $__templater->widgetPosition('whats_new_overview', array()) . '
	';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__finalCompiled .= '
	' . $__compilerTemp2 . '
';
	} else {
		$__finalCompiled .= '
	<div class="blockMessage">
		' . 'No results found.' . '
	</div>
';
	}
	return $__finalCompiled;
}
);