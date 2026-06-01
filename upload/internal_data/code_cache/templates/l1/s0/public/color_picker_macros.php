<?php
// FROM HASH: d4fe86ee2a347c607f3b320f85672596
return array(
'macros' => array('color_picker' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'name' => '!',
		'value' => '',
		'mapVariation' => 'default',
		'mapName' => '',
		'allowPalette' => 'false',
		'label' => '',
		'hint' => '',
		'explain' => '',
		'html' => '',
		'row' => true,
		'rowClass' => '',
		'rowtype' => '',
		'colorData' => '',
		'required' => false,
		'controlId' => '',
		'includeScripts' => true,
		'showTxt' => false,
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

	';
	$__templater->includeCss('public:color_picker.less');
	$__finalCompiled .= '
	';
	$__templater->includeJs(array(
		'src' => 'xf/color_picker.js',
		'min' => '1',
	));
	$__finalCompiled .= '

	';
	if ($__vars['row'] AND (!$__vars['controlId'])) {
		$__finalCompiled .= '
		';
		$__vars['controlId'] = $__templater->func('unique_id', array(), false);
		$__finalCompiled .= '
	';
	}
	$__finalCompiled .= '

	';
	$__compilerTemp1 = '';
	if ($__vars['showTxt']) {
		$__compilerTemp1 .= '
				<div class="inputGroup-text inputGroup-text--hslTxt"><span class="js-hslTxt-h"></span></div>
				<div class="inputGroup-text inputGroup-text--hslTxt"><span class="js-hslTxt-s"></span></div>
				<div class="inputGroup-text inputGroup-text--hslTxt"><span class="js-hslTxt-l"></span></div>
			';
	}
	$__vars['picker_orig'] = $__templater->preEscaped('
		<div class="inputGroup inputGroup--joined inputGroup--color"
			data-xf-init="color-picker"
			data-allow-palette="' . $__templater->escape($__vars['allowPalette']) . '"
			data-palette-variation="' . $__templater->escape($__vars['mapVariation']) . '"
			data-palette-name="' . $__templater->escape($__vars['mapName']) . '">

			' . $__templater->formTextBox(array(
		'name' => $__vars['name'],
		'value' => $__vars['value'],
		'required' => $__vars['required'],
		'id' => $__vars['controlId'],
		'dir' => 'ltr',
	)) . '
			<div class="inputGroup-text"><span class="colorPickerBox js-colorPickerTrigger"></span></div>
			' . $__compilerTemp1 . '
		</div>
	');
	$__finalCompiled .= '

	';
	$__compilerTemp2 = '';
	if ($__vars['showTxt']) {
		$__compilerTemp2 .= '
				<div class="inputGroup-text inputGroup-text--hslTxt"><span class="js-hslTxt-h"></span></div>
				<div class="inputGroup-text inputGroup-text--hslTxt"><span class="js-hslTxt-s"></span></div>
				<div class="inputGroup-text inputGroup-text--hslTxt"><span class="js-hslTxt-l"></span></div>
			';
	}
	$__vars['picker'] = $__templater->preEscaped('
		<div class="inputGroup inputGroup---joined inputGroup--color ' . ($__vars['showTxt'] ? 'inputGroup--color--showTxt' : '') . '"
			data-xf-init="color-picker"
			data-allow-palette="' . $__templater->escape($__vars['allowPalette']) . '"
			data-palette-variation="' . $__templater->escape($__vars['mapVariation']) . '"
			data-palette-name="' . $__templater->escape($__vars['mapName']) . '">

			' . $__templater->formTextBox(array(
		'name' => $__vars['name'],
		'value' => $__vars['value'],
		'required' => $__vars['required'],
		'id' => $__vars['controlId'],
		'dir' => 'ltr',
	)) . '
			<div class="inputGroup-text"><span class="colorPickerBox js-colorPickerTrigger"></span></div>
			' . $__compilerTemp2 . '
		</div>
	');
	$__finalCompiled .= '

	';
	if ($__vars['row']) {
		$__finalCompiled .= '
		' . $__templater->formRow('

			' . $__templater->filter($__vars['picker'], array(array('raw', array()),), true) . '
		', array(
			'rowtype' => $__vars['rowtype'],
			'rowclass' => 'formRow--input ' . $__vars['rowClass'],
			'controlid' => $__vars['controlId'],
			'label' => $__templater->escape($__vars['label']),
			'hint' => $__templater->escape($__vars['hint']),
			'explain' => $__templater->escape($__vars['explain']),
			'html' => $__templater->escape($__vars['html']),
		)) . '
	';
	} else {
		$__finalCompiled .= '
		' . $__templater->filter($__vars['picker'], array(array('raw', array()),), true) . '
	';
	}
	$__finalCompiled .= '

	';
	if ($__vars['includeScripts']) {
		$__finalCompiled .= '
		' . $__templater->callMacro(null, 'color_picker_scripts', array(
			'colorData' => $__vars['colorData'],
		), $__vars) . '
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
),
'color_picker_scripts' => array(
'arguments' => function($__templater, array $__vars) { return array(
		'colorData' => '',
	); },
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__compilerTemp1 = '';
	if ($__vars['colorData']) {
		$__compilerTemp1 .= '
			<script class="js-colorPickerData" type="application/json">
				' . $__templater->filter($__vars['colorData'], array(array('json', array()),array('raw', array()),), true) . '
			</script>
		';
	}
	$__templater->setPageParam('head.' . 'js-colorPicker', $__templater->preEscaped('
		<script type="text/template" id="xfColorPickerMenuTemplate">
			' . $__templater->callMacro(null, 'color_picker_menu', array(), $__vars) . '
		</script>

		' . $__compilerTemp1 . '
	'));
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
),
'color_picker_menu' => array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	<div class="menu menu--colorPicker menu--medium" data-menu="menu" aria-hidden="true">
		<div class="menu-content colorPicker">
			<h4 class="menu-tabHeader tabs hScroller" data-xf-init="tabs h-scroller" role="tablist">
				<span class="hScroller-scroll">
					' . $__templater->func('mustache', array('#palette', '
						<a class="tabs-tab" role="tab" tabindex="0">' . $__templater->func('mustache', array('title', ), true) . '</a>
					')) . '

					<a class="tabs-tab" role="tab" tabindex="0">' . 'Picker' . '</a>
				</span>
			</h4>

			<ul class="tabPanes">
				' . $__templater->func('mustache', array('#palette', '
					<li role="tabpanel">
						<div class="colorPicker-palette">
							' . $__templater->func('mustache', array('#colors', '
								<div class="colorPicker-palette-color" data-name="' . $__templater->func('mustache', array('name', ), true) . '">
									<span class="colorPicker-palette-color-preview ' . $__templater->func('mustache', array('className', ), true) . '">
										<span class="color-sample" style="background: ' . $__templater->func('mustache', array('background', ), true) . ';"></span>
									</span>

									<div class="colorPicker-palette-color-meta">
										<div class="colorPicker-palette-color-title">' . $__templater->func('mustache', array('title', ), true) . '</div>
										<div class="colorPicker-palette-color-name">' . $__templater->func('mustache', array('name', ), true) . '</div>
									</div>
								</div>
							')) . '
						</div>
					</li>
				')) . '

				<li role="tabpanel">
					<div class="colorPicker-sliders" dir="ltr">
						<div class="colorPicker-sliders-gradient">
							<div class="colorPicker-sliders-gradient-grid">
								<div class="colorPicker-sliders-gradient-saturation"></div>
								<div class="colorPicker-sliders-gradient-value"></div>
								<span class="colorPicker-sliders-gradient-indicator"></span>
							</div>
						</div>

						<div class="colorPicker-sliders-hue">
							<div class="colorPicker-sliders-hue-bar">
								<span class="colorPicker-sliders-hue-indicator"></span>
							</div>
						</div>

						<div class="colorPicker-sliders-alpha">
							<div class="colorPicker-sliders-alpha-bar">
								<span class="colorPicker-sliders-alpha-indicator"></span>
							</div>
						</div>
					</div>
				</li>
			</ul>

			<div class="colorPicker-inputs">
				<div class="colorPicker-inputs-input">
					<input type="text" class="input colorPicker-input" dir="ltr">
				</div>

				<div class="colorPicker-inputs-save">
					<div class="colorPicker-preview">
						<span class="colorPicker-preview-original"></span>
						<span class="colorPicker-preview-current"></span>
					</div>

					<div class="colorPicker-buttons">
						<button class="button colorPicker-reset">
							<span class="button-text">' . 'Reset' . '</span>
						</button>

						<button class="button button--primary colorPicker-save">
							<span class="button-text">' . 'Update' . '</span>
						</button>
					</div>
				</div>
			</div>
		</div>
	</div>
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . '

';
	return $__finalCompiled;
}
);