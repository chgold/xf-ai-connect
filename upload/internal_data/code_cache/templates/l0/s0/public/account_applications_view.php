<?php
// FROM HASH: 1605d72bee8b85a8c69363c092d0937c
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Connected application' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['client']['title']));
	$__finalCompiled .= '

';
	$__templater->wrapTemplate('account_wrapper', $__vars);
	$__finalCompiled .= '

';
	$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('Revoke', array(
		'href' => $__templater->func('link', array('account/applications/revoke', $__vars['client'], ), false),
		'class' => 'button--link',
		'overlay' => 'true',
	), '', array(
	)) . '
');
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		<div class="block-body">
			<h3 class="block-header">
				' . $__templater->escape($__vars['client']['title']) . '
			</h3>
		</div>

		<h3 class="block-textHeader">' . 'Permissions' . '</h3>
		<div class="block-body" dir="ltr">
			<ul>
				';
	if ($__templater->isTraversable($__vars['scopes'])) {
		foreach ($__vars['scopes'] AS $__vars['scope']) {
			$__finalCompiled .= '
					<li>' . $__templater->escape($__templater->method($__vars['scope'], 'getDescription', array())) . '</li>
				';
		}
	}
	$__finalCompiled .= '
			</ul>
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);