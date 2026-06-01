<?php
// FROM HASH: 98d941ce95588b7f0590425eb19850fe
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<div class="embed-container contentRow contentRow--alignMiddle">
	<div class="contentRow-figure">
		' . $__templater->func('avatar', array($__vars['content']['User'], 's', false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '
	</div>
	<div class="contentRow-main">
		<h3 class="contentRow-header">
			<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '" class="fauxBlockLink-blockLink u-cloaked">
				';
	if ($__vars['content']['user_id'] == $__vars['content']['ProfileUser']['user_id']) {
		$__finalCompiled .= '
					' . $__templater->escape($__templater->method($__vars['content'], 'getContentTitle', array())) . '
				';
	} else {
		$__finalCompiled .= '
					' . '' . $__templater->escape($__vars['content']['User']['username']) . '\'s message on ' . $__templater->escape($__vars['content']['ProfileUser']['username']) . '\'s profile.' . '
				';
	}
	$__finalCompiled .= '
			</a>
		</h3>

		<div class="contentRow-minor contentRow-minor--hideLinks">
			' . $__templater->func('date_dynamic', array($__vars['content']['post_date'], array(
	))) . '
		</div>
	</div>
</div>

<div class="embed-preview">
	' . $__templater->func('snippet', array($__vars['content']['message'], 300, array('stripQuote' => true, 'withinEmbedResolver' => true, ), ), true) . '
</div>

<div class="embed-footer">
	<div class="embed-footer-main">
		<ul class="listInline listInline--bullet">
			<li>
				' . $__templater->func('username_link', array($__vars['content']['User'], false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '
			</li>
			<li>
				' . 'Comments' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->filter($__vars['content']['comment_count'], array(array('number', array()),), true) . '
			</li>
		</ul>
	</div>
	<div class="embed-footer-opposite">
		' . $__templater->func('reactions_summary', array($__vars['content']['reactions'])) . '
	</div>
</div>';
	return $__finalCompiled;
}
);