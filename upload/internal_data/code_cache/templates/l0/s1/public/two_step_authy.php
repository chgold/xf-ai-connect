<?php
// FROM HASH: 9fb2cbf42e26df9f023fc4deb7099f8e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

';
	if ($__vars['context'] == 'setup') {
		$__finalCompiled .= '
	' . $__templater->formInfoRow('
		' . 'If you have not used Authy before, then you will first need to <a href="https://www.authy.com/install" target="_blank">install it</a>. You should receive an SMS/text message with instructions.<br />
<br />
A code will then be generated for you by the Authy app which you will need to enter below to verify your account.' . '
	', array(
		)) . '

	' . $__templater->formTextBoxRow(array(
			'name' => 'code',
			'type' => 'number',
			'autofocus' => 'autofocus',
		), array(
			'label' => 'Verification code',
		)) . '
';
	} else {
		$__finalCompiled .= '
	' . $__templater->formInfoRow('
		' . 'We have sent a push notification to your Authy app. Once you have approved it, you will be logged in.<br />
<br />If you do not, or cannot, receive the notification, you may <a href="' . $__templater->func('link', array('logout', null, array('t' => $__templater->func('csrf_token', array(), false), ), ), true) . '">log out</a> or try another method.' . '

		' . $__templater->formHiddenVal('uuid', $__vars['uuid'], array(
		)) . '
	', array(
			'rowclass' => 'js-authyLoginApprovalRow',
		)) . '

	';
		$__templater->inlineJs('
		XF.extendObject(XF.phrases, {
			authy_no_uuid: "' . $__templater->filter('No approval request UUID. Please try again later.', array(array('escape', array('js', )),), false) . '",
			authy_no_approval_request: "' . $__templater->filter('No approval request found with that UUID. Please try again later.', array(array('escape', array('js', )),), false) . '",
			authy_denied: "' . $__templater->filter('The approval request was denied so we cannot log you in at this time.', array(array('escape', array('js', )),), false) . '",
			authy_success: "' . $__templater->filter('The approval request was successful and you have logged in successfully.', array(array('escape', array('js', )),), false) . '"
		});
	', true);
		$__finalCompiled .= '

	';
		$__templater->inlineJs('
		const form = document.querySelector(\'.js-authyLoginApprovalRow\').closest(\'form\')
		const formData = XF.getDefaultFormData(form)

		const formSubmitRow = form.querySelector(\'.formSubmitRow\')
		formSubmitRow.style.display = \'none\'

		const interval = setInterval(() =>
		{
			XF.ajax(\'post\', form.getAttribute(\'action\'), formData, (data) =>
			{
				if (data.errors)
				{
					const error = data.errors[0]

					switch (error)
					{
						case \'authy_no_uuid\':
						case \'authy_no_approval_request\':
							clearInterval(interval)
							XF.alert(XF.phrase(error, null, \'An unexpected error occurred.\'))
							break

						case \'authy_denied\':
							clearInterval(interval)
							XF.flashMessage(XF.phrase(error), 3000, () =>
							{
								XF.redirect(XF.canonicalizeUrl(\'\'))
							})
							break
					}
				}
				else if (data.redirect)
				{
					clearInterval(interval)
					XF.flashMessage(XF.phrase(\'authy_success\'), 3000, () =>
					{
						XF.redirect(data.redirect)
					})
				}
			}, { skipDefault: true })
		}, 1000)
	', true);
		$__finalCompiled .= '
';
	}
	return $__finalCompiled;
}
);