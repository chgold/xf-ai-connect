<?php
// FROM HASH: 15ffbdd4002d9307e4f2b148ee19a119
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Template search results' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['style']['title']));
	$__finalCompiled .= '

<div class="block">
	';
	if (!$__templater->test($__vars['templates'], 'empty', array())) {
		$__finalCompiled .= '
		<div class="block-outer">
			' . $__templater->callMacro(null, 'filter_macros::quick_filter', array(
			'class' => 'block-outer-opposite',
		), $__vars) . '
		</div>
	';
	}
	$__finalCompiled .= '
	<div class="block-container">

		' . $__templater->callMacro(null, 'filter_macros::filter_bar', array(
		'route' => 'templates/search',
		'content' => $__vars['style'],
		'params' => $__vars['linkParams'],
		'displayValues' => $__vars['filterDisplay'],
		'phrases' => array('key:type' => 'Template type' . $__vars['xf']['language']['label_separator'], 'key:addon_id' => 'Add-on' . $__vars['xf']['language']['label_separator'], 'key:title' => 'Title contains' . $__vars['xf']['language']['label_separator'], 'key:template' => 'Text contains' . $__vars['xf']['language']['label_separator'], 'key:template_cs' => 'Case sensitive', 'key:state' => 'Template status' . $__vars['xf']['language']['label_separator'], 'val:_none' => 'None', 'val:default' => 'Unmodified', 'val:inherited' => 'Modified in a parent style', 'val:custom' => 'Modified in this style', ),
		'menu' => 'templates/refine-search',
	), $__vars) . '

		';
	if (!$__templater->test($__vars['templates'], 'empty', array())) {
		$__finalCompiled .= '
			<div class="block-body">
				' . $__templater->dataList('
					' . $__templater->callMacro(null, 'template_list::template_list', array(
			'templates' => $__vars['templates'],
			'style' => $__vars['style'],
		), $__vars) . '
				', array(
		)) . '
			</div>
			<div class="block-footer">
				<span class="block-footer-counter">' . $__templater->func('display_totals', array($__vars['templates'], ), true) . '</span>
			</div>
		';
	} else {
		$__finalCompiled .= '
			<div class="block-body block-row">' . 'No results found.' . '</div>
		';
	}
	$__finalCompiled .= '
	</div>
</div>';
	return $__finalCompiled;
}
);