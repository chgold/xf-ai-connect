<?php
// FROM HASH: 4186db0ef80d880974d74dfd62fc4f28
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->button('
	' . 'Change payment' . '
', array(
		'href' => $__templater->func('link', array('purchase/change-payment', null, array('request_key' => $__vars['purchaseRequest']['request_key'], ), ), false),
	), '', array(
	));
	return $__finalCompiled;
}
);