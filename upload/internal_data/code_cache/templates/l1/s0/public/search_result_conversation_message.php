<?php
// FROM HASH: 822763694047dff8f91d990fca63c60e
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<li class="block-row block-row--separated ' . ($__templater->method($__vars['message'], 'isIgnored', array()) ? 'is-ignored' : '') . '" data-author="' . ($__templater->escape($__vars['message']['User']['username']) ?: $__templater->escape($__vars['message']['username'])) . '">
	<div class="contentRow">
		<span class="contentRow-figure">
			' . $__templater->func('avatar', array($__vars['message']['User'], 's', false, array(
		'defaultname' => $__vars['message']['username'],
	))) . '
		</span>

		<div class="contentRow-main">
			<h3 class="contentRow-title">
				<a href="' . $__templater->func('link', array('direct-messages/replies', $__vars['message'], ), true) . '">' . $__templater->func('highlight', array($__vars['message']['Conversation']['title'], $__vars['options']['term'], ), true) . '</a>
			</h3>

			<div class="contentRow-snippet">' . $__templater->func('snippet', array($__vars['message']['message'], 300, array('term' => $__vars['options']['term'], 'stripQuote' => true, ), ), true) . '</div>

			<div class="contentRow-minor contentRow-minor--hideLinks">
				<ul class="listInline listInline--bullet">
					<li>' . $__templater->func('username_link', array($__vars['message']['User'], false, array(
		'defaultname' => $__vars['message']['username'],
	))) . '</li>
					<li>' . 'Direct message reply' . '</li>
					<li>' . $__templater->func('date_dynamic', array($__vars['message']['message_date'], array(
	))) . '</li>
				</ul>
			</div>
		</div>
	</div>
</li>';
	return $__finalCompiled;
}
);