<?php
// FROM HASH: 6c3d87c6aa932016c545146497436ba1
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('OAuth2 clients');
	$__finalCompiled .= '

';
	$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('Add OAuth2 client', array(
		'href' => $__templater->func('link', array('oauth2/clients/add', ), false),
		'icon' => 'add',
	), '', array(
	)) . '
');
	$__finalCompiled .= '

';
	if (!$__templater->test($__vars['clients'], 'empty', array())) {
		$__finalCompiled .= '
	';
		$__compilerTemp1 = '';
		if ($__templater->isTraversable($__vars['clients'])) {
			foreach ($__vars['clients'] AS $__vars['client']) {
				$__compilerTemp1 .= '
						' . $__templater->dataRow(array(
					'label' => $__templater->escape($__vars['client']['title']),
					'href' => $__templater->func('link', array('oauth2/clients/edit', $__vars['client'], ), false),
					'delete' => $__templater->func('link', array('oauth2/clients/delete', $__vars['client'], ), false),
					'explain' => '
								<ul class="listInline listInline--bullet">
									<li>
										' . 'Created' . ':
										' . $__templater->func('date_dynamic', array($__vars['client']['creation_date'], array(
				))) . '
									</li>
								</ul>
							',
				), array(array(
					'name' => 'active[' . $__vars['client']['client_id'] . ']',
					'selected' => $__vars['client']['active'],
					'class' => 'dataList-cell--separated',
					'submit' => 'true',
					'tooltip' => 'Enable / disable \'' . $__vars['client']['title'] . '\'',
					'_type' => 'toggle',
					'html' => '',
				))) . '
					';
			}
		}
		$__finalCompiled .= $__templater->form('
		<div class="block-outer">
			' . $__templater->callMacro('filter_macros', 'quick_filter', array(
			'key' => 'oauth2-clients',
			'class' => 'block-outer-opposite',
		), $__vars) . '
		</div>
		<div class="block-container">
			<div class="block-body">
				' . $__templater->dataList('
					' . $__compilerTemp1 . '
				', array(
		)) . '
			</div>
			<div class="block-footer">
				<span class="block-footer-counter">' . $__templater->func('display_totals', array($__vars['clients'], ), true) . '</span>
			</div>
		</div>
	', array(
			'action' => $__templater->func('link', array('oauth2/clients/toggle', ), false),
			'class' => 'block',
			'ajax' => 'true',
		)) . '
';
	} else {
		$__finalCompiled .= '
	<div class="blockMessage">' . 'No items have been created yet.' . '</div>
';
	}
	return $__finalCompiled;
}
);