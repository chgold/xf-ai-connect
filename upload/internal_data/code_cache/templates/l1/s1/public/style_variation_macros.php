<?php
// FROM HASH: 7fcc6f021e969347139463d8c42c50c4
return array(
'macros' => array('picture' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'property' => '!',
		'propertyRetina' => null,
		'width' => null,
		'height' => null,
		'alt' => null,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	$__vars['variations'] = array();
	$__finalCompiled .= '

	';
	$__compilerTemp1 = $__templater->method($__vars['xf']['style'], 'getVariations', array());
	if ($__templater->isTraversable($__compilerTemp1)) {
		foreach ($__compilerTemp1 AS $__vars['variation']) {
			$__finalCompiled .= '
		';
			$__vars['src'] = $__templater->func('property_variation', array($__vars['property'], $__vars['variation'], ), false);
			$__finalCompiled .= '
		';
			$__vars['src2x'] = ($__vars['propertyRetina'] ? $__templater->func('property_variation', array($__vars['propertyRetina'], $__vars['variation'], ), false) : null);
			$__finalCompiled .= '

		';
			$__vars['variations'] = $__templater->filter($__vars['variations'], array(array('replace', array($__vars['variation'], array(1 => ($__vars['src'] ? $__templater->func('base_url', array($__vars['src'], ), false) : null), 2 => ($__vars['src2x'] ? $__templater->func('base_url', array($__vars['src2x'], ), false) : null), ), )),), false);
			$__finalCompiled .= '
	';
		}
	}
	$__finalCompiled .= '

	' . $__templater->callMacro(null, 'picture_variations', array(
		'variations' => $__vars['variations'],
		'width' => $__vars['width'],
		'height' => $__vars['height'],
		'alt' => $__vars['alt'],
	), $__vars) . '
';
	return $__finalCompiled;
}
),
'picture_color_scheme' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'light' => '!',
		'dark' => '!',
		'width' => null,
		'height' => null,
		'alt' => null,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	$__vars['light'] = ($__templater->func('is_array', array($__vars['light'], ), false) ? $__vars['light'] : array(1 => $__vars['light'], ));
	$__finalCompiled .= '
	';
	$__vars['dark'] = ($__templater->func('is_array', array($__vars['dark'], ), false) ? $__vars['dark'] : array(1 => $__vars['dark'], ));
	$__finalCompiled .= '

	';
	$__vars['variations'] = array();
	$__finalCompiled .= '

	';
	$__compilerTemp1 = $__templater->method($__vars['xf']['style'], 'getVariations', array());
	if ($__templater->isTraversable($__compilerTemp1)) {
		foreach ($__compilerTemp1 AS $__vars['variation']) {
			$__finalCompiled .= '
		';
			$__vars['styleType'] = $__templater->func('property_variation', array('styleType', $__vars['variation'], 'light', ), false);
			$__finalCompiled .= '
		';
			$__vars['src'] = (($__vars['styleType'] === 'light') ? $__vars['light']['1'] : $__vars['dark']['1']);
			$__finalCompiled .= '
		';
			$__vars['src2x'] = (($__vars['styleType'] === 'light') ? $__vars['light']['2'] : $__vars['dark']['2']);
			$__finalCompiled .= '

		';
			$__vars['variations'] = $__templater->filter($__vars['variations'], array(array('replace', array($__vars['variation'], array(1 => ($__vars['src'] ? $__templater->func('base_url', array($__vars['src'], ), false) : null), 2 => ($__vars['src2x'] ? $__templater->func('base_url', array($__vars['src2x'], ), false) : null), ), )),), false);
			$__finalCompiled .= '
	';
		}
	}
	$__finalCompiled .= '

	' . $__templater->callMacro(null, 'picture_variations', array(
		'variations' => $__vars['variations'],
		'width' => $__vars['width'],
		'height' => $__vars['height'],
		'alt' => $__vars['alt'],
	), $__vars) . '
