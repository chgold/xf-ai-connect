<?php
// FROM HASH: eb63d7ab825e6f25818b4dcd9c3d4eef
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->setPageParam('template', 'OAUTH_CONTAINER');
	$__finalCompiled .= '
';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Authorize ' . $__templater->escape($__vars['client']['title']) . '');
	$__finalCompiled .= '
';
	$__templater->pageParams['pageH1'] = $__templater->preEscaped('');
	$__finalCompiled .= '

';
	$__templater->includeCss('oauth.less');
	$__finalCompiled .= '
';
	$__compilerTemp1 = '';
	if ($__vars['client']['image_url']) {
		$__compilerTemp1 .= '
						<img src="' . $__templater->func('base_url', array($__vars['client']['image_url'], ), true) . '" alt="' . $__templater->escape($__vars['client']['title']) . '" />
					';
	}
	$__compilerTemp2 = '';
	if ($__vars['client']['homepage_url']) {
		$__compilerTemp2 .= '
						<h2><a href="' . $__templater->escape($__vars['client']['homepage_url']) . '" target="_blank">' . $__templater->escape($__vars['client']['title']) . '</a></h2>
					';
	} else {
		$__compilerTemp2 .= '
						<h2>' . $__templater->escape($__vars['client']['title']) . '</h2>
					';
	}
	$__compilerTemp3 = '';
	if ($__templater->isTraversable($__vars['scopes'])) {
		foreach ($__vars['scopes'] AS $__vars['scope']) {
			$__compilerTemp3 .= '
						<li>' . $__templater->escape($__templater->method($__vars['scope'], 'getDescription', array())) . '</li>
					';
		}
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'The following application is requesting access to your account' . $__vars['xf']['language']['label_separator'] . '

				<div class="application">
					' . $__compilerTemp1 . '
					' . $__compilerTemp2 . '
					<p>' . $__templater->escape($__vars['client']['description']) . '</p>
				</div>

				<p class="u-smaller u-dimmed">' . 'Logged in as' . ' ' . $__templater->func('username_link', array($__vars['xf']['visitor'], false, array(
		'target' => '_blank',
	))) . '. <a href="' . $__templater->func('link', array('logout', null, array('t' => $__templater->func('csrf_token', array(), false), ), ), true) . '">' . 'Not you?' . '</a></p>

				<h5 class="app-info">' . 'Requested scopes' . $__vars['xf']['language']['label_separator'] . '</h5>
				<ul class="listInline listInline--bullet">
					' . $__compilerTemp3 . '
				</ul>

				<h5 class="app-info">' . 'Redirect' . $__vars['xf']['language']['label_separator'] . '</h5>
				<ul class="listInline listInline--bullet u-muted">
					<li>' . 'Once authorized, you will be redirected to' . ' ' . $__templater->escape($__templater->method($__vars['authRequest'], 'getRedirectUriSnippet', array())) . '</li>
				</ul>
			', array(
	)) . '

		</div>
		' . $__templater->formSubmitRow(array(
		'submit' => 'Authorize',
	), array(
		'rowtype' => 'simple',
		'html' => '
				' . $__templater->button('Deny', array(
		'href' => $__templater->func('link', array('oauth2/deny', null, $__vars['linkParams'], ), false),
	), '', array(
	)) . '
			',
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('oauth2/authorize', null, $__vars['linkParams'], ), false),
		'class' => 'block',
	));
	return $__finalCompiled;
}
);