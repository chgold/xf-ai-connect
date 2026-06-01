<?php
// FROM HASH: aa8aa8d76bb5364790de3fe2bdc7761b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Import complete!');
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'Your import is now complete and the necessary caches have been rebuilt.' . '
			', array(
	)) . '
			' . $__templater->callMacro(null, 'import_finalize::notes', array(
		'notes' => $__vars['notes'],
	), $__vars) . '
		</div>
		' . $__templater->formSubmitRow(array(
		'submit' => 'Complete import',
		'sticky' => 'true',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('import/complete', ), false),
		'class' => 'block',
	));
	return $__finalCompiled;
}
);