<?php
// FROM HASH: c3c8af43f8085d506c3b28358449b52d
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Confirm action');
	$__finalCompiled .= '

' . $__templater->form('

	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'Please confirm that you want to regenerate the following client\'s secret' . ':
				<strong><a href="' . $__templater->func('link', array('oauth2/clients/edit', $__vars['client'], ), true) . '">' . $__templater->escape($__vars['client']['title']) . '</a></strong>
				<span>' . 'Any applications using the old client secret value will not function correctly until they have been updated with the new client secret.' . '</span>
			', array(
		'rowtype' => 'confirm',
	)) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'submit' => 'Regenerate client secret',
	), array(
		'rowtype' => 'simple',
	)) . '
	</div>

', array(
		'action' => $__templater->func('link', array('oauth2/clients/regenerate', $__vars['client'], ), false),
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);