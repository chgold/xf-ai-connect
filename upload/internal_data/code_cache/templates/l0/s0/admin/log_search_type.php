<?php
// FROM HASH: 9202392abd1e10f552063b4b388af6e8
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<tbody class="dataList-rowGroup">
	' . $__templater->dataRow(array(
		'rowtype' => 'subsection',
		'rowclass' => 'dataList-row--noHover',
	), array(array(
		'_type' => 'cell',
		'html' => $__templater->escape($__vars['typeName']),
	))) . '
	';
	if ($__templater->isTraversable($__vars['results'])) {
		foreach ($__vars['results'] AS $__vars['result']) {
			$__finalCompiled .= '
		';
			$__compilerTemp1 = '';
			if ($__templater->func('is_array', array($__vars['result']['label'], ), false)) {
				$__compilerTemp1 .= '
						<ul class="listInline listInline--selfInline listInline--bullet">
							';
				if ($__templater->isTraversable($__vars['result']['label'])) {
					foreach ($__vars['result']['label'] AS $__vars['label']) {
						$__compilerTemp1 .= '
								<li>' . $__templater->escape($__vars['label']) . '</li>
							';
					}
				}
				$__compilerTemp1 .= '
						</ul>
					';
			} else {
				$__compilerTemp1 .= '
						' . $__templater->escape($__vars['result']['label']) . '
					';
			}
			$__compilerTemp2 = '';
			if ($__vars['result']['User']) {
				$__compilerTemp2 .= '<li>' . $__templater->escape($__vars['result']['User']['username']) . '</li>';
			}
			$__compilerTemp3 = '';
			if ($__vars['result']['ip']) {
				$__compilerTemp3 .= '<li>' . $__templater->filter($__vars['result']['ip'], array(array('ip', array()),), true) . '</li>';
			}
			$__finalCompiled .= $__templater->dataRow(array(
			), array(array(
				'href' => $__vars['result']['link'],
				'overlay' => 'true',
				'label' => '
					' . $__compilerTemp1 . '
				',
				'hint' => $__templater->escape($__vars['result']['hint']),
				'explain' => '
					<ul class="listInline listInline--bullet">
						<li>' . $__templater->func('date_dynamic', array($__vars['result']['date'], array(
			))) . '</li>
						' . $__compilerTemp2 . '
						' . $__compilerTemp3 . '
					</ul>
				',
				'_type' => 'main',
				'html' => '',
			))) . '
	';
		}
	}
	$__finalCompiled .= '
</tbody>';
	return $__finalCompiled;
}
);