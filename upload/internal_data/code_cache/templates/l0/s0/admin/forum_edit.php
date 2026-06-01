<?php
// FROM HASH: d7daa8955a99a09375a0e36da79746c3
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if ($__templater->method($__vars['forum'], 'isInsert', array())) {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Add forum');
		$__finalCompiled .= '
';
	} else {
		$__finalCompiled .= '
	';
		$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Edit forum' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['node']['title']));
		$__finalCompiled .= '
';
	}
	$__finalCompiled .= '

';
	if ($__templater->method($__vars['forum'], 'isUpdate', array())) {
		$__compilerTemp1 = '';
		if ($__vars['canChangeForumType']) {
			$__compilerTemp1 .= '
			' . $__templater->button('Change type', array(
				'href' => $__templater->func('link', array('forums/change-type', $__vars['node'], ), false),
				'overlay' => 'true',
			), '', array(
			)) . '
		';
		}
		$__templater->pageParams['pageAction'] = $__templater->preEscaped('
	<div class="buttonGroup">
		' . $__compilerTemp1 . '
		' . $__templater->button('', array(
			'href' => $__templater->func('link', array('forums/delete', $__vars['node'], ), false),
			'icon' => 'delete',
			'overlay' => 'true',
		), '', array(
		)) . '
	</div>
');
	}
	$__finalCompiled .= '

