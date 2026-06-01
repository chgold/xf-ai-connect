<?php
// FROM HASH: 0cd68afe2a0244ab67fd1c5ab0462e42
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Feature content');
	$__finalCompiled .= '

';
	$__templater->breadcrumbs($__vars['breadcrumbs']);
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__vars['showVisibilityToggle']) {
		$__compilerTemp1 .= '
				' . $__templater->formCheckBoxRow(array(
		), array(array(
			'name' => 'always_visible',
			'label' => 'Always visible',
			'hint' => 'If selected, the feature will be visible even if the member does not have permission to view the content.',
			'_type' => 'option',
		)), array(
		)) . '
			';
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->formInfoRow('
				' . 'Are you sure you want to feature this content?' . '
			', array(
		'rowtype' => 'confirm',
	)) . '

			' . $__compilerTemp1 . '
		</div>

		<h3 class="block-formSectionHeader">
			<span class="collapseTrigger collapseTrigger--block"
				data-xf-click="toggle" data-target="< :up:next">

				<span class="block-formSectionHeader-aligner">' . 'Customize' . '</span>
			</span>
		</h3>

		<div class="block-body block-body--collapsible">
			' . $__templater->formRow('
				' . 'If you leave these values blank, the feature will automatically derive them from the content.' . '
			', array(
	)) . '

			' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'maxlength' => $__templater->func('max_length', array('XF:FeaturedContent', 'title', ), false),
	), array(
		'label' => 'Feature title',
	)) . '

			' . $__templater->formTextAreaRow(array(
		'name' => 'snippet',
		'rows' => '2',
		'autosize' => 'true',
		'maxlength' => $__templater->func('max_length', array('XF:FeaturedContent', 'snippet', ), false),
	), array(
		'label' => 'Feature snippet',
	)) . '

			' . $__templater->formUploadRow(array(
		'name' => 'image',
		'accept' => '.gif,.jpeg,.jpg,.jpe,.png',
	), array(
		'label' => 'Feature image',
		'explain' => 'It is recommended that you use an image that is at least ' . 640 . 'x' . 640 . ' pixels.',
	)) . '
		</div>

		' . $__templater->func('redirect_input', array(null, null, true)) . '

		' . $__templater->formSubmitRow(array(
		'fa' => 'fa-award',
		'submit' => 'Feature',
	), array(
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