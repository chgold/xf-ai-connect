<?php
// FROM HASH: 53035c088fcd3f67aa1174f3a68355f3
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['client'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add OAuth2 client');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit OAuth2 client:' . ' ' . $__templater->escape($__vars['client']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['client'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('oauth2/clients/delete', $__vars['client'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
');
	}
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__templater->method($__vars['client'], 'isUpdate', array())) {
		$__compilerTemp1 .= '
				' . $__templater->formRow('
					<code class="js-clientIdCopyTarget">' . $__templater->escape($__vars['client']['client_id']) . '</code>
					' . $__templater->button('', array(
			'icon' => 'copy',
			'data-xf-init' => 'copy-to-clipboard',
			'data-copy-target' => '.js-clientIdCopyTarget',
			'data-success' => 'Client ID copied to clipboard',
			'class' => 'button--link is-hidden',
		), '', array(
		)) . '
				', array(
			'label' => 'Client ID',
		)) . '
				' . $__templater->formRow('
					<code>' . $__templater->escape($__vars['client']['client_secret_snippet']) . '</code>
					' . $__templater->button('View secret' . '
					', array(
			'href' => $__templater->func('link', array('oauth2/clients/view-secret', $__vars['client'], ), false),
			'data-xf-click' => 'overlay',
			'class' => 'button--link',
		), '', array(
		)) . '

					' . $__templater->button('Regenerate secret' . '
					', array(
			'href' => $__templater->func('link', array('oauth2/clients/regenerate', $__vars['client'], ), false),
			'data-xf-click' => 'overlay',
			'class' => 'button--link',
		), '', array(
		)) . '
				', array(
			'label' => 'Client secret',
			'rowtype' => 'button',
		)) . '
			';
	}
	$__compilerTemp2 = '';
	if ($__templater->isTraversable($__vars['client']['redirect_uris'])) {
		foreach ($__vars['client']['redirect_uris'] AS $__vars['counter'] => $__vars['uri']) {
			$__compilerTemp2 .= '
						<li class="inputGroup">
							' . $__templater->formTextBox(array(
				'name' => 'redirect_uris[' . $__vars['counter'] . ']',
				'value' => $__vars['uri'],
			)) . '
						</li>
					';
		}
	}
	$__compilerTemp3 = array();
	if ($__templater->isTraversable($__vars['scopes'])) {
		foreach ($__vars['scopes'] AS $__vars['scope']) {
			$__compilerTemp3[] = array(
				'value' => $__vars['scope']['api_scope_id'],
				'label' => $__templater->escape($__vars['scope']['api_scope_id']),
				'hint' => $__templater->escape($__vars['scope']['description']),
				'_type' => 'option',
			);
		}
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['client'],
	), array(
		'label' => 'Title',
	)) . '

			' . $__templater->formTextAreaRow(array(
		'name' => 'description',
		'value' => $__vars['client'],
		'autosize' => 'true',
	), array(
		'label' => 'Description',
	)) . '

			<hr class="formRowSep" />

			' . $__compilerTemp1 . '

			' . $__templater->formRadioRow(array(
		'name' => 'client_type',
		'value' => $__vars['client'],
	), array(array(
		'value' => 'confidential',
		'label' => 'Confidential',
		'_type' => 'option',
	),
	array(
		'value' => 'public',
		'label' => 'Public',
		'_type' => 'option',
	)), array(
		'label' => 'Client type',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formRow($__templater->func('link_type', array('public', 'canonical:oauth2/authorize', ), true), array(
		'label' => 'Authorization endpoint',
	)) . '
			' . $__templater->formRow($__templater->func('link_type', array('api', 'canonical:oauth2/token', ), true), array(
		'label' => 'Token endpoint',
	)) . '
			' . $__templater->formRow($__templater->func('link_type', array('api', 'canonical:oauth2/revoke', ), true), array(
		'label' => 'Token revocation endpoint',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formRow('
				<ul class="listPlain inputGroup-container">
					' . $__compilerTemp2 . '
					<li class="inputGroup" data-xf-init="field-adder" dir="ltr" >
						' . $__templater->formTextBox(array(
		'name' => 'redirect_uris[]',
	)) . '
					</li>
				</ul>
			', array(
		'name' => 'redirect_uris',
		'value' => $__vars['client'],
		'label' => 'Redirect URIs',
		'explain' => 'A list of URIs that may be used for redirecting users back to your application. This must match when authenticating with OAuth2.',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formTextBoxRow(array(
		'name' => 'homepage_url',
		'value' => $__vars['client'],
	), array(
		'label' => 'Homepage URL',
		'explain' => 'An optional URL to the homepage or information page for this application. Users may be directed here when they click on the application name.',
	)) . '

			' . $__templater->formAssetUploadRow(array(
		'name' => 'image_url',
		'value' => $__vars['client'],
		'asset' => 'applications',
	), array(
		'label' => 'Image URL',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formCheckBoxRow(array(
		'name' => 'allowed_scopes',
		'value' => $__vars['client']['allowed_scopes'],
	), $__compilerTemp3, array(
		'label' => 'Allowed scopes',
		'explain' => 'Scopes allow an OAuth2 client to only access certain parts of the API. For security, it is recommended that you restrict scopes to those needed for your application to function.',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'active',
		'value' => '1',
		'selected' => $__vars['client']['active'],
		'label' => 'Client is active',
		'_type' => 'option',
	)), array(
	)) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('oauth2/clients/save', $__vars['client'], ), false),
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);