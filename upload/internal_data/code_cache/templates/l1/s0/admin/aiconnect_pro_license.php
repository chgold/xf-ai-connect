<?php
// FROM HASH: bf32e90d00265052f245425c46539273
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('AI Connect Pro — License');
	$__finalCompiled .= '
';
	$__templater->breadcrumb($__templater->preEscaped('AI Connect Pro'), $__templater->func('link', array('addons', ), false), array(
	));
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__vars['msg']) {
		$__compilerTemp1 .= '
                    ';
		if ($__vars['msgType'] == 'success') {
			$__compilerTemp1 .= '
                        <div class="blockMessage blockMessage--success">' . $__templater->escape($__vars['msg']) . '</div>
                    ';
		} else {
			$__compilerTemp1 .= '
                        <div class="blockMessage blockMessage--error">' . $__templater->escape($__vars['msg']) . '</div>
                    ';
		}
		$__compilerTemp1 .= '
                ';
	}
	$__compilerTemp2 = '';
	if ($__vars['status'] AND $__vars['status']['valid']) {
		$__compilerTemp2 .= '
                    <div class="blockMessage blockMessage--success">
                        ';
		if ($__vars['status']['status'] == 'valid') {
			$__compilerTemp2 .= '
                            ✅ <strong>Active</strong> — updates valid until ' . $__templater->func('date', array('Y-m-d', $__templater->func('strtotime', array($__vars['status']['updates_expire_at'], ), false), ), true) . '
                        ';
		} else {
			$__compilerTemp2 .= '
                            ✅ <strong>Perpetual</strong> — license valid forever. Updates expired ' . $__templater->func('date', array('Y-m-d', $__templater->func('strtotime', array($__vars['status']['updates_expire_at'], ), false), ), true) . '.
                            <a href="https://gold-t.co.il/renew" target="_blank">Renew updates →</a>
                        ';
		}
		$__compilerTemp2 .= '
                        <br><small>Registered domain: <code>' . $__templater->escape($__vars['status']['licensed_domain']) . '</code></small>
                    </div>
                ';
	} else if ($__vars['status'] AND (!$__vars['status']['valid'])) {
		$__compilerTemp2 .= '
                    <div class="blockMessage blockMessage--error">
                        ';
		if ($__vars['status']['status'] == 'invalid_domain') {
			$__compilerTemp2 .= '
                            ❌ Domain mismatch — license is registered to <code>' . $__templater->escape($__vars['status']['licensed_domain']) . '</code>.
                            <a href="https://gold-t.co.il/license-reset" target="_blank">Reset domain →</a>
                        ';
		} else if ($__vars['status']['status'] == 'invalid_key') {
			$__compilerTemp2 .= '
                            ❌ License key not found. Verify your key is correct.
                        ';
		} else if ($__vars['status']['status'] == 'suspended') {
			$__compilerTemp2 .= '
                            ❌ License suspended. Contact <a href="mailto:support@gold-t.co.il">support@gold-t.co.il</a>.
                        ';
		} else if ($__vars['status']['status'] == 'no_license') {
			$__compilerTemp2 .= '
                            ⚠️ No license key entered. Enter your key below and click "Check License".
                        ';
		} else {
			$__compilerTemp2 .= '
                            ⚠️ Could not reach license server. Will retry automatically next week.
                        ';
		}
		$__compilerTemp2 .= '
                    </div>
                ';
	}
	$__finalCompiled .= $__templater->form('
    <div class="block">
        <div class="block-container">
            <h2 class="block-header">License Status</h2>
            <div class="block-body">

                ' . $__compilerTemp1 . '

                ' . $__compilerTemp2 . '

                <dl class="formRow">
                    <dt class="formRow-label">License Key</dt>
                    <dd class="formRow-input">
                        <input type="text" name="options[aiconnect_pro_license_key]" value="' . $__templater->escape($__vars['license_key']) . '"
                               class="input--text" placeholder="XFPRO-XXXX-XXXX-XXXX-XXXX" style="width:100%;max-width:400px">
                        <div class="formRow-explain">
                            Found in your purchase confirmation email.
                            <a href="https://gold-t.co.il/license-reset" target="_blank">Transfer to new domain</a>
                        </div>
                    </dd>
                </dl>

            </div>
            <div class="block-footer">
                <button type="submit" class="button button--primary">Check &amp; Save License</button>
                <a href="' . $__templater->func('link', array('options', null, array('group' => 'aiconnect_pro_license', ), ), true) . '" class="button">Advanced Options</a>
            </div>
        </div>
    </div>
', array(
		'action' => $__templater->func('link', array('aiconnect-pro/license/check', ), false),
		'ajax' => 'true',
	)) . '
';
	return $__finalCompiled;
}
);