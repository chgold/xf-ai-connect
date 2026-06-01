<?php
// FROM HASH: 295bad0995f76dbc0718a06c130a2c57
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__compilerTemp1 = array(array(
		'value' => '',
		'label' => $__vars['xf']['language']['parenthesis_open'] . 'Any' . $__vars['xf']['language']['parenthesis_close'],
		'_type' => 'option',
	));
	if ($__templater->isTraversable($__vars['contentTypes'])) {
		foreach ($__vars['contentTypes'] AS $__vars['contentType']) {
			$__compilerTemp1[] = array(
				'value' => $__vars['contentType'],
				'label' => '
						' . $__templater->escape($__templater->method($__vars['xf']['app'], 'getContentTypePhrase', array($__vars['contentType'], ))) . '
					',
				'_type' => 'option',
			);
		}
	}
	$__finalCompiled .= $__templater->form('
	' . '
	<div class="menu-row menu-row--separated">
		<label for="ctrl_content_type">' . 'Content type' . $__vars['xf']['language']['label_separator'] . '</label>
		<div class="u-inputSpacer">
			' . $__templater->formSelect(array(
		'name' => 'content_type',
		'value' => $__vars['filters']['content_type'],
		'id' => 'ctrl_content_type',
	), $__compilerTemp1) . '
		</div>
	</div>

	' . $__templater->formHiddenVal('apply', '1', array(
	)) . '

	<div class="menu-footer">
		<span class="menu-footer-controls">
			' . $__templater->button('Filter', array(
		'type' => 'submit',
		'class' => 'button--primary',
	), '', array(
	)) . '
		</span>
	</div>
', array(
		'action' => $__templater->func('link', array('featured/filters', ), false),
	));
	return $__finalCompiled;
}
);