';
	$__compilerTemp2 = '';
	if ($__vars['typeConfigTemplate']) {
		$__compilerTemp2 .= '
				' . $__templater->includeTemplate($__vars['typeConfigTemplate'], $__vars) . '
			';
	}
	$__compilerTemp3 = '';
	if (!$__templater->test($__vars['availableFields'], 'empty', array())) {
		$__compilerTemp3 .= '
				';
		$__compilerTemp4 = array();
		if ($__templater->isTraversable($__vars['availableFields'])) {
			foreach ($__vars['availableFields'] AS $__vars['fieldId'] => $__vars['field']) {
				$__compilerTemp4[] = array(
					'value' => $__vars['fieldId'],
					'label' => $__templater->escape($__vars['field']['title']),
					'labelclass' => ($__vars['field']['required'] ? 'u-appendAsterisk' : ''),
					'_type' => 'option',
				);
			}
		}
		$__compilerTemp3 .= $__templater->formCheckBoxRow(array(
			'name' => 'available_fields',
			'value' => $__vars['forum']['field_cache'],
			'listclass' => 'field listColumns',
		), $__compilerTemp4, array(
			'label' => 'Available fields',
			'explain' => '* Starred fields are required for new content when selected. Other fields are optional.',
			'hint' => '
						' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'check-all' => '.field.listColumns',
			'label' => 'Select all',
			'_type' => 'option',
		))) . '
					',
		)) . '
			';
	} else {
		$__compilerTemp3 .= '
				' . $__templater->formRow('
					' . $__templater->filter('None', array(array('parens', array()),), true) . '
					<a href="' . $__templater->func('link', array('custom-thread-fields/add', ), true) . '" target="_blank">' . 'Add field' . '</a>
				', array(
			'label' => 'Available fields',
		)) . '
			';
	}
	$__compilerTemp5 = '';
	if (!$__templater->test($__vars['prefixesGrouped'], 'empty', array())) {
		$__compilerTemp5 .= '

				';
		$__compilerTemp6 = array();
		if ($__templater->isTraversable($__vars['prefixGroups'])) {
			foreach ($__vars['prefixGroups'] AS $__vars['prefixGroupId'] => $__vars['prefixGroup']) {
				if ($__vars['prefixesGrouped'][$__vars['prefixGroupId']]) {
					$__compilerTemp6[] = array(
						'check-all' => 'true',
						'listclass' => 'listColumns',
						'label' => ($__vars['prefixGroupId'] ? $__vars['prefixGroup']['title'] : 'Ungrouped'),
						'_type' => 'optgroup',
						'options' => array(),
					);
					end($__compilerTemp6); $__compilerTemp7 = key($__compilerTemp6);
					if ($__templater->isTraversable($__vars['prefixesGrouped'][$__vars['prefixGroupId']])) {
						foreach ($__vars['prefixesGrouped'][$__vars['prefixGroupId']] AS $__vars['prefixId'] => $__vars['prefix']) {
							$__compilerTemp6[$__compilerTemp7]['options'][] = array(
								'value' => $__vars['prefixId'],
								'selected' => $__vars['forum']['prefix_cache'][$__vars['prefixId']],
								'label' => '<span class="label ' . $__templater->escape($__vars['prefix']['css_class']) . '">' . $__templater->escape($__vars['prefix']['title']) . '</span>',
								'_type' => 'option',
							);
						}
					}
				}
			}
		}
		$__compilerTemp5 .= $__templater->formCheckBoxRow(array(
			'name' => 'available_prefixes',
			'listclass' => 'prefix',
			'data-xf-init' => 'checkbox-select-disabler',
			'data-select' => '.js-availablePrefixSelect',
		), $__compilerTemp6, array(
			'rowtype' => 'explainOffset',
			'label' => 'Available prefixes',
			'explain' => 'Select all prefixes that should be available for use within this forum',
			'hint' => '
						' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'check-all' => '.prefix',
			'label' => 'Select all',
			'_type' => 'option',
		))) . '
					',
		)) . '

				';
		$__compilerTemp8 = array(array(
			'value' => '-1',
			'label' => 'None',
			'_type' => 'option',
		));
		if ($__templater->isTraversable($__vars['prefixGroups'])) {
			foreach ($__vars['prefixGroups'] AS $__vars['prefixGroupId'] => $__vars['prefixGroup']) {
				if (($__templater->func('count', array($__vars['prefixesGrouped'][$__vars['prefixGroupId']], ), false) > 0)) {
					$__compilerTemp8[] = array(
						'label' => (($__vars['prefixGroupId'] > 0) ? $__vars['prefixGroup']['title'] : 'Ungrouped'),
						'_type' => 'optgroup',
						'options' => array(),
					);
					end($__compilerTemp8); $__compilerTemp9 = key($__compilerTemp8);
					if ($__templater->isTraversable($__vars['prefixesGrouped'][$__vars['prefixGroupId']])) {
						foreach ($__vars['prefixesGrouped'][$__vars['prefixGroupId']] AS $__vars['prefixId'] => $__vars['prefix']) {
							$__compilerTemp8[$__compilerTemp9]['options'][] = array(
								'value' => $__vars['prefixId'],
								'disabled' => (!$__templater->func('in_array', array($__vars['prefixId'], $__vars['forum']['prefix_cache'], ), false)),
								'label' => $__templater->escape($__vars['prefix']['title']),
								'_type' => 'option',
							);
						}
					}
				}
			}
		}
		$__compilerTemp5 .= $__templater->formSelectRow(array(
			'name' => 'default_prefix_id',
			'value' => $__vars['forum'],
			'class' => 'js-availablePrefixSelect',
		), $__compilerTemp8, array(
			'label' => 'Default thread prefix',
			'explain' => 'You may specify a thread prefix to be automatically selected when visitors create new threads in this forum. The selected prefix <b>must</b> also be selected in the \'Available prefixes\' list above.',
		)) . '

				' . $__templater->formCheckBoxRow(array(
			'name' => 'require_prefix',
			'value' => $__vars['forum']['require_prefix'],
		), array(array(
			'value' => '1',
			'label' => 'Require users to select a prefix',
			'hint' => 'If selected, users will be required to select a prefix when creating a thread or updating its title. This will not be enforced for moderators or when moving a thread.',
			'_type' => 'option',
		)), array(
		)) . '

			';
	} else {
		$__compilerTemp5 .= '
				' . $__templater->formRow('
					' . $__templater->filter('None', array(array('parens', array()),), true) . ' <a href="' . $__templater->func('link', array('thread-prefixes/add', ), true) . '" target="_blank">' . 'Add prefix' . '</a>
				', array(
			'label' => 'Available prefixes',
		)) . '

				' . $__templater->formHiddenVal('default_thread_prefix', '0', array(
		)) . '
				' . $__templater->formHiddenVal('require_prefix', '0', array(
		)) . '
			';
	}
	$__compilerTemp10 = array();
	if ($__templater->isTraversable($__vars['userGroups'])) {
		foreach ($__vars['userGroups'] AS $__vars['userGroup']) {
			$__compilerTemp10[] = array(
				'value' => $__vars['userGroup']['user_group_id'],
				'label' => $__templater->escape($__vars['userGroup']['title']),
				'_type' => 'option',
			);
		}
	}
	$__compilerTemp11 = array();
	if ($__templater->isTraversable($__vars['sortOptions'])) {
		foreach ($__vars['sortOptions'] AS $__vars['sortKey'] => $__vars['null']) {
			$__compilerTemp11[] = array(
				'value' => $__vars['sortKey'],
				'label' => $__templater->func('phrase_dynamic', array('forum_sort.' . $__vars['sortKey'], ), true),
				'_type' => 'option',
			);
		}
	}
	$__compilerTemp12 = '';
	if (!$__templater->test($__vars['availablePrompts'], 'empty', array())) {
		$__compilerTemp12 .= '
				<hr class="formRowSep" />

				';
		$__compilerTemp13 = array();
		if ($__templater->isTraversable($__vars['promptGroups'])) {
			foreach ($__vars['promptGroups'] AS $__vars['promptGroupId'] => $__vars['promptGroup']) {
				if ($__vars['promptsGrouped'][$__vars['promptGroupId']]) {
					$__compilerTemp13[] = array(
						'check-all' => 'true',
						'listclass' => '_listColumns',
						'label' => ($__vars['promptGroupId'] ? $__vars['promptGroup']['title'] : 'Ungrouped'),
						'_type' => 'optgroup',
						'options' => array(),
					);
					end($__compilerTemp13); $__compilerTemp14 = key($__compilerTemp13);
					if ($__templater->isTraversable($__vars['promptsGrouped'][$__vars['promptGroupId']])) {
						foreach ($__vars['promptsGrouped'][$__vars['promptGroupId']] AS $__vars['promptId'] => $__vars['prompt']) {
							$__compilerTemp13[$__compilerTemp14]['options'][] = array(
								'value' => $__vars['promptId'],
								'selected' => $__vars['forum']['prompt_cache'][$__vars['promptId']],
								'label' => $__templater->escape($__vars['prompt']['title']),
								'_type' => 'option',
							);
						}
					}
				}
			}
		}
		$__compilerTemp12 .= $__templater->formCheckBoxRow(array(
			'name' => 'available_prompts',
			'listclass' => 'prompt',
		), $__compilerTemp13, array(
			'rowtype' => 'explainOffset',
			'label' => 'Available prompts',
			'explain' => 'Users will be prompted to post a new thread in this forum using one of the prompts selected here. The prompt appears in the thread title input box, before a title is entered. If no prompts are selected, the default prompt phrase (<a href="' . $__templater->func('link', array('phrases/edit-by-name', array(), array('title' => 'thread_prompt.default', ), ), true) . '"><code>thread_prompt.default</code></a>) is used.',
			'hint' => '
						' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'check-all' => '.prompt',
			'label' => 'Select all',
			'_type' => 'option',
		))) . '
					',
		)) . '

			';
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			' . $__templater->callMacro(null, 'node_edit_macros::title', array(
		'node' => $__vars['node'],
	), $__vars) . '
			' . $__templater->callMacro(null, 'node_edit_macros::description', array(
		'node' => $__vars['node'],
	), $__vars) . '

			<hr class="formRowSep" />
			' . $__templater->callMacro(null, 'node_edit_macros::node_name', array(
		'node' => $__vars['node'],
	), $__vars) . '
			' . $__templater->callMacro(null, 'node_edit_macros::position', array(
		'node' => $__vars['node'],
		'nodeTree' => $__vars['nodeTree'],
	), $__vars) . '
			' . $__templater->callMacro(null, 'node_edit_macros::navigation', array(
		'node' => $__vars['node'],
		'navChoices' => $__vars['navChoices'],
	), $__vars) . '
			<hr class="formRowSep" />

			' . $__templater->formRow('<span>' . $__templater->escape($__vars['forumTypeTitle']) . '</span>', array(
		'label' => 'Forum type',
		'explain' => $__templater->escape($__vars['forumTypeDesc']),
	)) . '
			' . $__compilerTemp2 . '
			' . $__templater->formHiddenVal('forum_type_id', $__vars['forumTypeId'], array(
	)) . '

			<hr class="formRowSep" />
			' . $__compilerTemp3 . '

			<hr class="formRowSep" />
			' . $__compilerTemp5 . '

		</div>

		<h3 class="block-formSectionHeader">
			<span class="collapseTrigger collapseTrigger--block" data-xf-click="toggle" data-target="< :up:next">
				<span class="block-formSectionHeader-aligner">' . 'Advanced options' . '</span>
			</span>
		</h3>
		<div class="block-body block-body--collapsible">
			' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'allow_posting',
		'selected' => $__vars['forum']['allow_posting'],
		'label' => 'Allow new messages to be posted in this forum',
		'hint' => 'If disabled, users will not be able to post new messages or edit or delete their own messages. Moderators will still be able to manage the contents of this forum.',
		'_type' => 'option',
	),
	array(
		'name' => 'moderate_threads',
		'selected' => $__vars['forum']['moderate_threads'],
		'label' => 'Moderate new threads posted in this forum',
		'hint' => 'If enabled, a moderator will have to manually approve threads posted in this forum.',
		'_type' => 'option',
	),
	array(
		'name' => 'moderate_replies',
		'selected' => $__vars['forum']['moderate_replies'],
		'label' => 'Moderate replies posted in this forum',
		'hint' => 'If enabled, a moderator will have to manually approve replies posted to threads in this forum.',
		'_type' => 'option',
	),
	array(
		'name' => 'count_messages',
		'selected' => $__vars['forum']['count_messages'],
		'label' => 'Count messages posted in this forum toward user total',
		'hint' => 'If disabled, messages posted (directly) in this forum will not contribute towards the posting user\'s total messages count. In addition, if this forum supports question threads, solutions will not contribute to a user\'s overall solution count.',
		'_type' => 'option',
	),
	array(
		'name' => 'auto_feature',
		'selected' => $__vars['forum']['auto_feature'],
		'label' => 'Automatically feature threads',
		'hint' => 'If enabled, threads posted in this forum will automatically be featured.',
		'_type' => 'option',
	),
	array(
		'name' => 'find_new',
		'selected' => $__vars['forum']['find_new'],
		'label' => 'Include threads from this forum when users click "New posts"',
		'hint' => 'If disabled, threads from this forum will never appear in the list of new / unread posts.',
		'_type' => 'option',
	)), array(
		'rowid' => 'advancedToggles',
	)) . '

			' . $__templater->formRadioRow(array(
		'name' => 'allow_index',
		'value' => $__vars['forum'],
	), array(array(
		'value' => 'allow',
		'label' => 'Index all threads',
		'_type' => 'option',
	),
	array(
		'value' => 'deny',
		'label' => 'Do not index any threads',
		'_type' => 'option',
	),
	array(
		'value' => 'criteria',
		'data-xf-init' => 'disabler',
		'data-container' => '.js-indexCriteria',
		'data-hide' => 'true',
		'label' => '
					' . 'Index threads based on criteria' . '
				',
		'_type' => 'option',
	)), array(
		'label' => 'Allow search engine indexing',
		'explain' => 'You can choose whether or not to allow external search engines to index all threads in this forum, or only threads meeting certain criteria.',
	)) . '

			<div class="js-indexCriteria">
				' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'index_criteria[max_days_post][enabled]',
		'selected' => $__vars['forum']['index_criteria']['max_days_post'],
		'label' => 'Thread was posted no more than X days ago' . $__vars['xf']['language']['label_separator'],
		'_dependent' => array($__templater->formNumberBox(array(
		'name' => 'index_criteria[max_days_post][value]',
		'value' => ($__vars['forum']['index_criteria']['max_days_post'] ?: 1),
		'min' => '1',
	))),
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[max_days_last_post][enabled]',
		'selected' => $__vars['forum']['index_criteria']['max_days_last_post'],
		'label' => 'Thread was replied to no more than X days ago' . $__vars['xf']['language']['label_separator'],
		'_dependent' => array($__templater->formNumberBox(array(
		'name' => 'index_criteria[max_days_last_post][value]',
		'value' => ($__vars['forum']['index_criteria']['max_days_last_post'] ?: 1),
		'min' => '1',
	))),
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[min_replies][enabled]',
		'selected' => $__vars['forum']['index_criteria']['min_replies'],
		'label' => 'Thread has at least X replies' . $__vars['xf']['language']['label_separator'],
		'_dependent' => array($__templater->formNumberBox(array(
		'name' => 'index_criteria[min_replies][value]',
		'value' => ($__vars['forum']['index_criteria']['min_replies'] ?: 1),
		'min' => '1',
	))),
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[min_reaction_score][enabled]',
		'selected' => ($__vars['forum']['index_criteria']['min_reaction_score'] !== null),
		'label' => 'Thread has at least X reaction score' . $__vars['xf']['language']['label_separator'],
		'_dependent' => array($__templater->formNumberBox(array(
		'name' => 'index_criteria[min_reaction_score][value]',
		'value' => ($__vars['forum']['index_criteria']['min_reaction_score'] ?: 0),
	))),
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[min_word_count][enabled]',
		'selected' => $__vars['forum']['index_criteria']['min_word_count'],
		'label' => 'Thread has at least X words in the first post',
		'_dependent' => array($__templater->formNumberBox(array(
		'name' => 'index_criteria[min_word_count][value]',
		'value' => ($__vars['forum']['index_criteria']['min_word_count'] ?: 1),
		'min' => '1',
	))),
		'_type' => 'option',
	)), array(
		'label' => 'Search engine index criteria',
		'explain' => 'Threads must match all of these criteria to be indexed by search engines. If no criteria are selected, all threads will be indexed.',
	)) . '

				' . $__templater->formCheckBoxRow(array(
	), array(array(
		'name' => 'index_criteria[is_sticky][enabled]',
		'selected' => $__vars['forum']['index_criteria']['is_sticky'],
		'label' => 'Thread is sticky',
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[is_article][enabled]',
		'selected' => $__vars['forum']['index_criteria']['is_article'],
		'label' => 'Thread is an article',
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[is_solved_question][enabled]',
		'selected' => $__vars['forum']['index_criteria']['is_solved_question'],
		'label' => 'Thread is a solved question',
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[first_post_staff][enabled]',
		'selected' => $__vars['forum']['index_criteria']['first_post_staff'],
		'label' => 'Thread was posted by a staff member',
		'_type' => 'option',
	),
	array(
		'name' => 'index_criteria[first_post_groups][enabled]',
		'selected' => ($__vars['forum']['index_criteria']['first_post_groups'] ? true : false),
		'label' => 'Thread was posted by a member of user group',
		'_dependent' => array($__templater->formSelect(array(
		'name' => 'index_criteria[first_post_groups][value]',
		'multiple' => 'true',
		'value' => $__vars['forum']['index_criteria']['first_post_groups'],
	), $__compilerTemp10)),
		'_type' => 'option',
	)), array(
		'label' => 'Search engine index exceptions',
		'explain' => 'Threads matching any of these criteria will always be indexed by search engines, regardless of whether or not they meet the criteria above.',
	)) . '
			</div>

			' . $__templater->formNumberBoxRow(array(
		'name' => 'min_tags',
		'value' => $__vars['forum'],
	), array(
		'label' => 'Minimum required tags',
		'explain' => 'This will require users to provide at least this many tags when creating a thread.',
	)) . '

			' . $__templater->formRadioRow(array(
		'name' => 'allowed_watch_notifications',
		'value' => $__vars['forum'],
	), array(array(
		'value' => 'all',
		'label' => 'New messages',
		'_type' => 'option',
	),
	array(
		'value' => 'thread',
		'label' => 'New threads',
		'_type' => 'option',
	),
	array(
		'value' => 'none',
		'label' => 'None',
		'_type' => 'option',
	)), array(
		'label' => 'Forum watch notification limit',
		'explain' => 'You can limit the amount of notifications that can be triggered by a user watching a forum here. For example, if you select "new threads", users will only be able to choose between no notifications or notifications when a new thread is posted. This can be used to limit the overhead of the forum watching system in busy forums.',
	)) . '

			' . $__templater->formRow('

				<div class="inputPair">
					' . $__templater->formSelect(array(
		'name' => 'default_sort_order',
		'value' => $__vars['forum']['default_sort_order'],
		'class' => 'input--inline',
	), $__compilerTemp11) . '
					' . $__templater->formSelect(array(
		'name' => 'default_sort_direction',
		'value' => $__vars['forum']['default_sort_direction'],
		'class' => 'input--inline',
	), array(array(
		'value' => 'desc',
		'label' => 'Descending',
		'_type' => 'option',
	),
	array(
		'value' => 'asc',
		'label' => 'Ascending',
		'_type' => 'option',
	))) . '
				</div>
			', array(
		'rowtype' => 'input',
		'label' => 'Default sort order',
	)) . '

			' . $__templater->formSelectRow(array(
		'name' => 'list_date_limit_days',
		'value' => $__vars['forum'],
	), array(array(
		'value' => '0',
		'label' => 'None',
		'_type' => 'option',
	),
	array(
		'value' => '7',
		'label' => '' . '7' . ' days',
		'_type' => 'option',
	),
	array(
		'value' => '14',
		'label' => '' . '14' . ' days',
		'_type' => 'option',
	),
	array(
		'value' => '30',
		'label' => '' . '30' . ' days',
		'_type' => 'option',
	),
	array(
		'value' => '60',
		'label' => '' . '2' . ' months',
		'_type' => 'option',
	),
	array(
		'value' => '90',
		'label' => '' . '3' . ' months',
		'_type' => 'option',
	),
	array(
		'value' => '182',
		'label' => '' . '6' . ' months',
		'_type' => 'option',
	),
	array(
		'value' => '365',
		'label' => '1 year',
		'_type' => 'option',
	)), array(
		'label' => 'Thread list date limit',
		'explain' => 'This can be used on busy forums to improve performance by only listing recently updated threads by default.',
	)) . '

			' . $__templater->callMacro(null, 'node_edit_macros::style', array(
		'node' => $__vars['node'],
		'styleTree' => $__vars['styleTree'],
	), $__vars) . '

			' . $__compilerTemp12 . '
		</div>

		' . $__templater->formSubmitRow(array(
		'icon' => 'save',
		'sticky' => 'true',
	), array(
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('forums/save', $__vars['node'], ), false),
		'ajax' => 'true',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);