<?php
// FROM HASH: 3e40aff696df490992158b38c24c2a6e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => $__vars['inputName'] . '[guest]',
		'checked' => $__vars['option']['option_value']['guest'],
		'label' => 'Count views by guests',
		'_dependent' => array($__templater->formCheckBox(array(
	), array(array(
		'name' => $__vars['inputName'] . '[robot]',
		'checked' => $__vars['option']['option_value']['robot'],
		'label' => 'Count views by robots',
		'_type' => 'option',
	)))),
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