<?php
// FROM HASH: c0cf1adb93eb05e9ed9128dc3d5f1a1a
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
			';
	if ($__vars['content']['Thread']['prefix_id']) {
		$__finalCompiled .= '
				' . $__templater->func('prefix', array('thread', $__vars['content']['Thread'], 'html', '', ), true) . '
			';
	}
	$__finalCompiled .= '
			<a href="' . $__templater->escape($__templater->method($__vars['content'], 'getContentUrl', array())) . '" class="fauxBlockLink-blockLink u-cloaked">' . $__templater->escape($__templater->method($__vars['content'], 'getContentTitle', array())) . '</a>
		</h3>

		<div class="contentRow-minor contentRow-minor--hideLinks">
			' . $__templater->func('date_dynamic', array($__vars['content']['post_date'], array(
	))) . '
		</div>
	</div>
</div>

<div class="embed-preview">
	' . $__templater->func('bb_code_snippet', array($__vars['content']['message'], 'post', $__vars['content'], 600, array('withinEmbedResolver' => true, ), ), true) . '
</div>

<div class="embed-footer">
	<div class="embed-footer-main">
		<ul class="listInline listInline--bullet">
			<li>
				' . $__templater->func('username_link', array($__vars['content']['User'], false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '
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