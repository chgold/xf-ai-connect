<?php
// FROM HASH: 98a040c2236ac5f22279d63dc3937817
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Attachment browser');
	$__finalCompiled .= '

';
	$__templater->inlineCss('
	.attachment-tooltip-image
	{
		display: block;
	}
');
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if (!$__templater->test($__vars['attachments'], 'empty', array())) {
		$__compilerTemp1 .= '
			<div class="block-body">
				';
		$__compilerTemp2 = '';
		if ($__templater->isTraversable($__vars['attachments'])) {
			foreach ($__vars['attachments'] AS $__vars['attachment']) {
				$__compilerTemp2 .= '
						';
				$__compilerTemp3 = array(array(
					'name' => 'attachment_ids[]',
					'value' => $__vars['attachment']['attachment_id'],
					'_type' => 'toggle',
					'html' => '',
				));
				if ($__vars['attachment']['has_thumbnail']) {
					$__compilerTemp3[] = array(
						'class' => 'dataList-cell--attachment',
						'href' => $__templater->func('link', array('attachments/view', $__vars['attachment'], ), false),
						'style' => 'background-image: url(' . $__vars['attachment']['thumbnail_url'] . ')',
						'alt' => $__vars['attachment']['filename'],
						'data-xf-init' => 'element-tooltip',
						'data-element' => '| .tooltip-element',
						'_type' => 'cell',
						'html' => '
									<span class="tooltip-element"><img src="' . $__templater->escape($__vars['attachment']['thumbnail_url']) . '" class="attachment-tooltip-image" /></span>
								',
					);
				} else {
					$__compilerTemp3[] = array(
						'class' => 'dataList-cell--attachment',
						'href' => $__templater->func('link', array('attachments/view', $__vars['attachment'], ), false),
						'_type' => 'cell',
						'html' => '
									' . $__templater->fontAwesome('fa-file fa-2x', array(
					)) . '
								',
					);
				}
				$__compilerTemp3[] = array(
					'href' => $__templater->func('link', array('attachments/view', $__vars['attachment'], ), false),
					'class' => 'dataList-cell--main',
					'_type' => 'cell',
					'html' => '
								<div class="dataList-mainRow">' . $__templater->escape($__vars['attachment']['filename']) . '</div>
								<div class="dataList-subRow">
									<ul class="listInline listInline--bullet">
										<li>' . $__templater->func('date_dynamic', array($__vars['attachment']['Data']['upload_date'], array(
				))) . '</li>
										<li>' . ($__templater->escape($__vars['attachment']['Data']['User']['username']) ?: 'Unknown user') . '</li>
										<li>' . $__templater->filter($__vars['attachment']['file_size'], array(array('file_size', array()),), true) . '</li>
									</ul>
								</div>
							',
				);
				$__compilerTemp4 = '';
				if (!$__vars['attachment']['unassociated']) {
					$__compilerTemp4 .= '
									' . 'View host content' . '
									<span class="dataList-secondRow">' . $__templater->filter($__templater->method($__vars['attachment'], 'getContentTypePhrase', array()), array(array('parens', array()),), true) . '</span>
								';
				} else {
					$__compilerTemp4 .= '
									' . 'Unassociated' . '
								';
				}
				$__compilerTemp3[] = array(
					'href' => ((((!$__vars['attachment']['unassociated']) AND $__templater->method($__vars['attachment'], 'getContainerLink', array()))) ? $__templater->method($__vars['attachment'], 'getContainerLink', array()) : $__templater->func('link', array('attachments/view', $__vars['attachment'], ), false)),
					'target' => ((!$__vars['attachment']['unassociated']) ? '_blank' : ''),
					'class' => 'dataList-cell--action',
					'_type' => 'cell',
					'html' => '
								' . $__compilerTemp4 . '
							',
				);
				$__compilerTemp3[] = array(
					'href' => $__templater->func('link', array('attachments/delete', $__vars['attachment'], $__vars['linkParams'], ), false),
					'_type' => 'delete',
					'html' => '',
				);
				$__compilerTemp2 .= $__templater->dataRow(array(
				), $__compilerTemp3) . '
					';
			}
		}
		$__compilerTemp1 .= $__templater->dataList('
					' . $__compilerTemp2 . '
				', array(
		)) . '
			</div>
			<div class="block-footer block-footer--split">
				<span class="block-footer-counter">' . $__templater->func('display_totals', array($__vars['attachments'], $__vars['total'], ), true) . '</span>
				<span class="block-footer-select">' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'check-all' => '< .block-container',
			'label' => 'Select all',
			'_type' => 'option',
		))) . '</span>
				<span class="block-footer-controls">
					' . $__templater->button('', array(
			'type' => 'submit',
			'name' => 'delete_attachments',
			'icon' => 'delete',
		), '', array(
		)) . '
				</span>
			</div>
		';
	} else {
		$__compilerTemp1 .= '
			<div class="block-body block-row">' . 'No results found.' . '</div>
		';
	}
	$__finalCompiled .= $__templater->form('

	<div class="block-container">
		' . $__templater->callMacro(null, 'filter_macros::filter_bar', array(
		'route' => 'attachments',
		'content' => null,
		'params' => $__vars['linkParams'],
		'displayValues' => $__vars['filterDisplay'],
		'phrases' => array('key:content_type' => 'Content type' . $__vars['xf']['language']['label_separator'], 'key:username' => 'Username' . $__vars['xf']['language']['label_separator'], 'key:start' => 'Start date' . $__vars['xf']['language']['label_separator'], 'key:end' => 'End date' . $__vars['xf']['language']['label_separator'], 'key:order' => 'Sort by' . $__vars['xf']['language']['label_separator'], 'val:file_size_desc' => 'Largest', 'val:file_size_asc' => 'Smallest', 'val:attach_date_desc' => 'Most recent', 'val:attach_date_asc' => 'Least recent', ),
		'menu' => 'attachments/filter',
		'menuTitle' => 'Filter',
	), $__vars) . '

		' . $__compilerTemp1 . '
	</div>
	' . $__templater->func('page_nav', array(array(
		'page' => $__vars['page'],
		'total' => $__vars['total'],
		'link' => 'attachments',
		'params' => $__vars['linkParams'],
		'wrapperclass' => 'block-outer block-outer--after',
		'perPage' => $__vars['perPage'],
	))) . '

', array(
		'action' => $__templater->func('link', array('attachments', ), false),
		'class' => 'block',
		'ajax' => 'true',
		'data-xf-init' => 'select-plus',
		'data-sp-checkbox' => 'input[name=\'attachment_ids[]\']',
		'data-sp-container' => '.dataList-row',
	));
	return $__finalCompiled;
}
);