';
	return $__finalCompiled;
}
),
'picture_variations' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'variations' => '!',
		'width' => null,
		'height' => null,
		'alt' => null,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	<picture data-variations="' . $__templater->filter($__vars['variations'], array(array('json', array()),), true) . '">
		';
	$__vars['variation'] = ((($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array()) AND $__vars['xf']['visitor']['style_variation'])) ? $__vars['xf']['visitor']['style_variation'] : 'default');
	$__finalCompiled .= '
		';
	$__vars['src'] = $__vars['variations'][$__vars['variation']]['1'];
	$__finalCompiled .= '
		';
	$__vars['src2x'] = $__vars['variations'][$__vars['variation']]['2'];
	$__finalCompiled .= '

		';
	if ($__templater->method($__vars['xf']['style'], 'isVariationsEnabled', array()) AND ((!$__vars['xf']['visitor']['style_variation']) AND $__templater->method($__vars['xf']['style'], 'hasAlternateStyleTypeVariation', array()))) {
		$__finalCompiled .= '
			';
		$__vars['alternateVariation'] = $__templater->method($__vars['xf']['style'], 'getAlternateStyleTypeVariation', array());
		$__finalCompiled .= '
			';
		$__vars['alternateSrc'] = $__vars['variations'][$__vars['alternateVariation']]['1'];
		$__finalCompiled .= '
			';
		$__vars['alternateSrc2x'] = $__vars['variations'][$__vars['alternateVariation']]['2'];
		$__finalCompiled .= '

			';
		if (($__vars['alternateSrc'] !== $__vars['src']) OR ($__vars['alternateSrc2x'] !== $__vars['src2x'])) {
			$__finalCompiled .= '
				<source srcset="' . $__templater->escape($__vars['alternateSrc']) . ($__vars['alternateSrc2x'] ? ((', ' . $__templater->escape($__vars['alternateSrc2x'])) . ' 2x') : '') . '" media="(prefers-color-scheme: ' . $__templater->escape($__templater->method($__vars['xf']['style'], 'getAlternateStyleType', array())) . ')" />
			';
		}
		$__finalCompiled .= '
		';
	}
	$__finalCompiled .= '

		<img src="' . $__templater->escape($__vars['src']) . '" ' . ($__vars['src2x'] ? (('srcset="' . $__templater->escape($__vars['src2x'])) . ' 2x"') : '') . ' width="' . $__templater->escape($__vars['width']) . '" height="' . $__templater->escape($__vars['height']) . '" alt="' . $__templater->filter($__vars['alt'], array(array('for_attr', array()),), true) . '" />
	</picture>
';
	return $__finalCompiled;
}
),
'variation_menu' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'style' => '!',
		'routeType' => 'public',
		'route' => 'misc/style-variation',
		'selectedVariation' => $__vars['xf']['visitor']['style_variation'],
		'live' => false,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	if ($__templater->method($__vars['style'], 'hasAlternateStyleTypeVariation', array())) {
		$__finalCompiled .= '
		' . $__templater->callMacro(null, 'variation_menu_item', array(
			'routeType' => $__vars['routeType'],
			'route' => $__vars['route'],
			'variation' => '',
			'label' => 'System',
			'selectedVariation' => $__vars['selectedVariation'],
			'live' => $__vars['live'],
			'redirect' => $__vars['redirect'],
		), $__vars) . '

		' . $__templater->callMacro(null, 'variation_menu_item', array(
			'routeType' => $__vars['routeType'],
			'route' => $__vars['route'],
			'variation' => $__templater->method($__vars['style'], 'getStyleTypeVariation', array('light', )),
			'icon' => $__templater->method($__vars['style'], 'getVariationIcon', array($__templater->method($__vars['style'], 'getStyleTypeVariation', array('light', )), )),
			'label' => 'Light',
			'selectedVariation' => $__vars['selectedVariation'],
			'live' => $__vars['live'],
			'redirect' => $__vars['redirect'],
		), $__vars) . '

		' . $__templater->callMacro(null, 'variation_menu_item', array(
			'routeType' => $__vars['routeType'],
			'route' => $__vars['route'],
			'variation' => $__templater->method($__vars['style'], 'getStyleTypeVariation', array('dark', )),
			'icon' => $__templater->method($__vars['style'], 'getVariationIcon', array($__templater->method($__vars['style'], 'getStyleTypeVariation', array('dark', )), )),
			'label' => 'Dark',
			'selectedVariation' => $__vars['selectedVariation'],
			'live' => $__vars['live'],
			'redirect' => $__vars['redirect'],
		), $__vars) . '
	';
	} else {
		$__finalCompiled .= '
		' . $__templater->callMacro(null, 'variation_menu_item', array(
			'routeType' => $__vars['routeType'],
			'route' => $__vars['route'],
			'variation' => '',
			'selectedVariation' => $__vars['selectedVariation'],
			'live' => $__vars['live'],
			'redirect' => $__vars['redirect'],
		), $__vars) . '
	';
	}
	$__finalCompiled .= '

	';
	$__compilerTemp1 = $__templater->method($__vars['style'], 'getVariations', array(false, ));
	if ($__templater->isTraversable($__compilerTemp1)) {
		foreach ($__compilerTemp1 AS $__vars['variation']) {
			$__finalCompiled .= '
		';
			if ($__vars['variation'] !== $__templater->method($__vars['style'], 'getAlternateStyleTypeVariation', array())) {
				$__finalCompiled .= '
			' . $__templater->callMacro(null, 'variation_menu_item', array(
					'routeType' => $__vars['routeType'],
					'route' => $__vars['route'],
					'variation' => $__vars['variation'],
					'selectedVariation' => $__vars['selectedVariation'],
					'live' => $__vars['live'],
					'redirect' => $__vars['redirect'],
				), $__vars) . '
		';
			}
			$__finalCompiled .= '
	';
		}
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
),
'variation_menu_item' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'routeType' => 'public',
		'route' => $__templater->func('link', array('misc/style-variation', ), false),
		'variation' => '!',
		'icon' => 'fa-adjust',
		'label' => '',
		'selectedVariation' => $__vars['xf']['visitor']['style_variation'],
		'live' => false,
		'redirect' => null,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	<a href="' . $__templater->func('link_type', array($__vars['routeType'], $__vars['route'], null, array('variation' => $__vars['variation'], 'reset' => ($__vars['variation'] ? null : true), 't' => $__templater->func('csrf_token', array(), false), '_xfRedirect' => $__vars['redirect'], ), ), true) . '"
		class="menu-linkRow ' . (($__vars['variation'] === $__vars['selectedVariation']) ? 'is-selected' : '') . '"
		rel="nofollow"
		data-xf-click="' . ($__vars['live'] ? 'style-variation' : '') . '" data-variation="' . $__templater->escape($__vars['variation']) . '">

		' . $__templater->fontAwesome($__templater->escape($__vars['icon']), array(
	)) . '

		';
	if ($__vars['label']) {
		$__finalCompiled .= '
			' . $__templater->escape($__vars['label']) . '
		';
	} else if ($__vars['variation']) {
		$__finalCompiled .= '
			' . $__templater->func('phrase_dynamic', array('style_variation.' . $__vars['variation'], ), true) . '
		';
	} else {
		$__finalCompiled .= '
			' . 'Default' . '
		';
	}
	$__finalCompiled .= '
	</a>
