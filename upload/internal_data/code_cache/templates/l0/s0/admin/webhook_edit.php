<?php
// FROM HASH: 5d4d25b4c8318d7e7a67d7fd318a9bde
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['webhook'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add webhook');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit webhook' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['webhook']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['webhook'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('webhooks/delete', $__vars['webhook'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
');
	}
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__templater->isTraversable($__vars['contentTypeHandlers'])) {
		foreach ($__vars['contentTypeHandlers'] AS $__vars['contentType'] => $__vars['handler']) {
			$__compilerTemp1 .= '
				<h3 class="block-formSectionHeader">
					<span class="collapseTrigger collapseTrigger--block ' . ($__vars['webhook']['events'][$__vars['contentType']] ? 'is-active' : '') . '" data-xf-click="toggle" data-target="< :up:next">
						<span class="block-formSectionHeader-aligner">' . $__templater->escape($__templater->method($__vars['handler'], 'getTitle', array())) . '</span>
					</span>
					<span class="block-desc">
						' . '' . $__templater->func('count', array($__templater->method($__vars['handler'], 'getEvents', array()), ), true) . ' events' . '
					</span>
				</h3>
				<div class="block-body block-body--collapsible ' . ($__vars['webhook']['events'][$__vars['contentType']] ? 'is-active' : '') . '">
					';
			$__compilerTemp2 = array(array(
				'value' => 'none',
				'label' => 'Don\'t send for any events',
				'selected' => (!$__vars['webhook']['events'][$__vars['contentType']]),
				'_type' => 'option',
			)
,array(
				'value' => 'all',
				'label' => 'Send all events',
				'selected' => ($__vars['webhook']['events'][$__vars['contentType']] === '*'),
				'_type' => 'option',
			));
			if ($__templater->func('count', array($__templater->method($__vars['handler'], 'getEvents', array()), ), false)) {
				$__compilerTemp3 = array();
				$__compilerTemp4 = $__templater->method($__vars['handler'], 'getEvents', array());
				if ($__templater->isTraversable($__compilerTemp4)) {
					foreach ($__compilerTemp4 AS $__vars['event']) {
						$__compilerTemp3[] = array(
							'label' => $__templater->escape($__templater->method($__vars['handler'], 'getEventName', array($__vars['event'], ))),
							'hint' => $__templater->escape($__templater->method($__vars['handler'], 'getEventHint', array($__vars['event'], ))),
							'value' => $__vars['event'],
							'_type' => 'option',
						);
					}
				}
				$__compilerTemp2[] = array(
					'value' => 'specific',
					'data-hide' => 'true',
					'data-xf-init' => 'disabler',
					'data-container' => '.js-webhookEvents-' . $__vars['contentType'],
					'label' => 'Send only specific events',
					'selected' => $__templater->func('is_array', array($__vars['webhook']['events'][$__vars['contentType']], ), false),
					'_dependent' => array('
									<div class="js-webhookEvents-' . $__templater->escape($__vars['contentType']) . '">
										' . $__templater->formCheckBox(array(
					'name' => 'events[' . $__vars['contentType'] . '][]',
					'listclass' => 'listColumns',
					'value' => $__vars['webhook']['events'][$__vars['contentType']],
				), $__compilerTemp3) . '
									</div>
								'),
					'_type' => 'option',
				);
			}
			$__compilerTemp1 .= $__templater->formRadioRow(array(
				'name' => 'send_mode[' . $__vars['contentType'] . ']',
			), $__compilerTemp2, array(
				'label' => 'Events',
			)) . '

					<hr class="formRowSep" />

					<div class="webhookCriteria-' . $__templater->escape($__vars['contentType']) . '">
						' . $__templater->filter($__templater->method($__vars['handler'], 'renderCriteria', array($__vars['webhook'], )), array(array('raw', array()),), true) . '
					</div>
				</div>
			';
		}
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['webhook'],
	), array(
		'label' => 'Title',
	)) . '

			' . $__templater->formTextAreaRow(array(
		'name' => 'description',
		'value' => $__vars['webhook'],
		'autosize' => 'true',
	), array(
		'label' => 'Description',
		'explain' => 'This is for reference only and will not be sent to the target URL.',
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'url',
		'value' => $__vars['webhook'],
	), array(
		'label' => 'Target URL',
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'secret',
		'value' => $__vars['webhook'],
	), array(
		'label' => 'Secret',
		'explain' => 'If specified, this value will be included in the <code>XF-Webhook-Secret</code> request header and can be used to validate webhook payloads on the receiving end.',
	)) . '

			' . $__compilerTemp1 . '

			<hr class="block-separator" />

			' . $__templater->formSelectRow(array(
		'name' => 'content_type',
		'value' => $__vars['webhook'],
	), array(array(
		'value' => 'json',
		'label' => 'application/json',
		'_type' => 'option',
	),
	array(
		'value' => 'form_params',
		'label' => 'x-www-form-urlencoded',
		'_type' => 'option',
	)), array(
		'label' => 'Content type',
	)) . '

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'ssl_verify',
		'selected' => $__vars['webhook']['ssl_verify'],
		'label' => 'Enable SSL verification',
		'hint' => 'We do not recommend disabling this as it is insecure and in most cases you will not need to. Disable this if you are aware of the risks and trust the target URL.',
		'_type' => 'option',
	)), array(
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'active',
		'selected' => $__vars['webhook']['active'],
		'label' => 'Webhook is active',
		'hint' => 'Use this to disable the webhook.',
		'_type' => 'option',
	)), array(
	)) . '

			' . $__templater->formSubmitRow(array(
		'icon' => 'save',
	), array(
	)) . '
		</div>
	</div>
', array(
		'action' => $__templater->func('link', array('webhooks/save', $__vars['webhook'], ), false),
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);