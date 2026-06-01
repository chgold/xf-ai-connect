<?php
// FROM HASH: f0b37dddcb5cc49b7382a957af3fd325
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Revoke all matching tokens');
	$__finalCompiled .= '

' . $__templater->form('
    <div class="block-container">
        <div class="block-body">
            <div class="block-row">
                <p>' . 'Are you sure you want to revoke all ' . $__templater->escape($__vars['count']) . ' tokens matching the current filter (' . $__templater->escape($__vars['filter']) . ')?' . '</p>
            </div>
        </div>
        ' . $__templater->formSubmitRow(array(
		'icon' => 'delete',
		'sticky' => 'true',
	), array(
	)) . '
    </div>
', array(
		'action' => $__templater->func('link', array('ai-connect-tokens/revoke-all', null, array('filter' => $__vars['filter'], ), ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);