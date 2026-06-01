<?php
// FROM HASH: 08832898b381275d286a54c48c46c652
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = array(array(
		'label' => $__vars['xf']['language']['parenthesis_open'] . 'All content types' . $__vars['xf']['language']['parenthesis_close'],
		'_type' => 'option',
	));
	if ($__templater->isTraversable($__vars['handlers'])) {
		foreach ($__vars['handlers'] AS $__vars['contentType'] => $__vars['handler']) {
			$__compilerTemp1[] = array(
				'value' => $__vars['contentType'],
				'label' => $__templater->escape($__templater->method($__vars['handler'], 'getContentTypePhrase', array())),
				'_type' => 'option',
			);
		}
	}
	$__compilerTemp2 = array(array(
		'label' => '',
		'value' => '-1',
		'_type' => 'option',
	));
	$__compilerTemp2 = $__templater->mergeChoiceOptions($__compilerTemp2, $__vars['datePresets']);
	$__compilerTemp2[] = array(
		'value' => '1995-01-01',
		'label' => 'All time',
		'_type' => 'option',
	);
	$__finalCompiled .= $__templater->form('
	<div class="menu-row">
		' . 'Content type' . $__vars['xf']['language']['label_separator'] . '
		<div class="u-inputSpacer">
			' . $__templater->formSelect(array(
		'name' => 'content_type',
		'value' => $__vars['conditions']['content_type'],
	), $__compilerTemp1) . '
		</div>
	</div>

	<div class="menu-row menu-row--separated">
		' . 'Username' . $__vars['xf']['language']['label_separator'] . '
		<div class="u-inputSpacer">
			' . $__templater->formTextBox(array(
		'name' => 'username',
		'ac' => 'single',
		'value' => $__vars['conditions']['username'],
		'dir' => 'ltr',
	)) . '
		</div>
	</div>

	<div class="menu-row">
		' . 'Attached between' . $__vars['xf']['language']['label_separator'] . '
		<div class="u-inputSpacer inputGroup inputGroup--auto">
			' . $__templater->formDateInput(array(
		'name' => 'start',
		'value' => ($__vars['conditions']['start'] ? $__templater->func('date', array($__vars['conditions']['start'], 'Y-m-d', ), false) : ''),
	)) . '
			<span class="inputGroup-text">-</span>
			' . $__templater->formDateInput(array(
		'name' => 'end',
		'value' => ($__vars['conditions']['end'] ? $__templater->func('date', array($__vars['conditions']['end'], 'Y-m-d', ), false) : ''),
	)) . '
		</div>
	</div>
	<div class="menu-row menu-row--separated">
		' . 'Date presets' . $__vars['xf']['language']['label_separator'] . '
		<div class="u-inputSpacer">
			' . $__templater->formSelect(array(
		'name' => 'date_preset',
		'class' => 'js-presetChange filterBlock-input',
	), $__compilerTemp2) . '
		</div>
	</div>

	<div class="menu-row">
		' . 'Sort by' . $__vars['xf']['language']['label_separator'] . '
		<div class="u-inputSpacer inputGroup">
			' . $__templater->formSelect(array(
		'name' => 'order',
		'value' => ($__vars['conditions']['order'] ?: 'attach_date'),
	), array(array(
		'value' => 'attach_date',
		'label' => 'Attach date',
		'_type' => 'option',
	),
	array(
		'value' => 'file_size',
		'label' => 'File size',
		'_type' => 'option',
	))) . '
			<span class="inputGroup-splitter"></span>
			' . $__templater->formSelect(array(
		'name' => 'direction',
		'value' => ($__vars['conditions']['direction'] ?: 'desc'),
	), array(array(
		'value' => 'desc',
		'label' => 'Descending',
		'_type' => 'option',
	),
	array(
		'value' => 'asc',
		'label' => 'Ascending',
		'_type' => 'option',
	))) . '
		</div>
	</div>

	<div class="menu-footer">
		<span class="menu-footer-controls">
			' . $__templater->button('Filter', array(
		'type' => 'submit',
		'icon' => 'search',
		'class' => 'button--primary',
	), '', array(
	)) . '
		</span>
	</div>
', array(
		'action' => $__templater->func('link', array('attachments', ), false),
	)) . '

';
	$__templater->inlineJs('
	const ctrl = document.querySelector(\'.js-presetChange\')
	XF.on(ctrl, \'change\', e =>
	{
		const value = ctrl.value
		const form = ctrl.closest(\'form\')

		if (value == -1)
		{
			return
		}

		form.querySelector(ctrl.dataset.start || \'input[name=start]\').value = value
		form.querySelector(ctrl.dataset.end || \'input[name=end]\').value = \'\'
	})
', true);
	return $__finalCompiled;
}
);