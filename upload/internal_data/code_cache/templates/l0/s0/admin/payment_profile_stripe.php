<?php
// FROM HASH: 042541b11689fe577007a97455f3385e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formTextBoxRow(array(
		'name' => 'options[live_publishable_key]',
		'value' => $__vars['profile']['options']['live_publishable_key'],
	), array(
		'label' => 'Live publishable key',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[live_secret_key]',
		'value' => $__vars['profile']['options']['live_secret_key'],
	), array(
		'label' => 'Live secret key',
		'explain' => 'Enter the live secret and publishable keys from your Stripe dashboard on the <a href="https://dashboard.stripe.com/account/apikeys" target="_blank">Developers > API keys</a> page. You also need to set up a webhook on the <a href="https://dashboard.stripe.com/account/webhooks">Developers > Webhooks</a> page.',
	)) . '

<hr class="formRowSep" />

' . $__templater->formTextBoxRow(array(
		'name' => 'options[test_publishable_key]',
		'value' => $__vars['profile']['options']['test_publishable_key'],
	), array(
		'label' => 'Test publishable key',
	)) . '

' . $__templater->formTextBoxRow(array(
		'name' => 'options[test_secret_key]',
		'value' => $__vars['profile']['options']['test_secret_key'],
	), array(
		'label' => 'Test secret key',
		'explain' => 'The test keys will only be used if <code>enableLivePayments</code> is set to false in <code>config.php</code>.<br />
<br /><b>Note:</b> Before accepting live payments, you must activate your Stripe account from the Stripe Dashboard.',
	)) . '

<hr class="formRowSep" />

' . $__templater->formTextBoxRow(array(
		'name' => 'options[statement_descriptor]',
		'value' => $__vars['profile']['options']['statement_descriptor'],
		'minlength' => '5',
		'maxlength' => '22',
	), array(
		'label' => 'Statement descriptor',
		'explain' => 'Statement descriptors explain charges or payments on bank statements. Leaving this empty will use the board title. If you wish to set a custom descriptor, it must follow the format <a href="https://stripe.com/docs/statement-descriptors#requirements" target="_blank">outlined in Stripe\'s documentation</a>.',
	)) . '

<hr class="formRowSep" />

';
	$__compilerTemp1 = '';
	if ($__templater->isTraversable($__vars['events'])) {
		foreach ($__vars['events'] AS $__vars['event']) {
			$__compilerTemp1 .= '
				<li><pre>' . $__templater->escape($__vars['event']) . '</pre></li>
			';
		}
	}
	$__finalCompiled .= $__templater->formRow('
	<div class="formRow-explain">
		' . '<strong>Note:</strong> You must set up a webhook endpoint so that Stripe can send messages in order to verify and process payments. You can do this on the <a href="https://dashboard.stripe.com/account/webhooks">Developers > Webhooks</a> page in your dashboard with the following URL:
		<pre><code>' . $__templater->escape($__vars['xf']['options']['boardUrl']) . '/payment_callback.php?_xfProvider=stripe</code></pre>
		You should also configure your webhook endpoint to listen for the following events:' . '

		<ul>
			' . $__compilerTemp1 . '
		</ul>

		' . 'For additional security, it is also recommended to input your "Signing secret" below.' . '
	</div>
', array(
		'label' => '',
	)) . '

<hr class="formRowSep" />

' . $__templater->formCheckBoxRow(array(
	), array(array(
		'label' => 'Verify webhook with signing secret' . $__vars['xf']['language']['label_separator'],
		'selected' => $__vars['profile']['options']['signing_secret'],
		'_dependent' => array($__templater->formTextBox(array(
		'name' => 'options[signing_secret]',
		'value' => $__vars['profile']['options']['signing_secret'],
	))),
		'_type' => 'option',
	)), array(
		'explain' => 'To verify incoming webhook signatures and prevent replay attacks you must provide the &quot;Signing secret&quot;. You can obtain this after setting up the webhook endpoint by clicking the endpoint in your dashboard on the <a href="https://dashboard.stripe.com/account/webhooks">Developers > Webhooks</a> page.',
	)) . '

' . $__templater->formHiddenVal('options[stripe_country]', $__vars['profile']['options']['stripe_country'], array(
	));
	return $__finalCompiled;
}
);