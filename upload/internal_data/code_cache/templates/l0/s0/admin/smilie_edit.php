<?php
// FROM HASH: 829736351bcebe604948b4730a220ebc
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['smilie'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add smilie');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit smilie' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['smilie']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['smilie'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('smilies/delete', $__vars['smilie'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
');
	}
	$__finalCompiled .= '

';
	$__compilerTemp1 = array(array(
		'value' => '0',
		'label' => $__vars['xf']['language']['parenthesis_open'] . 'None' . $__vars['xf']['language']['parenthesis_close'],
		'_type' => 'option',
	));
	$__compilerTemp1 = $__templater->mergeChoiceOptions($__compilerTemp1, $__vars['smilieCategories']);
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">

			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['smilie'],
	), array(
		'label' => 'Title',
	)) . '

			' . $__templater->formTextAreaRow(array(
		'name' => 'smilie_text',
		'value' => $__vars['smilie'],
		'autosize' => 'true',
	), array(
		'label' => 'Text to replace',
		'explain' => 'You may enter multiple text values to replace by putting them on separate lines.',
	)) . '

			<hr class="formRowSep" />

			' . $__templater->formTextBoxRow(array(
		'name' => 'emoji_shortname',
		'value' => $__vars['smilie']['emoji'],
		'dir' => 'ltr',
		'autocomplete' => 'off',
		'maxlength' => $__templater->func('max_length', array($__vars['smilie'], 'emoji_shortname', ), false),
		'data-xf-init' => 'emoji-completer',
		'data-exclude-smilies' => 'true',
		'data-insert-emoji' => 'true',
	), array(
		'label' => 'Emoji replacement',
		'hint' => 'Optional',
		'explain' => 'Enter an emoji or emoji short-name (such as :smile:) which will replace the smilie text. If an image replacement is also entered, the emoji will be used as in places where image replacements are not supported (such as emails).',
	)) . '

			' . $__templater->formAssetUploadRow(array(
		'name' => 'image_url',
		'value' => $__vars['smilie'],
		'asset' => 'smilies',
	), array(
		'label' => 'Image replacement URL',
		'hint' => 'Optional',
	)) . '

			' . $__templater->formAssetUploadRow(array(
		'name' => 'image_url_2x',
		'value' => $__vars['smilie'],
		'asset' => 'smilies',
	), array(
		'hint' => 'Optional',
		'label' => '2x image replacement URL',
		'explain' => 'If provided, the 2x image will be automatically displayed instead of the image URL above on devices capable of displaying a higher pixel resolution.<br />
<br />
<strong>Note: This option has no effect with sprite mode enabled.</strong>',
	)) . '

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'sprite_mode',
		'selected' => $__vars['smilie']['sprite_mode'],
		'label' => 'Enable CSS sprite mode with the following parameters:',
		'data-hide' => 'true',
		'data-xf-init' => 'disabler',
		'data-container' => '.js-smilieSpriteOptions',
		'_type' => 'option',
	)), array(
		'label' => 'Sprite mode',
	)) . '

			<div class="js-smilieSpriteOptions">
				' . $__templater->formRow('

					<div class="inputGroup">
						' . $__templater->formNumberBox(array(
		'name' => 'sprite_params[w]',
		'value' => $__vars['smilie']['sprite_params']['w'],
		'min' => '1',
		'title' => 'Width',
		'data-xf-init' => 'tooltip',
	)) . '
						<span class="inputGroup-text">x</span>
						' . $__templater->formNumberBox(array(
		'name' => 'sprite_params[h]',
		'value' => $__vars['smilie']['sprite_params']['h'],
		'min' => '1',
		'title' => 'Height',
		'data-xf-init' => 'tooltip',
	)) . '
						<span class="inputGroup-text">' . 'Pixels' . '</span>
					</div>
				', array(
		'rowtype' => 'input',
		'label' => 'Sprite dimensions',
	)) . '

				' . $__templater->formRow('

					<div class="inputGroup">
						' . $__templater->formNumberBox(array(
		'name' => 'sprite_params[x]',
		'value' => $__vars['smilie']['sprite_params']['x'],
		'title' => 'Background position x',
		'data-xf-init' => 'tooltip',
	)) . '
						<span class="inputGroup-text">x</span>
						' . $__templater->formNumberBox(array(
		'name' => 'sprite_params[y]',
		'value' => $__vars['smilie']['sprite_params']['y'],
		'title' => 'Background position y',
		'data-xf-init' => 'tooltip',
	)) . '
						<span class="inputGroup-text">' . 'Pixels' . '</span>
					</div>
				', array(
		'rowtype' => 'input',
		'label' => 'Sprite position',
	)) . '

				' . $__templater->formTextBoxRow(array(
		'name' => 'sprite_params[bs]',
		'value' => $__vars['smilie']['sprite_params']['bs'],
		'dir' => 'ltr',
	), array(
		'label' => 'Background size',
		'explain' => 'If required, enter a value for the <code>background-size</code> CSS property for this sprite.',
	)) . '
			</div>

			<hr class="formRowSep" />

			' . $__templater->formSelectRow(array(
		'name' => 'smilie_category_id',
		'value' => $__vars['smilie'],
	), $__compilerTemp1, array(
		'label' => 'Smilie category',
	)) . '

			' . $__templater->callMacro(null, 'display_order_macros::row', array(
		'value' => $__vars['smilie'],
	), $__vars) . '

			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'display_in_editor',
		'selected' => $__vars['smilie']['display_in_editor'],
		'label' => 'Show this smilie in the text editor',
		'explain' => 'Hidden smilies are not shown as clickable items in the text editor, but are displayed on the smilie help page, and will still convert smilie text to a smilie image if typed manually into the editor.',
		'_type' => 'option',
	)), array(
	)) . '
		</div>

		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('smilies/save', $__vars['smilie'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);