';
	return $__finalCompiled;
}
),
'variation_input' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'style' => '!',
		'name' => 'user[style_variation]',
		'value' => $__vars['xf']['visitor']['style_variation'],
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	if ($__templater->method($__vars['style'], 'isVariationsEnabled', array())) {
		$__finalCompiled .= '
		';
		$__compilerTemp1 = array();
		if ($__templater->method($__vars['style'], 'hasAlternateStyleTypeVariation', array())) {
			$__compilerTemp1[] = array(
				'value' => '',
				'label' => 'System',
				'_type' => 'option',
			);
			$__compilerTemp1[] = array(
				'value' => $__templater->method($__vars['style'], 'getStyleTypeVariation', array('light', )),
				'label' => 'Light',
				'_type' => 'option',
			);
			$__compilerTemp1[] = array(
				'value' => $__templater->method($__vars['style'], 'getStyleTypeVariation', array('dark', )),
				'label' => 'Dark',
				'_type' => 'option',
			);
		} else {
			$__compilerTemp1[] = array(
				'value' => '',
				'label' => 'Default',
				'_type' => 'option',
			);
		}
		$__compilerTemp2 = $__templater->method($__vars['style'], 'getVariations', array(false, ));
		if ($__templater->isTraversable($__compilerTemp2)) {
			foreach ($__compilerTemp2 AS $__vars['variation']) {
				if ($__vars['variation'] !== $__templater->method($__vars['style'], 'getAlternateStyleTypeVariation', array())) {
					$__compilerTemp1[] = array(
						'value' => $__vars['variation'],
						'label' => $__templater->func('phrase_dynamic', array('style_variation.' . $__vars['variation'], ), true),
						'_type' => 'option',
					);
				}
			}
		}
		$__finalCompiled .= $__templater->formSelectRow(array(
			'name' => $__vars['name'],
			'value' => $__vars['value'],
		), $__compilerTemp1, array(
			'label' => 'Style variation',
		)) . '
	';
	} else {
		$__finalCompiled .= '
		' . $__templater->formHiddenVal('user[style_variation]', $__vars['value'], array(
		)) . '
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . '

' . '

' . '

' . '

';
	return $__finalCompiled;
}
);