<?php
// FROM HASH: efaf537206e6988ff08847ce6c19772d
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['purchaseRequest']['Purchasable'], 'isCancelled', array($__vars['purchaseRequest'], ))) {
		$__finalCompiled .= '
	<div class="block-rowMessage block-rowMessage--important">
		' . 'This purchase has been cancelled successfully and will expire on the date above.' . '
	</div>
';
	} else {
		$__finalCompiled .= '
	' . $__templater->button('
		' . 'Cancel' . '
	', array(
			'href' => $__templater->func('link', array('purchase/cancel-recurring', null, array('request_key' => $__vars['purchaseRequest']['request_key'], ), ), false),
			'overlay' => 'true',
		), '', array(
		)) . '
';
	}
	return $__finalCompiled;
}
);