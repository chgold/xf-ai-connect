<?php
// FROM HASH: b38d3701ef36f8a24379c49bc25fc1ac
return array(
'macros' => array('your_passkeys' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'passkeys' => '!',
		'linkType' => $__vars['xf']['appType'],
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__templater->includeJs(array(
		'src' => 'xf/webauthn.js',
	));
	$__finalCompiled .= '

	<div class="block">
		<div class="block-container">
			<h3 class="block-header">
				' . 'Your passkeys' . '
				';
	if ($__templater->method($__vars['xf']['request'], 'isSecure', array())) {
		$__finalCompiled .= '
					<span class="u-pullRight u-jsOnly">
						' . $__templater->button('Add passkey', array(
			'href' => $__templater->func('link_type', array($__vars['linkType'], 'account/passkey/add', ), false),
			'fa' => 'fa-plus',
			'data-xf-click' => 'webauthn-click',
		), '', array(
		)) . '
					</span>
				';
	}
	$__finalCompiled .= '
			</h3>
			<div class="block-body">
				' . $__templater->formInfoRow('
					' . 'Passkeys are a secure replacement for passwords, allowing you to use biometric or device-based authentication to access your account.' . '
				', array(
		'rowtype' => 'close',
	)) . '

				';
	if ($__templater->isTraversable($__vars['passkeys'])) {
		foreach ($__vars['passkeys'] AS $__vars['passkey']) {
			$__finalCompiled .= '
					<li class="block-row block-row--separated">
						<div class="contentRow contentRow--alignMiddle">
							';
			if ($__templater->method($__vars['passkey'], 'hasIcon', array())) {
				$__finalCompiled .= '
								<div class="contentRow-figure contentRow-figure--fixedSmall">
									' . $__templater->callMacro(null, 'style_variation_macros::picture_color_scheme', array(
					'light' => $__templater->method($__vars['passkey'], 'getIcon', array('light', )),
					'dark' => $__templater->method($__vars['passkey'], 'getIcon', array('dark', )),
					'width' => '100',
					'height' => '100',
					'alt' => $__templater->method($__vars['passkey'], 'getAuthenticatorName', array()),
				), $__vars) . '
								</div>
							';
			}
			$__finalCompiled .= '
							<div class="contentRow-main">
								<div class="contentRow-extra">
									<div class="buttonGroup">
										' . $__templater->button('Edit', array(
				'href' => $__templater->func('link_type', array($__vars['linkType'], 'account/passkeys/edit', $__vars['passkey'], ), false),
				'class' => 'button--link button--smaller',
				'overlay' => 'true',
			), '', array(
			)) . '
										' . $__templater->button('Delete', array(
				'href' => $__templater->func('link_type', array($__vars['linkType'], 'account/passkeys/delete', $__vars['passkey'], ), false),
				'class' => 'button--link button--smaller',
				'overlay' => 'true',
			), '', array(
			)) . '
									</div>
								</div>

								<h2 class="contentRow-title">' . $__templater->escape($__vars['passkey']['name']) . '</h2>

								<div class="contentRow-minor contentRow-minor--hideLinks">
									<ul class="listInline listInline--bullet">
										<li>
											' . 'Created' . $__vars['xf']['language']['label_separator'] . '
											' . $__templater->func('date_dynamic', array($__vars['passkey']['create_date'], array(
			))) . '
										</li>
										';
			if ($__vars['passkey']['last_use_date']) {
				$__finalCompiled .= '
											<li>
												' . 'Last used' . $__vars['xf']['language']['label_separator'] . '
												' . $__templater->func('date_dynamic', array($__vars['passkey']['last_use_date'], array(
				))) . '
											</li>
										';
			}
			$__finalCompiled .= '
									</ul>
								</div>
							</div>
						</div>
					</li>
				';
		}
	}
	$__finalCompiled .= '
			</div>
		</div>
	</div>
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';

	return $__finalCompiled;
}
);