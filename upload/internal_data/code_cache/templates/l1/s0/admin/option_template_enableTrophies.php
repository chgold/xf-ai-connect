<?php
// FROM HASH: 955b1976089042e5c7bcc11e54b2e039
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => $__vars['inputName'],
		'selected' => $__vars['option']['option_value'],
		'class' => 'js-enableTrophies',
		'label' => $__templater->escape($__vars['option']['title']),
		'_type' => 'option',
	)), array(
		'hint' => $__templater->escape($__vars['hintHtml']),
		'explain' => $__templater->escape($__vars['explainHtml']),
		'html' => $__templater->escape($__vars['listedHtml']),
		'rowclass' => $__vars['rowClass'],
	)) . '

';
	$__templater->inlineJs('
	processCheckBox()

	const enable = document.querySelector(\'.js-enableTrophies\', \'click\', () =>
	{
		processCheckBox()
	})

	function processCheckBox ()
	{
		const userTitle = document.querySelector(\'input[name="options[userTitleLadderField]"]:checked\'),
			trophyOption = document.querySelector(\'.js-trophy_points\'),
			messageOption = document.querySelector(\'.js-messages\'),
			enableTrophies = document.querySelector(\'.js-enableTrophies\')

		if (enableTrophies.checked)
		{
			trophyOption.removeAttribute(\'disabled\')
		}
		else
		{
			trophyOption.setAttribute(\'disabled\', true)

			if (userTitle && userTitle.value === \'trophy_points\')
			{
				messageOption.checked = true
			}
		}
	}
', true);
	return $__finalCompiled;
}
);