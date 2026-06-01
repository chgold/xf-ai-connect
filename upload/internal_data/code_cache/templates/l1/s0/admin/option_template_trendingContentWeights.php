<?php
// FROM HASH: 457c8372ee815be0c9b670693e9e9244
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = '';
	if ($__templater->isTraversable($__vars['metrics'])) {
		foreach ($__vars['metrics'] AS $__vars['metric']) {
			$__compilerTemp1 .= '
		<dl class="inputLabelPair">
			<dt><label for="' . $__templater->escape($__vars['inputName']) . '_0">' . $__templater->func('phrase_dynamic', array('activity_log_metric.' . $__vars['metric'], ), true) . '</label></dt>
			<dd>
				' . $__templater->formNumberBox(array(
				'name' => $__vars['inputName'] . '[' . $__vars['metric'] . ']',
				'value' => $__vars['option']['option_value'][$__vars['metric']],
				'id' => $__vars['inputName'] . '_' . $__vars['metric'],
				'min' => '0',
				'max' => '100',
				'units' => 'Points',
			)) . '
			</dd>
		</dl>
	';
		}
	}
	$__finalCompiled .= $__templater->formRow('

	' . $__compilerTemp1 . '
', array(
		'rowtype' => 'inputLabelPair',
		'label' => $__templater->escape($__vars['option']['title']),
		'hint' => $__templater->escape($__vars['hintHtml']),
		'explain' => $__templater->escape($__vars['explainHtml']),
		'html' => $__templater->escape($__vars['listedHtml']),
		'rowclass' => $__vars['rowClass'],
	));
	return $__finalCompiled;
}
);