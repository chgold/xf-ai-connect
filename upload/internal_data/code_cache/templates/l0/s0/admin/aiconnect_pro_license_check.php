<?php
// FROM HASH: 5edf814e3080581a1d15b624cad30507
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Check AI Connect Pro license');
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__vars['licenseKey']) {
		$__compilerTemp1 .= '
				';
		$__compilerTemp2 = '';
		if ($__vars['status']['licensed_domain']) {
			$__compilerTemp2 .= '
						<br>Registered to: <code>' . $__templater->escape($__vars['status']['licensed_domain']) . '</code>
					';
		}
		$__compilerTemp1 .= $__templater->formInfoRow('
					This contacts the licensing server and refreshes the verdict for
					<strong>' . $__templater->escape($__vars['xf']['options']['boardUrl']) . '</strong>.
					Pro tools are enabled or disabled based on the result.
					<br><br>
					Key on file: <code>' . $__templater->escape($__vars['licenseKey']) . '</code>
					' . $__compilerTemp2 . '
				', array(
			'rowtype' => 'confirm',
		)) . '
			';
	} else {
		$__compilerTemp1 .= '
				' . $__templater->formInfoRow('
					<strong>No license key is saved yet.</strong>
					<br><br>
					Close this window, paste your key into the <em>License key</em> field on
					the options page, click <strong>Save</strong> at the bottom, then run this check.
				', array(
			'rowtype' => 'confirm',
		)) . '
			';
	}
	$__compilerTemp3 = '';
	if ($__vars['licenseKey']) {
		$__compilerTemp3 .= '
			' . $__templater->formSubmitRow(array(
			'icon' => 'refresh',
			'submit' => 'Check license now',
		), array(
		)) . '
		';
	}
	$__finalCompiled .= $__templater->form('

	<div class="block-container">
		<div class="block-body">

			' . $__compilerTemp1 . '

		</div>

		' . $__compilerTemp3 . '
	</div>

', array(
		'action' => $__templater->func('link', array('aiconnect-pro/license/check', ), false),
		'class' => 'block',
		'ajax' => 'true',
	)) . '
';
	return $__finalCompiled;
}
);