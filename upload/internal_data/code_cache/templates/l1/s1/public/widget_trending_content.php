<?php
// FROM HASH: 107feafb99bcc26a90be24cd151c4d66
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if (!$__templater->test($__vars['content'], 'empty', array())) {
		$__finalCompiled .= '
	';
		if ($__vars['style'] == 'simple') {
			$__finalCompiled .= '
		<div class="block" ' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
			<div class="block-container">
				<h3 class="block-minorHeader">' . ($__templater->escape($__vars['title']) ?: 'Trending content') . '</h3>

				<ul class="block-body">
					';
			if ($__templater->isTraversable($__vars['content'])) {
				foreach ($__vars['content'] AS $__vars['item']) {
					$__finalCompiled .= '
						<li class="block-row">
							' . $__templater->filter($__templater->method($__vars['result'], 'renderContent', array($__vars['item'], 'simple', $__vars['snippetLength'], )), array(array('raw', array()),), true) . '
						</li>
					';
				}
			}
			$__finalCompiled .= '
				</ul>
			</div>
		</div>
	';
		} else if ($__vars['style'] == 'carousel') {
			$__finalCompiled .= '
		' . $__templater->callMacro(null, 'carousel_macros::setup', array(), $__vars) . '

		<div class="carousel" ' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
			<ul class="carousel-body carousel-body--show2" data-xf-init="carousel">
				';
			if ($__templater->isTraversable($__vars['content'])) {
				foreach ($__vars['content'] AS $__vars['item']) {
					$__finalCompiled .= '
					<li class="carousel-container">
						<div class="carousel-item">
							' . $__templater->filter($__templater->method($__vars['result'], 'renderContent', array($__vars['item'], 'carousel', $__vars['snippetLength'], )), array(array('raw', array()),), true) . '
						</div>
					</li>
				';
				}
			}
			$__finalCompiled .= '
			</ul>
		</div>
	';
		} else {
			$__finalCompiled .= '
		';
			$__templater->includeCss('message.less');
			$__finalCompiled .= '

		<div class="block block--articles block--messages block--previews" ' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
			<div class="block-container">
				<div class="block-body">
					';
			if ($__templater->isTraversable($__vars['content'])) {
				foreach ($__vars['content'] AS $__vars['item']) {
					$__finalCompiled .= '
						' . $__templater->filter($__templater->method($__vars['result'], 'renderContent', array($__vars['item'], 'article', $__vars['snippetLength'], )), array(array('raw', array()),), true) . '
					';
				}
			}
			$__finalCompiled .= '
				</div>
			</div>
		</div>
	';
		}
		$__finalCompiled .= '
';
	}
	return $__finalCompiled;
}
);