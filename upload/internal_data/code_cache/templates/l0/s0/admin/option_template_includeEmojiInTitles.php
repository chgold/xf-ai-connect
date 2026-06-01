<?php
// FROM HASH: 64f08ed8e991b3c912ac322d1d5d7e20
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formRadioRow(array(
		'name' => $__vars['inputName'],
		'value' => $__vars['option']['option_value'],
	), array(array(
		'value' => 'convert',
		'label' => 'Convert emoji to text',
		'hint' => 'Example: <code>' . '/threads/a-thread-about-my-cat.128/' . '</code>',
		'_type' => 'option',
	),
	array(
		'value' => 'include',
		'label' => 'Keep emoji in title',
		'hint' => 'Example: <code>' . (('/threads/a-thread-about-my-' . $__templater->func('short_to_emoji', array(':cat2:', 'native', ), true)) . '.128/') . '</code>',
		'_type' => 'option',
	),
	array(
		'value' => 'remove',
		'label' => 'Remove emoji from title',
		'hint' => 'Example: <code>' . '/threads/a-thread-about-my.128/' . '</code>',
		'_type' => 'option',
	)), array(
		'label' => $__templater->escape($__vars['option']['title']),
		'hint' => $__templater->escape($__vars['hintHtml']),
		'explain' => $__templater->escape($__vars['explainHtml']),
		'html' => $__templater->escape($__vars['listedHtml']),
		'rowclass' => $__vars['rowClass'],
	));
	return $__finalCompiled;
}
);