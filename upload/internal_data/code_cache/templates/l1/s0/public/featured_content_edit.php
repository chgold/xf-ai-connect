<?php
// FROM HASH: b42bc04b179a4a82e95f86c77aacdbbf
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Manage content feature');
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
					';
	if ($__vars['showVisibilityToggle']) {
		$__compilerTemp2 .= '
						' . $__templater->formCheckBoxRow(array(
		), array(array(
			'name' => 'always_visible',
			'checked' => $__vars['feature']['always_visible'],
			'label' => 'Always visible',
			'hint' => 'If selected, the feature will be visible even if the member does not have permission to view the content.',
			'_type' => 'option',
		)), array(
		)) . '
					';
	}
	$__compilerTemp2 .= '
				';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__compilerTemp1 .= '
			<div class="block-body">
				' . $__compilerTemp2 . '
			</div>
		';
	}
	$__compilerTemp3 = '';
	if ($__vars['feature']['image_date']) {
		$__compilerTemp3 .= '
					' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'name' => 'delete_image',
			'label' => 'Delete current image',
			'_type' => 'option',
		))) . '
				';
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		' . $__compilerTemp1 . '

		<h3 class="block-formSectionHeader">
			<span class="collapseTrigger collapseTrigger--block ' . ($__templater->method($__vars['feature'], 'isCustomized', array()) ? 'is-active' : '') . '"
				data-xf-click="toggle" data-target="< :up:next">

				<span class="block-formSectionHeader-aligner">' . 'Customize' . '</span>
			</span>
		</h3>

		<div class="block-body block-body--collapsible ' . ($__templater->method($__vars['feature'], 'isCustomized', array()) ? 'is-active' : '') . '">
			' . $__templater->formRow('
				' . 'If you leave these values blank, the feature will automatically derive them from the content.' . '
			', array(
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['feature']['title_'],
		'maxlength' => $__templater->func('max_length', array('XF:FeaturedContent', 'title', ), false),
	), array(
		'label' => 'Feature title',
	)) . '

			' . $__templater->formTextAreaRow(array(
		'name' => 'snippet',
		'value' => $__vars['feature']['snippet_'],
		'rows' => '2',
		'autosize' => 'true',
		'maxlength' => $__templater->func('max_length', array('XF:FeaturedContent', 'snippet', ), false),
	), array(
		'label' => 'Feature snippet',
	)) . '

			' . $__templater->formRow('
				' . $__templater->formUpload(array(
		'name' => 'image',
		'accept' => '.gif,.jpeg,.jpg,.jpe,.png,.webp',
	)) . '

				<div class="formRow-explain">
					' . 'It is recommended that you use an image that is at least ' . 640 . 'x' . 640 . ' pixels.' . '
				</div>

				' . $__compilerTemp3 . '
			', array(
		'label' => 'Feature image',
	)) . '

			' . $__templater->formDateTimeInputRow(array(
		'name' => 'date',
		'value' => $__vars['feature']['feature_date'],
	), array(
		'label' => 'Featured date',
		'explain' => 'Featured content is ordered by feature date, from newest to oldest.',
	)) . '
		</div>

		' . $__templater->func('redirect_input', array(null, null, true)) . '

		' . $__templater->formSubmitRow(array(
		'icon' => 'save',
	), array(
		'html' => '
				' . $__templater->button('Unfeature' . '...', array(
		'href' => $__vars['deleteUrl'],
		'icon' => 'delete',
		'overlay' => 'true',
	), '', array(
	)) . '
			',
	)) . '
	</div>
', array(
		'action' => $__vars['confirmUrl'],
		'class' => 'block',
		'ajax' => 'true',
	));
	return $__finalCompiled;
}
);