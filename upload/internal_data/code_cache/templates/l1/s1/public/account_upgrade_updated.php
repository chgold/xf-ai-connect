<?php
// FROM HASH: dde451e61453740c9f6055e1891e3b4e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Changed payment confirmation');
	$__finalCompiled .= '

';
	$__templater->wrapTemplate('account_wrapper', $__vars);
	$__finalCompiled .= '

<div class="blockMessage">' . 'The request to change your payment has been received and will be processed shortly. You will receive an email once complete.' . '</div>';
	return $__finalCompiled;
}
);