<?php
// FROM HASH: 4bd6e5c5379a527c66df664cf49c8227
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<hr class="formRowSep" />

' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'options[contextual]',
		'selected' => $__vars['options']['contextual'],
		'label' => 'Display contextual content',
		'hint' => 'If selected, the widget will display content which pertains to the location the widget appears in. For example, if the widget appears within a forum, it will only display threads from that forum.',
		'_dependent' => array('
			' . $__templater->formCheckBox(array(
	), array(array(
		'name' => 'options[contextual_hidden]',
		'selected' => $__vars['options']['contextual_hidden'],
		'label' => 'Hide the widget when there is no available context',
		'hint' => 'If selected, the widget will be hidden when there is no available context. Otherwise, all content will be displayed.',
		'_type' => 'option',
	))) . '
		'),
		'_type' => 'option',
	)), array(
	)) . '

';
	$__compilerTemp1 = array();
	if ($__templater->isTraversable($__vars['orders'])) {
		foreach ($__vars['orders'] AS $__vars['order']) {
			$__compilerTemp1[] = array(
				'value' => $__vars['order'],
				'label' => $__templater->func('phrase_dynamic', array('trending_result_order.' . $__vars['order'], ), true),
				'_type' => 'option',
			);
		}
	}
	$__finalCompiled .= $__templater->formRadioRow(array(
		'name' => 'options[order]',
		'value' => $__vars['options']['order'],
	), $__compilerTemp1, array(
		'label' => 'Order by',
		'explain' => 'The method to order results by. Hot sorting weighs newer content more heavily.',
	)) . '

' . $__templater->formNumberBoxRow(array(
		'name' => 'options[duration]',
		'value' => $__vars['options']['duration'],
		'min' => '1',
		'max' => '365',
		'units' => 'Days',
	), array(
		'label' => 'Duration',
		'explain' => 'The number of days to calculate metrics over. For example, you may only include activity that occurred within the last seven days. This value should not exceed your activity log length (' . $__templater->escape($__vars['xf']['options']['activityLogLength']) . ' days).',
	)) . '

' . $__templater->formNumberBoxRow(array(
		'name' => 'options[limit]',
		'value' => $__vars['options']['limit'],
		'min' => '1',
		'max' => '100',
	), array(
		'label' => 'Maximum entries',
	)) . '

' . $__templater->formRadioRow(array(
		'name' => 'options[style]',
		'value' => $__vars['options']['style'],
	), array(array(
		'value' => 'simple',
		'label' => 'Simple',
		'hint' => 'A simple view, designed for narrow spaces such as sidebars.',
		'_type' => 'option',
	),
	array(
		'value' => 'full',
		'label' => 'Standard',
		'hint' => 'A full size view, displaying as a standard content list.',
		'_type' => 'option',
	),
	array(
		'value' => 'carousel',
		'label' => 'Carousel',
		'hint' => 'A carousel view, displaying the content in a slider. This display is not designed for sidebar positions.',
		'_type' => 'option',
	)), array(
		'label' => 'Display style',
	)) . '

' . $__templater->formNumberBoxRow(array(
		'name' => 'options[snippet_length]',
		'value' => $__vars['options']['snippet_length'],
		'min' => '0',
		'max' => '500',
	), array(
		'label' => 'Snippet length',
		'explain' => 'The maximum number of characters that should be displayed in the snippet. Use 0 to display the full snippet.',
	)) . '

';
	$__compilerTemp2 = array(array(
		'value' => '',
		'label' => $__vars['xf']['language']['parenthesis_open'] . 'Any' . $__vars['xf']['language']['parenthesis_close'],
		'_type' => 'option',
	));
	if ($__templater->isTraversable($__vars['contentTypes'])) {
		foreach ($__vars['contentTypes'] AS $__vars['contentType']) {
			$__compilerTemp2[] = array(
				'value' => $__vars['contentType'],
				'label' => '
			' . $__templater->escape($__templater->method($__vars['xf']['app'], 'getContentTypePhrase', array($__vars['contentType'], ))) . '
		',
				'_type' => 'option',
			);
		}
	}
	$__finalCompiled .= $__templater->formSelectRow(array(
		'name' => 'options[content_type]',
		'value' => $__vars['options']['content_type'],
	), $__compilerTemp2, array(
		'label' => 'Content type',
	));
	return $__finalCompiled;
}
);