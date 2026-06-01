<?php
// FROM HASH: 894ed980a546b45e6c47610e5ba851fa
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<li class="block-row block-row--separated js-inlineModContainer" data-author="' . ($__templater->escape($__vars['conversation']['Starter']['username']) ?: $__templater->escape($__vars['conversation']['username'])) . '">
	<div class="contentRow">
		<span class="contentRow-figure">
			' . $__templater->func('avatar', array($__vars['conversation']['Starter'], 's', false, array(
		'defaultname' => $__vars['conversation']['username'],
	))) . '
		</span>

		<div class="contentRow-main">
			<h3 class="contentRow-title">
				<a href="' . $__templater->func('link', array('direct-messages', $__vars['conversation'], ), true) . '">' . $__templater->func('highlight', array($__vars['conversation']['title'], $__vars['options']['term'], ), true) . '</a>
			</h3>

			<div class="contentRow-snippet">' . $__templater->func('snippet', array($__vars['conversation']['FirstMessage']['message'], 300, array('term' => $__vars['options']['term'], 'stripQuote' => true, ), ), true) . '</div>

			<div class="contentRow-minor contentRow-minor--hideLinks">
				<ul class="listInline listInline--bullet">
					';
	if ($__vars['options']['mod'] == 'conversation') {
		$__finalCompiled .= '
						<li>
							' . $__templater->formCheckBox(array(
			'standalone' => 'true',
		), array(array(
			'value' => $__vars['conversation']['conversation_id'],
			'class' => 'js-inlineModToggle',
			'data-xf-init' => 'tooltip',
			'title' => 'Select for moderation',
			'_type' => 'option',
		))) . '
						</li>
					';
	}
	$__finalCompiled .= '

					<li>
						<ul class="listInline listInline--comma listInline--selfInline">
							<li>' . $__templater->func('username_link', array($__vars['conversation']['Starter'], false, array(
		'defaultname' => $__vars['conversation']['username'],
		'title' => $__templater->filter('Direct message starter', array(array('for_attr', array()),), false),
	))) . '</li>';
	$__compilerTemp1 = '';
	if ($__templater->isTraversable($__vars['conversation']['recipients'])) {
		foreach ($__vars['conversation']['recipients'] AS $__vars['recipient']) {
			if ($__vars['recipient']['user_id'] != $__vars['conversation']['user_id']) {
				$__compilerTemp1 .= $__templater->func('trim', array('
								<li>' . $__templater->func('username_link', array($__vars['recipient'], false, array(
					'defaultname' => 'Unknown member',
				))) . '</li>
							'), false);
			}
		}
	}
	$__finalCompiled .= $__templater->func('trim', array('
							' . $__compilerTemp1 . '
							'), false) . '
						</ul>
					</li>

					<li>' . 'Direct message' . '</li>
					<li>' . $__templater->func('date_dynamic', array($__vars['conversation']['start_date'], array(
	))) . '</li>
					<li>' . 'Replies' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->filter($__vars['conversation']['reply_count'], array(array('number', array()),), true) . '</li>
				</ul>
			</div>
		</div>
	</div>
</li>';
	return $__finalCompiled;
}
);