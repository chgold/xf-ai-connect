<?php
// FROM HASH: ea26500ff92688866a8dbc37c8473803
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = array(array(
		'value' => '0',
		'label' => $__vars['xf']['language']['parenthesis_open'] . 'Any' . $__vars['xf']['language']['parenthesis_close'],
		'_type' => 'option',
	));
	if ($__templater->isTraversable($__vars['forums'])) {
		foreach ($__vars['forums'] AS $__vars['forum']) {
			$__compilerTemp1[] = array(
				'value' => $__vars['forum']['value'],
				'disabled' => $__vars['forum']['disabled'],
				'label' => $__templater->escape($__vars['forum']['label']),
				'_type' => 'option',
			);
		}
	}
	$__finalCompiled .= $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'criteria[' . $__vars['contentType'] . '][nodes][rule]',
		'value' => 'node_id',
		'selected' => $__vars['criteria']['nodes'],
		'label' => 'Forum',
		'html' => '
			<ul class="inputList">
				<li>' . $__templater->formRadio(array(
		'name' => 'criteria[' . $__vars['contentType'] . '][nodes][data][search_type]',
		'value' => ($__vars['criteria']['nodes']['data']['search_type'] ?: 'include'),
		'listclass' => 'inputChoices--inline',
	), array(array(
		'value' => 'include',
		'label' => 'Include selected',
		'_type' => 'option',
	),
	array(
		'value' => 'exclude',
		'label' => 'Exclude selected',
		'_type' => 'option',
	))) . '</li>

				<li>' . $__templater->formSelect(array(
		'name' => 'criteria[' . $__vars['contentType'] . '][nodes][data][node_ids]',
		'value' => $__templater->filter($__vars['criteria']['nodes']['data']['node_ids'], array(array('numeric_keys_only', array()),), false),
		'multiple' => 'true',
		'size' => '8',
	), $__compilerTemp1) . '</li>
			</ul>
		',
		'_type' => 'option',
	)), array(
	));
	return $__finalCompiled;
}
);