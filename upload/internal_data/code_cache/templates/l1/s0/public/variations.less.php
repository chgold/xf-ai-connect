<?php
// FROM HASH: 860c18c842fce545ad536fcbe1e3f4c0
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= ':root
{
	color-scheme: @xf-styleType;

	--xf-color-adjust: ' . (($__templater->method($__vars['xf']['style'], 'getDefaultStyleType', array()) === 'light') ? 1 : -1) . ';

	';
	$__compilerTemp1 = $__templater->method($__vars['xf']['style'], 'getVariationVariables', array('default', ));
	if ($__templater->isTraversable($__compilerTemp1)) {
		foreach ($__compilerTemp1 AS $__vars['property'] => $__vars['value']) {
			$__finalCompiled .= '
		--xf-' . $__templater->escape($__vars['property']) . ': ~"' . $__templater->escape($__vars['value']) . '";
	';
		}
	}
	$__finalCompiled .= '

	';
	$__compilerTemp2 = $__templater->method($__vars['xf']['style'], 'getVariationVariables', array('default', true, ));
	if ($__templater->isTraversable($__compilerTemp2)) {
		foreach ($__compilerTemp2 AS $__vars['property'] => $__vars['value']) {
			$__finalCompiled .= '
		--xf-' . $__templater->escape($__vars['property']) . ': var(--xf-' . $__templater->escape($__vars['property']) . '--h), var(--xf-' . $__templater->escape($__vars['property']) . '--s), var(--xf-' . $__templater->escape($__vars['property']) . '--l), var(--xf-' . $__templater->escape($__vars['property']) . '--a);
		--xf-' . $__templater->escape($__vars['property']) . '--h: hue(' . $__templater->escape($__vars['value']) . ');
		--xf-' . $__templater->escape($__vars['property']) . '--s: saturation(' . $__templater->escape($__vars['value']) . ');
		--xf-' . $__templater->escape($__vars['property']) . '--l: lightness(' . $__templater->escape($__vars['value']) . ');
		--xf-' . $__templater->escape($__vars['property']) . '--a: alpha(' . $__templater->escape($__vars['value']) . ');
	';
		}
	}
	$__finalCompiled .= '

	';
	if ($__templater->method($__vars['xf']['style'], 'hasAlternateStyleTypeVariation', array())) {
		$__finalCompiled .= '
		&:not([data-variation])
		{
			@media (prefers-color-scheme: ' . $__templater->escape($__templater->method($__vars['xf']['style'], 'getAlternateStyleType', array())) . ')
			{
				--xf-color-adjust: ' . (($__templater->method($__vars['xf']['style'], 'getAlternateStyleType', array()) === 'light') ? 1 : -1) . ';

				';
		$__compilerTemp3 = $__templater->method($__vars['xf']['style'], 'getVariationVariables', array($__templater->method($__vars['xf']['style'], 'getAlternateStyleTypeVariation', array()), ));
		if ($__templater->isTraversable($__compilerTemp3)) {
			foreach ($__compilerTemp3 AS $__vars['property'] => $__vars['value']) {
				$__finalCompiled .= '
					--xf-' . $__templater->escape($__vars['property']) . ': ~"' . $__templater->escape($__vars['value']) . '";
				';
			}
		}
		$__finalCompiled .= '

				';
		$__compilerTemp4 = $__templater->method($__vars['xf']['style'], 'getVariationVariables', array($__templater->method($__vars['xf']['style'], 'getAlternateStyleTypeVariation', array()), true, ));
		if ($__templater->isTraversable($__compilerTemp4)) {
			foreach ($__compilerTemp4 AS $__vars['property'] => $__vars['value']) {
				$__finalCompiled .= '
					--xf-' . $__templater->escape($__vars['property']) . '--h: hue(' . $__templater->escape($__vars['value']) . ');
					--xf-' . $__templater->escape($__vars['property']) . '--s: saturation(' . $__templater->escape($__vars['value']) . ');
					--xf-' . $__templater->escape($__vars['property']) . '--l: lightness(' . $__templater->escape($__vars['value']) . ');
					--xf-' . $__templater->escape($__vars['property']) . '--a: alpha(' . $__templater->escape($__vars['value']) . ');
				';
			}
		}
		$__finalCompiled .= '
			}
		}
	';
	}
	$__finalCompiled .= '

	';
	$__compilerTemp5 = $__templater->method($__vars['xf']['style'], 'getVariations', array(false, ));
	if ($__templater->isTraversable($__compilerTemp5)) {
		foreach ($__compilerTemp5 AS $__vars['variation']) {
			$__finalCompiled .= '
		&[data-variation="' . $__templater->escape($__vars['variation']) . '"]
		{
			--xf-color-adjust: ' . (($__templater->method($__vars['xf']['style'], 'getPropertyVariation', array('styleType', $__vars['variation'], 'light', )) === 'light') ? 1 : -1) . ';

			';
			$__compilerTemp6 = $__templater->method($__vars['xf']['style'], 'getVariationVariables', array($__vars['variation'], ));
			if ($__templater->isTraversable($__compilerTemp6)) {
				foreach ($__compilerTemp6 AS $__vars['property'] => $__vars['value']) {
					$__finalCompiled .= '
				--xf-' . $__templater->escape($__vars['property']) . ': ~"' . $__templater->escape($__vars['value']) . '";
			';
				}
			}
			$__finalCompiled .= '

			';
			$__compilerTemp7 = $__templater->method($__vars['xf']['style'], 'getVariationVariables', array($__vars['variation'], true, ));
			if ($__templater->isTraversable($__compilerTemp7)) {
				foreach ($__compilerTemp7 AS $__vars['property'] => $__vars['value']) {
					$__finalCompiled .= '
				--xf-' . $__templater->escape($__vars['property']) . '--h: hue(' . $__templater->escape($__vars['value']) . ');
				--xf-' . $__templater->escape($__vars['property']) . '--s: saturation(' . $__templater->escape($__vars['value']) . ');
				--xf-' . $__templater->escape($__vars['property']) . '--l: lightness(' . $__templater->escape($__vars['value']) . ');
				--xf-' . $__templater->escape($__vars['property']) . '--a: alpha(' . $__templater->escape($__vars['value']) . ');
			';
				}
			}
			$__finalCompiled .= '
		}
	';
		}
	}
	$__finalCompiled .= '
}';
	return $__finalCompiled;
}
);