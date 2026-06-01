<?php
// FROM HASH: d9ca21674619f6c0337dadcc48ec6ef0
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->setPageParam('template', 'LOGIN_CONTAINER');
	$__finalCompiled .= '

';
	$__templater->includeJs(array(
		'src' => 'xf/webauthn.js',
		'min' => '1',
	));
	$__finalCompiled .= '

<div class="adminLogin-contentForm">
	';
	$__vars['afterInput'] = $__templater->preEscaped($__templater->fontAwesome('fa-key', array(
	)));
	$__finalCompiled .= $__templater->form('
		<div><a href="' . $__templater->escape($__vars['xf']['options']['boardUrl']) . '"><img src="' . $__templater->func('base_url', array('styles/default/xenforo/xenforo-logo.svg', ), true) . '"
			width="100" height="36" alt="XenForo Ltd." /></a></div>
		<!--<h1>' . $__templater->escape($__vars['xf']['options']['boardTitle']) . '</h1>-->
		<dl class="adminLogin-row">
			<dt>' . 'Your name or email address' . $__vars['xf']['language']['label_separator'] . '</dt>
			<dd>
				' . $__templater->formTextBox(array(
		'name' => 'login',
		'value' => $__vars['xf']['visitor']['username'],
		'placeholder' => 'Username or email' . $__vars['xf']['language']['ellipsis'],
		'aria-label' => 'Username or email',
		'autofocus' => 'autofocus',
	)) . '
				' . $__templater->fontAwesome('fa-user', array(
	)) . '
			</dd>
		</dl>
		<dl class="adminLogin-row">
			<dt>' . 'Password' . $__vars['xf']['language']['label_separator'] . '</dt>
			<dd>
				' . '' . '
				' . $__templater->formPasswordBox(array(
		'name' => 'password',
		'placeholder' => 'Password' . $__vars['xf']['language']['ellipsis'],
		'aria-label' => 'Password',
		'afterinputhtml' => $__vars['afterInput'],
	)) . '
			</dd>
		</dl>
		<div class="adminLogin-row adminLogin-row--submit">
			' . $__templater->button('Administrator login', array(
		'type' => 'submit',
		'icon' => 'login',
	), '', array(
	)) . '
		</div>
	', array(
		'action' => $__templater->func('link', array('login/login', ), false),
		'ajax' => 'true',
	)) . '

	';
	if ($__templater->method($__vars['xf']['request'], 'isSecure', array())) {
		$__finalCompiled .= '
		<div class="u-jsOnly">
			<div class="blocks-textJoiner"><span></span><em>' . 'or' . '</em><span></span></div>

			' . $__templater->form('

				<div class="adminLogin-row adminLogin-row--submit">
					' . $__templater->button('
						' . $__templater->func('passkey_icon', array(), true) . ' ' . 'Passkey login' . '
					', array(
			'class' => 'button--provider button--provider--passkey js-webauthnStart',
		), '', array(
		)) . '

					' . $__templater->formHiddenVal('webauthn_payload', '', array(
		)) . '
					' . $__templater->formHiddenVal('webauthn_challenge', ($__vars['passkey'] ? $__templater->method($__vars['passkey'], 'getChallenge', array()) : ''), array(
		)) . '
				</div>
			', array(
			'action' => $__templater->func('link', array('login/login', ), false),
			'data-xf-init' => 'webauthn',
			'data-type' => 'get',
			'data-autotrigger' => 'false',
			'data-autosubmit' => 'true',
			'data-verifying' => 'Verifying' . $__vars['xf']['language']['ellipsis'],
		)) . '
		</div>
	';
	}
	$__finalCompiled .= '

	<div class="adminLogin-boardTitle">' . $__templater->escape($__vars['xf']['options']['boardTitle']) . '</div>

</div>';
	return $__finalCompiled;
}
);