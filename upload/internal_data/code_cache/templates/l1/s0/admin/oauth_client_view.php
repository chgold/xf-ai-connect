<?php
// FROM HASH: 2336bc641eefbd29571a97c93be10af6
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('OAuth2 client details:' . ' ' . $__templater->escape($__vars['client']['title']));
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formRow('
				<code class="js-clientSecretCopyTarget">' . $__templater->escape($__vars['client']['client_secret']) . '</code>
				' . $__templater->button('', array(
		'icon' => 'copy',
		'data-xf-init' => 'copy-to-clipboard',
		'data-copy-target' => '.js-clientSecretCopyTarget',
		'data-success' => 'Client secret copied to clipboard',
		'class' => 'button--link is-hidden',
	), '', array(
	)) . '
			', array(
		'label' => 'Client secret',
		'rowtype' => 'button',
	)) . '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);