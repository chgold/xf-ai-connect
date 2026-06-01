<?php
// FROM HASH: 44725dbb735574795fb4f8dff775c8c5
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit poll');
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

' . $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->callMacro(null, 'poll_macros::add_edit_inputs', array(
		'poll' => $__vars['poll'],
	), $__vars) . '
		</div>

		' . $__templater->func('redirect_input', array($__vars['redirect'], null, true)) . '
		' . $__templater->formSubmitRow(array(
		'icon' => 'save',
		'sticky' => 'true',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->method($__vars['poll'], 'getLink', array('edit', )),
		'class' => 'block',
		'ajax' => 'true',
	)) . '

';
	if ($__vars['poll'] AND $__templater->method($__vars['poll'], 'canDelete', array())) {
		$__finalCompiled .= '
	' . $__templater->callMacro(null, 'poll_macros::delete_block', array(
			'poll' => $__vars['poll'],
		), $__vars) . '
';
	}
	return $__finalCompiled;
}
);