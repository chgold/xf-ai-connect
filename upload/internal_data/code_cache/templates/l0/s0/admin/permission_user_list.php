<?php
// FROM HASH: 5f60d77d12786a45abf49eb92c782d3f
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('User permissions');
	$__finalCompiled .= '

';
	if (!$__templater->test($__vars['users'], 'empty', array())) {
		$__finalCompiled .= '
	<div class="block">
		<div class="block-outer">
			' . $__templater->callMacro(null, 'filter_macros::quick_filter', array(
			'key' => 'user-permissions',
			'class' => 'block-outer-opposite',
		), $__vars) . '
		</div>
		<div class="block-container">
			<h3 class="block-header">' . 'Users with permissions set' . '</h3>
			<div class="block-body">
				';
		$__compilerTemp1 = '';
		if ($__templater->isTraversable($__vars['users'])) {
			foreach ($__vars['users'] AS $__vars['user']) {
				$__compilerTemp1 .= '
						' . $__templater->dataRow(array(
					'label' => $__templater->escape($__vars['user']['username']),
					'href' => $__templater->func('link', array('permissions/users', $__vars['user'], ), false),
				), array()) . '
					';
			}
		}
		$__finalCompiled .= $__templater->dataList('
					' . $__compilerTemp1 . '
				', array(
		)) . '
			</div>
			<div class="block-footer">
				<span class="block-footer-counter">' . $__templater->func('display_totals', array($__vars['users'], ), true) . '</span>
			</div>
		</div>
	</div>
';
	}
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formTextBoxRow(array(
		'name' => 'username',
		'ac' => 'single',
	), array(
		'label' => 'Set permissions for user',
	)) . '
		</div>
		' . $__templater->formSubmitRow(array(
		'submit' => 'Proceed' . $__vars['xf']['language']['ellipsis'],
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('permissions/users', ), false),
		'method' => 'post',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);