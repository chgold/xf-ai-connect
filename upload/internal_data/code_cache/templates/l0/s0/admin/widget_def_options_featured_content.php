<?php
// FROM HASH: 8cb959bd42388586cf656ee2a592d3dd
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

' . $__templater->formNumberBoxRow(array(
		'name' => 'options[limit]',
		'value' => $__vars['options']['limit'],
		'min' => '1',
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
	$__compilerTemp1 = array(array(
		'value' => '',
		'label' => $__vars['xf']['language']['parenthesis_open'] . 'Any' . $__vars['xf']['language']['parenthesis_close'],
		'_type' => 'option',
	));
	if ($__templater->isTraversable($__vars['contentTypes'])) {
		foreach ($__vars['contentTypes'] AS $__vars['contentType']) {
			$__compilerTemp1[] = array(
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
	), $__compilerTemp1, array(
		'label' => 'Content type',
	));
	return $__finalCompiled;
}
);