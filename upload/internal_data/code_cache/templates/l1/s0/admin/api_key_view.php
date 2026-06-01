<?php
// FROM HASH: ef5af48e89197cedbee087d5f1d046d8
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('API key details' . ': ' . $__templater->escape($__vars['apiKey']['title']));
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		<div class="block-body">
			' . $__templater->callMacro(null, 'api_key_macros::copy_key_row', array(
		'apiKey' => $__vars['apiKey'],
	), $__vars) . '
			' . $__templater->callMacro(null, 'api_key_macros::key_type_row', array(
		'apiKey' => $__vars['apiKey'],
	), $__vars) . '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);