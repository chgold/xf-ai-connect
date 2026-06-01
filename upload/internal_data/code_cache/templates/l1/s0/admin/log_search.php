<?php
// FROM HASH: a27be4bfa1c189af4b88e7476dd56ca4
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Search logs');
	$__finalCompiled .= '

';
	$__compilerTemp1 = array();
	if ($__templater->isTraversable($__vars['typeOptions'])) {
		foreach ($__vars['typeOptions'] AS $__vars['type'] => $__vars['name']) {
			$__compilerTemp1[] = array(
				'name' => 'typeChoices[]',
				'value' => $__vars['type'],
				'label' => $__templater->escape($__vars['name']),
				'selected' => $__templater->func('in_array', array($__vars['type'], $__vars['typeChoices'], ), false),
				'_type' => 'option',
			);
		}
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formTextBoxRow(array(
		'type' => 'search',
		'name' => 'q',
		'value' => $__vars['q'],
		'autocomplete' => 'off',
	), array(
		'label' => 'Search',
	)) . '

			' . $__templater->formRow('

				<div class="inputGroup">
					' . $__templater->formDateInput(array(
		'name' => 'start',
		'value' => $__vars['start'],
		'size' => '15',
	)) . '
					<span class="inputGroup-text">-</span>
					' . $__templater->formDateInput(array(
		'name' => 'end',
		'value' => $__vars['end'],
		'size' => '15',
	)) . '
				</div>
			', array(
		'rowtype' => 'input',
		'label' => 'Log date between',
		'hint' => 'Optional',
	)) . '

			' . $__templater->formCheckBoxRow(array(
		'listclass' => 'js-contentTypes listColumns',
	), $__compilerTemp1, array(
		'label' => 'Search logs',
		'hint' => '
					' . $__templater->formCheckBox(array(
		'standalone' => 'true',
	), array(array(
		'check-all' => '.js-contentTypes',
		'label' => 'Select all',
		'_type' => 'option',
	))) . '
				',
	)) . '

			' . $__templater->formSubmitRow(array(
		'submit' => 'Search logs',
		'fa' => 'fa-file-search',
	), array(
	)) . '
		</div>
	</div>
', array(
		'action' => $__templater->func('link', array('logs/search', ), false),
		'class' => 'block',
	)) . '

';
	if ($__vars['resultTypeSets']) {
		$__finalCompiled .= '
	<div class="block">
		<div class="block-container">
			<h2 class="block-header">' . 'Search results' . '</h2>
			<div class="block-body">
				';
		$__compilerTemp2 = '';
		if ($__templater->isTraversable($__vars['resultTypeSets'])) {
			foreach ($__vars['resultTypeSets'] AS $__vars['resultType']) {
				$__compilerTemp2 .= '
						' . $__templater->escape($__templater->method($__vars['resultType'], 'render', array($__templater->func('templater', array(), false), ))) . '
					';
			}
		}
		$__finalCompiled .= $__templater->dataList('
					' . $__compilerTemp2 . '
				', array(
		)) . '
			</div>
		</div>
	</div>
';
	}
	return $__finalCompiled;
}
);