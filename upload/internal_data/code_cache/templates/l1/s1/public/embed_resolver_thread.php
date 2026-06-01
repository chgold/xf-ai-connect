<?php
// FROM HASH: 0c376953c1cabadf61316ad878d265fe
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeCss('help_page_smilies.less');
	$__finalCompiled .= '

<div class="embed-container contentRow contentRow--alignMiddle">
	<div class="contentRow-figure">
		' . $__templater->func('avatar', array($__vars['content']['User'], 's', false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '
	</div>
	<div class="contentRow-main">
		<h3 class="contentRow-header">
			';
	if ($__vars['content']['prefix_id']) {
		$__finalCompiled .= '
				' . $__templater->func('prefix', array('thread', $__vars['content'], 'html', '', ), true) . '
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
	' . $__templater->func('bb_code_snippet', array($__vars['content']['FirstPost']['message'], 'post', $__vars['content']['FirstPost'], 600, array('withinEmbedResolver' => true, ), ), true) . '
</div>

' . '

<div class="embed-footer">
	<div class="embed-footer-main">
		<ul class="listInline listInline--bullet">
			<li>' . $__templater->func('username_link', array($__vars['content']['User'], false, array(
		'defaultname' => $__vars['content']['username'],
	))) . '</li>
			';
	if ($__vars['xf']['options']['enableTagging'] AND $__vars['content']['tags']) {
		$__finalCompiled .= '
				<li>
					' . $__templater->callMacro('tag_macros', 'simple_list', array(
			'tags' => $__vars['content']['tags'],
		), $__vars) . '
				</li>
			';
	}
	$__finalCompiled .= '
			<li>' . 'Replies' . $__vars['xf']['language']['label_separator'] . ' ' . $__templater->filter($__vars['content']['reply_count'], array(array('number', array()),), true) . '</li>
			<li>' . 'Forum' . $__vars['xf']['language']['label_separator'] . ' <a href="' . $__templater->func('link', array('forums', $__vars['content']['Forum'], ), true) . '">' . $__templater->escape($__vars['content']['Forum']['title']) . '</a></li>
		</ul>
	</div>
	<div class="embed-footer-opposite">
		' . $__templater->func('reactions_summary', array($__vars['content']['first_post_reactions'])) . '
	</div>
</div>';
	return $__finalCompiled;
}
);