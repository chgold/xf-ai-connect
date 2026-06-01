<?php
// FROM HASH: 4da2de26403ad60a5657171fcfde4461
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
<div class="block" ' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
	<div class="block-container">
		<h3 class="block-minorHeader">' . $__templater->escape($__vars['title']) . '</h3>

		<div class="block-body block-row">
			<dl class="pairs pairs--spaced pairs--small">
				<dt>' . 'Created' . '</dt>
				<dd>
					' . $__templater->func('username_link', array($__vars['thread'], false, array(
	))) . ',
					<a href="' . $__templater->func('link', array('threads', $__vars['thread'], ), true) . '" class="u-concealed">
						' . $__templater->func('date_dynamic', array($__vars['thread']['post_date'], array(
	))) . '
					</a>
				</dd>
			</dl>

			';
	if ($__vars['thread']['reply_count']) {
		$__finalCompiled .= '
				<dl class="pairs pairs--spaced">
					<dt>' . 'Last reply from' . '</dt>
					<dd>
						' . $__templater->func('username_link', array($__vars['thread']['last_post_cache'], false, array(
		))) . ',
						<a href="' . $__templater->func('link', array('threads/latest', $__vars['thread'], ), true) . '" class="u-concealed">
							' . $__templater->func('date_dynamic', array($__vars['thread']['last_post_date'], array(
		))) . '
						</a>
					</dd>
				</dl>
			';
	}
	$__finalCompiled .= '

			<dl class="pairs pairs--justified count--replies">
				<dt>' . 'Replies' . '</dt>
				<dd>' . $__templater->filter($__vars['thread']['reply_count'], array(array('number', array()),), true) . '</dd>
			</dl>

			<dl class="pairs pairs--justified count--views">
				<dt>' . 'Views' . '</dt>
				<dd>' . $__templater->filter($__vars['thread']['view_count'], array(array('number', array()),), true) . '</dd>
			</dl>
			' . '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);