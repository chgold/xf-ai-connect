<?php
// FROM HASH: bd40ea263dcdc95093e7431c8fa6087c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['profile'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add payment profile' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['provider']['title']));
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit payment profile' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['provider']['title']) . ' - ' . $__templater->escape($__vars['profile']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['profile'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('payment-profiles/delete', $__vars['profile'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
');
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['profile'], 'isDeprecated', array())) {
		$__finalCompiled .= '
	<div class="blockMessage blockMessage--important blockMessage--iconic">
		';
		if ($__templater->method($__vars['profile'], 'isUpdate', array())) {
			$__finalCompiled .= '
			' . 'This payment provider is deprecated. For backwards compatibility reasons, it will not be removed. However, we strongly recommend deleting it for new purchases and setting up an alternative provider instead. This will not affect existing purchases or subscriptions.' . '
		';
		} else {
			$__finalCompiled .= '
			' . 'This payment provider is deprecated. We strongly recommend setting up an alternative provider instead.' . '
		';
		}
		$__finalCompiled .= '
	</div>
';
	}
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['profile'],
	), array(
		'label' => 'Title',
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'display_title',
		'value' => $__vars['profile'],
	), array(
		'label' => 'Display title',
		'explain' => 'Enter a name for this payment profile to be shown to users when purchasing products with this profile. If no display title is entered, the profile title above will be used instead.',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->filter($__templater->method($__vars['provider'], 'renderConfig', array($__vars['profile'], )), array(array('raw', array()),), true) . '

			' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
		</div>
	</div>

	' . $__templater->formHiddenVal('provider_id', $__vars['provider']['provider_id'], array(
	)) . '
', array(
		'action' => $__templater->func('link', array('payment-profiles/save', $__vars['profile'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);