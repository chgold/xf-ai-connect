<?php
// FROM HASH: f122d99b9b6e34fe22197137dae29f6b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__vars['xf']['visitor']['user_id']) {
		$__finalCompiled .= '
	<div class="block"' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
		<div class="block-container">
			<div class="block-body block-row">
				' . $__templater->callMacro(null, 'account_visitor_menu::visitor_panel_row', array(), $__vars) . '
			</div>
		</div>
	</div>
';
	}
	return $__finalCompiled;
}
);