<?php
// FROM HASH: b90eec749e0ec0909ab9793d39cc5d18
return array(
'macros' => array('field_list' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'fields' => '!',
		'label' => '!',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<ol class="formRow-linkList">
		';
	if ($__templater->isTraversable($__vars['fields'])) {
		foreach ($__vars['fields'] AS $__vars['field']) {
			$__finalCompiled .= '
			<li>
				<!--<a href="' . $__templater->func('link', array('content-types/delete', $__vars['field'], ), true) . '" data-xf-click="overlay" class="link-delete">' . $__templater->fontAwesome('fa-trash-alt', array(
			)) . '</a>-->
				<span class="u-anchorTarget" id="__' . $__templater->escape($__vars['field']['content_type']) . '_' . $__templater->escape($__vars['field']['field_name']) . '"></span>
				<a href="' . $__templater->func('link', array('content-types/edit', $__vars['field'], array('group' => (($__vars['label'] == 'content_type') ? 'field_name' : 'content_type'), ), ), true) . '" data-xf-click="overlay">
					<span class="link-title">' . $__templater->escape($__vars['field'][$__vars['label']]) . '</span>
					<span class="link-hint">' . $__templater->escape($__vars['field']['field_value']) . '</span>
				</a>
			</li>
		';
		}
	}
	$__finalCompiled .= '
	</ol>
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Content types');
	$__finalCompiled .= '

';
	$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('Add content type field', array(
		'href' => $__templater->func('link', array('content-types/add', ), false),
		'icon' => 'add',
	), '', array(
	)) . '
');
	$__finalCompiled .= '

';
	if ($__templater->isTraversable($__vars['_fieldsGrouped'])) {
		foreach ($__vars['_fieldsGrouped'] AS $__vars['contentType'] => $__vars['fields']) {
			$__finalCompiled .= '
	<div class="block">
		<div class="block-container">
			<h2 class="block-header">
				<a href="' . $__templater->func('link', array('content-types/add', array(), array('content_type' => $__vars['contentType'], ), ), true) . '" class="u-pullRight">' . $__templater->fontAwesome('fa-plus-circle', array(
			)) . '</a>
				' . $__templater->escape($__vars['contentType']) . '
			</h2>
			<div class="block-body">
				';
			$__compilerTemp1 = '';
			if ($__templater->isTraversable($__vars['fields'])) {
				foreach ($__vars['fields'] AS $__vars['field']) {
					$__compilerTemp1 .= '
					' . $__templater->dataRow(array(
						'href' => $__templater->func('link', array('content-types/edit', $__vars['field'], ), false),
						'label' => $__templater->escape($__vars['field']['field_name']),
						'hint' => $__templater->escape($__vars['field']['field_value']),
						'hash' => $__vars['field']['content_type'] . '_' . $__vars['field']['field_name'],
						'dir' => 'auto',
					), array()) . '
				';
				}
			}
			$__finalCompiled .= $__templater->dataList('
				' . $__compilerTemp1 . '
				', array(
			)) . '
			</div>
		</div>
	</div>
';
		}
	}
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">

		<h2 class="block-tabHeader tabs hScroller" role="tablist">
			<span class="hScroller-scroll">
				<span class="tabs-tab tab tab-label">' . 'Group by' . $__vars['xf']['language']['label_separator'] . '</span>
				';
	if ($__vars['group'] == 'field_name') {
		$__finalCompiled .= '
					<a class="tabs-tab" role="tab" tabindex="0" aria-controls="content_type" href="' . $__templater->func('link', array('content-types', null, array('group' => 'content_type', ), ), true) . '">' . 'Content types' . '</a>
					<a class="tabs-tab is-active" role="tab" tabindex="0" aria-controls="field_name">' . 'Type fields' . '</a>
				';
	} else {
		$__finalCompiled .= '
					<a class="tabs-tab is-active" role="tab" tabindex="0" aria-controls="content_type">' . 'Content types' . '</a>
					<a class="tabs-tab" role="tab" tabindex="0" aria-controls="field_name" href="' . $__templater->func('link', array('content-types', null, array('group' => 'field_name', ), ), true) . '">' . 'Type fields' . '</a>
				';
	}
	$__finalCompiled .= '
			</span>
		</h2>

		<ul class="tabPanes block-body">
			';
	if ($__vars['group'] == 'field_name') {
		$__finalCompiled .= '
				<li role="tabpanel" id="content_type">-</li>
				<li role="tabpanel" id="field_name" class="is-active">
					';
		if ($__templater->isTraversable($__vars['typesGrouped'])) {
			foreach ($__vars['typesGrouped'] AS $__vars['fieldName'] => $__vars['fields']) {
				$__finalCompiled .= '
						' . $__templater->formRow('
							<a href="' . $__templater->func('link', array('content-types/add', array(), array('field_name' => $__vars['fieldName'], ), ), true) . '"
								class="u-pullRight"
								data-xf-click="overlay"
								data-xf-init="tooltip"
								title="' . 'Add ' . $__templater->escape($__vars['fieldName']) . ' content type field' . '">
								' . $__templater->fontAwesome('fa-plus-circle', array(
				)) . '
							</a>
							' . $__templater->callMacro(null, 'field_list', array(
					'fields' => $__vars['fields'],
					'label' => 'content_type',
				), $__vars) . '
						', array(
					'label' => $__templater->escape($__vars['fieldName']),
					'rowtype' => 'noColon boldLabel stickyLabel',
				)) . '
					';
			}
		}
		$__finalCompiled .= '
				</li>
			';
	} else {
		$__finalCompiled .= '
				<li class="is-active" role="tabpanel" id="content_type">
					';
		if ($__templater->isTraversable($__vars['fieldsGrouped'])) {
			foreach ($__vars['fieldsGrouped'] AS $__vars['contentType'] => $__vars['fields']) {
				$__finalCompiled .= '
						' . $__templater->formRow('
							<span class="u-anchorTarget" id="__' . $__templater->escape($__vars['contentType']) . '"></span>
							<a href="' . $__templater->func('link', array('content-types/add', array(), array('content_type' => $__vars['contentType'], ), ), true) . '"
								class="u-pullRight"
								data-xf-click="overlay"
								data-xf-init="tooltip"
								title="' . 'Add ' . $__templater->escape($__vars['contentType']) . ' content type field' . '">
								' . $__templater->fontAwesome('fa-plus-circle', array(
				)) . '
							</a>
							' . $__templater->callMacro(null, 'field_list', array(
					'fields' => $__vars['fields'],
					'label' => 'field_name',
				), $__vars) . '
						', array(
					'label' => $__templater->escape($__vars['contentType']),
					'rowtype' => 'noColon boldLabel stickyLabel',
				)) . '
					';
			}
		}
		$__finalCompiled .= '
				</li>
				<li role="tabpanel" id="field_name">-</li>
			';
	}
	$__finalCompiled .= '
		</ul>
	</div>
</div>

';
	return $__finalCompiled;
}
);