<?php
// FROM HASH: dff9ee181780c534547e64c946c77af6
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Merge template changes' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->escape($__vars['template']['title']));
	$__finalCompiled .= '

';
	$__templater->includeCss('public:diff.less');
	$__finalCompiled .= '
';
	$__templater->includeJs(array(
		'src' => 'xf/form.js, xf/template_merge.js',
		'min' => '1',
	));
	$__finalCompiled .= '

';
	$__compilerTemp1 = '';
	if ($__templater->isTraversable($__vars['diffs'])) {
		foreach ($__vars['diffs'] AS $__vars['diff']) {
			$__compilerTemp1 .= '
						';
			$__vars['diffHtml'] = $__templater->filter($__vars['diff']['1'], array(array('escape', array()),array('join', array('<br />', )),), false);
			$__compilerTemp1 .= '
						';
			if ($__vars['diff']['0'] == 'c') {
				$__compilerTemp1 .= '
							<li class="diffList-conflict js-conflictContainer">
								';
				$__vars['parentHtml'] = $__templater->filter($__vars['diff']['3'], array(array('escape', array()),array('join', array('<br />', )),), false);
				$__compilerTemp1 .= '
								<div class="diffList-line js-diffParent">';
				$__compilerTemp2 = '';
				if ($__vars['diff']['3']) {
					$__compilerTemp2 .= '
										<span>' . (($__templater->func('trim', array($__vars['parentHtml'], ), false) !== '') ? $__templater->filter($__vars['parentHtml'], array(array('raw', array()),), true) : '&nbsp;') . '</span>' . $__templater->formHiddenVal('', $__templater->filter($__vars['diff']['3'], array(array('join', array('
', )),), false), array(
					)) . '
									';
				} else {
					$__compilerTemp2 .= '
										<i>' . 'Deleted' . '</i>
									';
				}
				$__compilerTemp1 .= $__templater->func('trim', array('
									' . $__compilerTemp2 . '
								'), false) . '</div>
								<div class="diffList-resolve">

									' . $__templater->button('
										&uarr; ' . 'Resolve using parent version' . '
									', array(
					'class' => 'js-resolveButton',
					'data-target' => '.js-diffParent',
				), '', array(
				)) . '
									' . $__templater->button('
										' . 'Resolve using both' . '
									', array(
					'class' => 'js-resolveButton',
					'data-target' => '.js-diffParent, .js-diffCustom',
				), '', array(
				)) . '
									' . $__templater->button('
										' . 'Resolve using custom version' . ' &darr;
									', array(
					'class' => 'js-resolveButton',
					'data-target' => '.js-diffCustom',
				), '', array(
				)) . '

									' . $__templater->formHiddenVal('merged[]', $__templater->filter($__vars['diff']['3'], array(array('join', array('
', )),), false), array(
					'class' => 'js-mergedInput',
				)) . '
								</div>
								<div class="diffList-line js-diffCustom">';
				$__compilerTemp3 = '';
				if ($__vars['diff']['1']) {
					$__compilerTemp3 .= '
										<span>' . (($__templater->func('trim', array($__vars['diffHtml'], ), false) !== '') ? $__templater->filter($__vars['diffHtml'], array(array('raw', array()),), true) : '&nbsp;') . '</span>' . $__templater->formHiddenVal('', $__templater->filter($__vars['diff']['1'], array(array('join', array('
', )),), false), array(
					)) . '
									';
				} else {
					$__compilerTemp3 .= '
										<i>' . 'Deleted' . '</i>
									';
				}
				$__compilerTemp1 .= $__templater->func('trim', array('
									' . $__compilerTemp3 . '
								'), false) . '</div>
							</li>
						';
			} else {
				$__compilerTemp1 .= '
							<li class="diffList-line diffList-line--' . $__templater->escape($__vars['diff']['0']) . '">';
				$__compilerTemp4 = '';
				if ($__vars['diff']['1']) {
					$__compilerTemp4 .= '
									<span>' . (($__templater->func('trim', array($__vars['diffHtml'], ), false) !== '') ? $__templater->filter($__vars['diffHtml'], array(array('raw', array()),), true) : '&nbsp;') . '</span>' . $__templater->formHiddenVal('merged[]', $__templater->filter($__vars['diff']['1'], array(array('join', array('
', )),), false), array(
					)) . '
								';
				} else {
					$__compilerTemp4 .= '
									<i>' . 'Deleted' . '</i>
								';
				}
				$__compilerTemp1 .= $__templater->func('trim', array('
								' . $__compilerTemp4 . '
							'), false) . '</li>
						';
			}
			$__compilerTemp1 .= '
					';
		}
	}
	$__finalCompiled .= $__templater->form('
	<div class="block-container">
		<div class="block-body">
			<div class="block-row block-row--separated">
				<div class="diffListContainer" dir="ltr">
					<ol class="diffList diffList--code diffList--wrapped diffList--editable">
					' . $__compilerTemp1 . '
					</ol>
				</div>
			</div>
			<div class="block-row block-row--separated">
				' . 'You may click on an update to edit it.' . '
			</div>
		</div>
		' . $__templater->formSubmitRow(array(
		'submit' => 'Merge',
		'icon' => 'save',
	), array(
		'rowtype' => 'simple',
	)) . '
	</div>
', array(
		'action' => $__templater->func('link', array('templates/merge-outdated', $__vars['template'], ), false),
		'data-xf-init' => 'template-merger',
		'class' => 'block',
	));
	return $__finalCompiled;
}
);