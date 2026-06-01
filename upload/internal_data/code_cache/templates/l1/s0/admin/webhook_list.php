<?php
// FROM HASH: a8a0b4593d3a5dd136f6972b94b871ff
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Webhooks');
	$__finalCompiled .= '

';
	$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('Add webhook', array(
		'href' => $__templater->func('link', array('webhooks/add', ), false),
		'icon' => 'add',
	), '', array(
	)) . '
');
	$__finalCompiled .= '

';
	if (!$__templater->test($__vars['webhooks'], 'empty', array())) {
		$__finalCompiled .= '
	';
		$__compilerTemp1 = '';
		if ($__templater->isTraversable($__vars['webhooks'])) {
			foreach ($__vars['webhooks'] AS $__vars['webhook']) {
				$__compilerTemp1 .= '
						' . $__templater->dataRow(array(
					'label' => $__templater->escape($__vars['webhook']['title']),
					'hint' => $__templater->escape($__templater->method($__vars['webhook'], 'getFormattedEvents', array())),
					'explain' => $__templater->escape($__vars['webhook']['description']),
					'href' => $__templater->func('link', array('webhooks/edit', $__vars['webhook'], ), false),
					'delete' => $__templater->func('link', array('webhooks/delete', $__vars['webhook'], ), false),
				), array(array(
					'name' => 'active[' . $__vars['webhook']['webhook_id'] . ']',
					'selected' => $__vars['webhook']['active'],
					'class' => 'dataList-cell--separated',
					'submit' => 'true',
					'tooltip' => 'Enable / disable \'' . $__vars['webhook']['title'] . '\'',
					'_type' => 'toggle',
					'html' => '',
				))) . '
					';
			}
		}
		$__finalCompiled .= $__templater->form('
		<div class="block-outer">
			' . $__templater->callMacro('filter_macros', 'quick_filter', array(
			'key' => 'webhooks',
			'class' => 'block-outer-opposite',
		), $__vars) . '
		</div>
		<div class="block-container">
			<div class="block-body">
				' . $__templater->dataList('
					' . $__compilerTemp1 . '
				', array(
		)) . '
			</div>
			<div class="block-footer">
				<span class="block-footer-counter">' . $__templater->func('display_totals', array($__vars['webhooks'], ), true) . '</span>
			</div>
		</div>
	', array(
			'action' => $__templater->func('link', array('webhooks/toggle', ), false),
			'class' => 'block',
			'ajax' => 'true',
		)) . '
';
	} else {
		$__finalCompiled .= '
	<div class="blockMessage">' . 'No items have been created yet.' . '</div>
';
	}
	return $__finalCompiled;
}
);