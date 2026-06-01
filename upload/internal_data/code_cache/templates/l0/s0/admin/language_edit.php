<?php
// FROM HASH: 158501b1782b0c20b2fb502bb0d9a41b
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['language'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add language');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit language' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['language']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['language'], 'isUpdate', array())) {
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	' . $__templater->button('', array(
			'href' => $__templater->func('link', array('languages/delete', $__vars['language'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
');
	}
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if (!$__templater->test($__vars['quickPhrases'], 'empty', array())) {
		$__compilerTemp1 .= '
					<a class="tabs-tab" role="tab" aria-controls="phrases">' . 'Commonly edited phrases' . ' </a>
				';
	}
	$__compilerTemp2 = array(array(
		'value' => '0',
		'label' => $__vars['xf']['language']['parenthesis_open'] . 'No parent' . $__vars['xf']['language']['parenthesis_close'],
		'_type' => 'option',
	));
	$__compilerTemp3 = $__templater->method($__vars['languageTree'], 'getFlattened', array(1, ));
	if ($__templater->isTraversable($__compilerTemp3)) {
		foreach ($__compilerTemp3 AS $__vars['treeEntry']) {
			$__compilerTemp2[] = array(
				'value' => $__vars['treeEntry']['record']['language_id'],
				'label' => $__templater->func('repeat', array('--', $__vars['treeEntry']['depth'], ), true) . ' ' . $__templater->escape($__vars['treeEntry']['record']['title']),
				'_type' => 'option',
			);
		}
	}
	$__compilerTemp4 = array(array(
		'value' => '',
		'label' => '&nbsp;',
		'_type' => 'option',
	));
	$__compilerTemp4 = $__templater->mergeChoiceOptions($__compilerTemp4, $__vars['locales']);
	$__compilerTemp5 = $__templater->mergeChoiceOptions(array(), $__vars['currencyFormats']);
	$__compilerTemp5[] = array(
		'value' => '',
		'selected' => !$__vars['currencyFormats'][$__vars['language']['currency_format']],
		'label' => 'Other',
		'_dependent' => array('
								' . $__templater->formTextBox(array(
		'name' => 'currency_format_other',
		'value' => ($__vars['currencyFormats'][$__vars['language']['currency_format']] ? '' : $__vars['language']['currency_format']),
		'size' => '15',
		'maxlength' => $__templater->func('max_length', array($__vars['language'], 'currency_format', ), false),
	)) . '
								<p class="formRow-explain">' . 'You may use {symbol} to be replaced with the given currency symbol and {value} to be replaced with the given value.' . '</p>
							'),
		'_type' => 'option',
	);
	$__compilerTemp6 = $__templater->mergeChoiceOptions(array(), $__vars['dateFormats']);
	$__compilerTemp6[] = array(
		'value' => '',
		'selected' => !$__vars['dateFormats'][$__vars['language']['date_format']],
		'label' => 'Other',
		'_dependent' => array('
								' . $__templater->formTextBox(array(
		'name' => 'date_format_other',
		'value' => ($__vars['dateFormats'][$__vars['language']['date_format']] ? '' : $__vars['language']['date_format']),
		'size' => '15',
		'maxlength' => $__templater->func('max_length', array($__vars['language'], 'date_format', ), false),
		'dir' => 'ltr',
	)) . '
								<p class="formRow-explain">' . 'Use PHP <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">date format</a>' . '</p>
							'),
		'_type' => 'option',
	);
	$__compilerTemp7 = $__templater->mergeChoiceOptions(array(), $__vars['timeFormats']);
	$__compilerTemp7[] = array(
		'value' => '',
		'selected' => !$__vars['timeFormats'][$__vars['language']['time_format']],
		'label' => 'Other',
		'_dependent' => array('
								' . $__templater->formTextBox(array(
		'name' => 'time_format_other',
		'value' => ($__vars['timeFormats'][$__vars['language']['time_format']] ? '' : $__vars['language']['time_format']),
		'size' => '15',
		'maxlength' => $__templater->func('max_length', array($__vars['language'], 'time_format', ), false),
		'dir' => 'ltr',
	)) . '
								<p class="formRow-explain">' . 'Use PHP <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">time format</a>' . '</p>
							'),
		'_type' => 'option',
	);
	$__compilerTemp8 = $__templater->mergeChoiceOptions(array(), $__vars['dateShortRecentFormats']);
	$__compilerTemp8[] = array(
		'value' => '',
		'selected' => !$__vars['dateShortRecentFormats'][$__vars['language']['date_short_recent_format']],
		'label' => 'Other',
		'_dependent' => array('
								' . $__templater->formTextBox(array(
		'name' => 'date_short_recent_format_other',
		'value' => ($__vars['dateShortRecentFormats'][$__vars['language']['date_short_recent_format']] ? '' : $__vars['language']['date_short_recent_format']),
		'size' => '15',
		'maxlength' => $__templater->func('max_length', array($__vars['language'], 'date_short_recent_format', ), false),
		'dir' => 'ltr',
	)) . '
								<p class="formRow-explain">' . 'Use PHP <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">date format</a>' . '</p>
							'),
		'_type' => 'option',
	);
	$__compilerTemp9 = $__templater->mergeChoiceOptions(array(), $__vars['dateShortFormats']);
	$__compilerTemp9[] = array(
		'value' => '',
		'selected' => !$__vars['dateShortFormats'][$__vars['language']['date_short_format']],
		'label' => 'Other',
		'_dependent' => array('
								' . $__templater->formTextBox(array(
		'name' => 'date_short_format_other',
		'value' => ($__vars['dateShortFormats'][$__vars['language']['date_short_format']] ? '' : $__vars['language']['date_short_format']),
		'size' => '15',
		'maxlength' => $__templater->func('max_length', array($__vars['language'], 'date_short_format', ), false),
		'dir' => 'ltr',
	)) . '
								<p class="formRow-explain">' . 'Use PHP <a href="https://www.php.net/manual/en/datetime.format.php" target="_blank">date format</a>' . '</p>
							'),
		'_type' => 'option',
	);
	$__compilerTemp10 = '';
	if (!$__templater->test($__vars['quickPhrases'], 'empty', array())) {
		$__compilerTemp10 .= '
				<li role="tabpanel" id="phrases" aria-expanded="false">
					<div class="block-body">

						' . $__templater->formTextAreaRow(array(
			'name' => 'quick_phrases[privacy_policy_text]',
			'value' => $__vars['quickPhrases']['privacy_policy_text']['phrase_text'],
			'autosize' => 'true',
			'class' => 'input--fitHeight--short',
		), array(
			'label' => 'Privacy policy',
			'hint' => 'You may use HTML',
		)) . '

						' . $__templater->formTextAreaRow(array(
			'name' => 'quick_phrases[terms_rules_text]',
			'value' => $__vars['quickPhrases']['terms_rules_text']['phrase_text'],
			'autosize' => 'true',
			'class' => 'input--fitHeight--short',
		), array(
			'label' => 'Terms and rules',
			'hint' => 'You may use HTML',
		)) . '

						' . $__templater->formTextAreaRow(array(
			'name' => 'quick_phrases[extra_copyright]',
			'value' => $__vars['quickPhrases']['extra_copyright']['phrase_text'],
			'autosize' => 'true',
			'class' => 'input--fitHeight--short',
		), array(
			'label' => 'Extra footer copyright',
			'hint' => 'You may use HTML',
		)) . '

						' . $__templater->formTextAreaRow(array(
			'name' => 'quick_phrases[email_footer_html]',
			'value' => $__vars['quickPhrases']['email_footer_html']['phrase_text'],
			'autosize' => 'true',
			'class' => 'input--fitHeight--short',
		), array(
			'label' => 'Extra email footer (HTML)',
			'hint' => 'You may use HTML',
		)) . '

						' . $__templater->formTextAreaRow(array(
			'name' => 'quick_phrases[email_footer_text]',
			'value' => $__vars['quickPhrases']['email_footer_text']['phrase_text'],
			'autosize' => 'true',
			'class' => 'input--fitHeight--short',
		), array(
			'label' => 'Extra email footer (plain text)',
		)) . '

					</div>
				</li>
			';
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">

		<h2 class="block-tabHeader tabs hScroller" role="tablist"
			data-xf-init="tabs h-scroller"
			xdata-state="replace">

			<span class="hScroller-scroll">
				' . '
				<a class="tabs-tab is-active" role="tab" aria-controls="options">' . 'Options' . '</a>
				<a class="tabs-tab" role="tab" aria-controls="date-format">' . 'Date format' . '</a>
				<a class="tabs-tab" role="tab" aria-controls="short-date-format">' . 'Short date format' . '</a>
				' . $__compilerTemp1 . '
				' . '
			</span>
		</h2>

		<ul class="tabPanes">
			' . '

			<li class="is-active" role="tabpanel" id="options" aria-expanded="true">
				<div class="block-body">

					' . $__templater->formTextBoxRow(array(
		'name' => 'title',
		'value' => $__vars['language'],
	), array(
		'label' => 'Title',
	)) . '

					' . $__templater->formSelectRow(array(
		'name' => 'parent_id',
		'value' => $__vars['language'],
	), $__compilerTemp2, array(
		'label' => 'Parent language',
	)) . '

					' . $__templater->formSelectRow(array(
		'name' => 'language_code',
		'value' => $__vars['language'],
	), $__compilerTemp4, array(
		'label' => 'Locale',
	)) . '

					' . $__templater->formRadioRow(array(
		'name' => 'text_direction',
		'value' => $__vars['language'],
	), array(array(
		'value' => 'LTR',
		'label' => 'Left-to-right',
		'_type' => 'option',
	),
	array(
		'value' => 'RTL',
		'label' => 'Right-to-left',
		'_type' => 'option',
	)), array(
		'label' => 'Text direction',
	)) . '

					' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'user_selectable',
		'selected' => $__vars['language']['user_selectable'],
		'label' => '
							' . 'Allow user selection' . '
						',
		'_type' => 'option',
	)), array(
	)) . '

					<hr class="formRowSep" />

					' . $__templater->formTextBoxRow(array(
		'name' => 'decimal_point',
		'value' => $__vars['language'],
		'size' => '5',
		'class' => 'input--autoSize',
	), array(
		'label' => 'Decimal point character',
	)) . '

					' . $__templater->formTextBoxRow(array(
		'name' => 'thousands_separator',
		'value' => $__vars['language'],
		'size' => '5',
		'class' => 'input--autoSize',
	), array(
		'label' => 'Thousands separator',
	)) . '

					' . $__templater->formTextBoxRow(array(
		'name' => 'label_separator',
		'value' => $__vars['language'],
		'size' => '5',
		'class' => 'input--autoSize',
	), array(
		'label' => 'Label separator',
		'explain' => 'This is used to separate labels from their associated values.',
	)) . '

					' . $__templater->formTextBoxRow(array(
		'name' => 'comma_separator',
		'value' => $__vars['language'],
		'size' => '5',
		'class' => 'input--autoSize',
	), array(
		'label' => 'Comma separator',
		'explain' => 'This is used as a separator in lists of values. Include a trailing space if needed.',
	)) . '

					' . $__templater->formTextBoxRow(array(
		'name' => 'ellipsis',
		'value' => $__vars['language'],
		'size' => '5',
		'class' => 'input--autoSize',
	), array(
		'label' => 'Ellipsis character',
	)) . '

					' . $__templater->formRow('

						<div class="inputGroup">
							' . $__templater->formTextBox(array(
		'name' => 'parenthesis_open',
		'value' => $__vars['language']['parenthesis_open'],
		'size' => '5',
		'class' => 'input--autoSize',
		'maxlength' => $__templater->func('max_length', array($__vars['language'], 'parenthesis_open', ), false),
	)) . '
							<span class="inputGroup-splitter"></span>
							' . $__templater->formTextBox(array(
		'name' => 'parenthesis_close',
		'value' => $__vars['language']['parenthesis_close'],
		'size' => '5',
		'class' => 'input--autoSize',
		'maxlength' => $__templater->func('max_length', array($__vars['language'], 'parenthesis_close', ), false),
	)) . '
						</div>
					', array(
		'label' => 'Parentheses characters',
	)) . '

					<hr class="formRowSep" />

					' . $__templater->formRadioRow(array(
		'name' => 'currency_format',
		'value' => $__vars['language'],
	), $__compilerTemp5, array(
		'label' => 'Currency format',
	)) . '

				</div>
			</li>

			<li role="tabpanel" id="date-format" aria-expanded="false">
				<div class="block-body">

					' . $__templater->formSelectRow(array(
		'name' => 'week_start',
		'value' => $__vars['language'],
	), array(array(
		'value' => '0',
		'label' => 'Sunday',
		'_type' => 'option',
	),
	array(
		'value' => '1',
		'label' => 'Monday',
		'_type' => 'option',
	),
	array(
		'value' => '2',
		'label' => 'Tuesday',
		'_type' => 'option',
	),
	array(
		'value' => '3',
		'label' => 'Wednesday',
		'_type' => 'option',
	),
	array(
		'value' => '4',
		'label' => 'Thursday',
		'_type' => 'option',
	),
	array(
		'value' => '5',
		'label' => 'Friday',
		'_type' => 'option',
	),
	array(
		'value' => '6',
		'label' => 'Saturday',
		'_type' => 'option',
	)), array(
		'label' => 'Week start day',
	)) . '

					' . $__templater->formRadioRow(array(
		'name' => 'date_format',
		'value' => $__vars['language'],
	), $__compilerTemp6, array(
		'label' => 'Date format',
	)) . '

					<hr class="formRowSep" />

					' . $__templater->formRadioRow(array(
		'name' => 'time_format',
		'value' => $__vars['language'],
	), $__compilerTemp7, array(
		'label' => 'Time format',
	)) . '

				</div>
			</li>

			<li role="tabpanel" id="short-date-format" aria-expanded="false">
				<div class="block-body">

					' . $__templater->formTextBoxRow(array(
		'name' => 'quick_phrases[short_date_x_minutes]',
		'value' => $__vars['quickPhrases']['short_date_x_minutes']['phrase_text'],
	), array(
		'label' => 'Short date minutes format',
		'explain' => 'Used to display short dates less than 60 minutes ago. The number of minutes is available in the <code>{minutes}</code> placeholder. For example, <code>{minutes}m</code> would display 45m for a date occurring 45 minutes ago.',
	)) . '

					' . $__templater->formTextBoxRow(array(
		'name' => 'quick_phrases[short_date_x_hours]',
		'value' => $__vars['quickPhrases']['short_date_x_hours']['phrase_text'],
	), array(
		'label' => 'Short date hours format',
		'explain' => 'Used to display short dates greater than 60 minutes but less than 24 hours ago. The number of hours is available in the <code>{hours}</code> placeholder. For example, <code>{hours}h</code> would display 18h for a date occurring 18 hours ago.',
	)) . '

					' . $__templater->formTextBoxRow(array(
		'name' => 'quick_phrases[short_date_x_days]',
		'value' => $__vars['quickPhrases']['short_date_x_days']['phrase_text'],
	), array(
		'label' => 'Short date days format',
		'explain' => 'Used to display short dates greater than 24 hours but less than 30 days ago. The number of days is available in the <code>{days}</code> placeholder. For example, <code>{days}d</code> would display 21d for a date occurring 21 days ago.',
	)) . '

					' . $__templater->formRadioRow(array(
		'name' => 'date_short_recent_format',
		'value' => $__vars['language'],
	), $__compilerTemp8, array(
		'label' => 'Short date full format without year',
		'explain' => 'Used to display short dates greater than 30 days but less than one year ago.',
	)) . '

					' . $__templater->formRadioRow(array(
		'name' => 'date_short_format',
		'value' => $__vars['language'],
	), $__compilerTemp9, array(
		'label' => 'Short date full format with year',
		'explain' => 'Used to display short dates greater than one year ago.',
	)) . '

				</div>
			</li>

			' . $__compilerTemp10 . '

		</ul>

		' . $__templater->formSubmitRow(array(
		'sticky' => 'true',
		'icon' => 'save',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('languages/save', $__vars['language'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);