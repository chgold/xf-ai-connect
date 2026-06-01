<?php
// FROM HASH: faec5c1136a36b2090f3247458cceccb
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__vars['canUseIndexNow']) {
		$__finalCompiled .= '
	' . $__templater->formCheckBoxRow(array(
		), array(array(
			'name' => $__vars['inputName'] . '[enabled]',
			'selected' => $__vars['option']['option_value']['enabled'],
			'label' => $__templater->escape($__vars['option']['title']),
			'_type' => 'option',
		)), array(
			'hint' => $__templater->escape($__vars['hintHtml']),
			'explain' => $__templater->escape($__vars['explainHtml']),
			'html' => $__templater->escape($__vars['listedHtml']),
			'rowclass' => $__vars['rowClass'],
		)) . '
';
	} else {
		$__finalCompiled .= '
	' . $__templater->formRow('

		<div class="block-rowMessage block-rowMessage--error">' . $__templater->escape($__vars['error']) . '</div>
	', array(
			'label' => $__templater->escape($__vars['option']['title']),
			'hint' => $__templater->escape($__vars['hintHtml']),
			'explain' => $__templater->escape($__vars['explainHtml']),
			'rowclass' => $__vars['rowClass'],
		)) . '
';
	}
	return $__finalCompiled;
}
);