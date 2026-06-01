<?php
// FROM HASH: 02b4a6ff5e895ba10507daa70b7c07dc
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	if (!$__templater->test($__vars['features'], 'empty', array())) {
		$__finalCompiled .= '
	';
		if ($__vars['style'] == 'simple') {
			$__finalCompiled .= '
		<div class="block" ' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
			<div class="block-container">
				<h3 class="block-minorHeader">
					<a href="' . $__templater->escape($__vars['link']) . '">' . ($__templater->escape($__vars['title']) ?: 'Featured content') . '</a>
				</h3>

				<ul class="block-body">
					';
			if ($__templater->isTraversable($__vars['features'])) {
				foreach ($__vars['features'] AS $__vars['feature']) {
					$__finalCompiled .= '
						<li class="block-row">
							' . $__templater->filter($__templater->method($__vars['feature'], 'render', array('simple', $__vars['snippetLength'], )), array(array('raw', array()),), true) . '
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

		<div class="carousel ' . ($__vars['hasMore'] ? 'carousel--withFooter' : '') . '" ' . $__templater->func('widget_data', array($__vars['widget'], ), true) . '>
			<ul class="carousel-body carousel-body--show2" data-xf-init="carousel">
				';
			if ($__templater->isTraversable($__vars['features'])) {
				foreach ($__vars['features'] AS $__vars['feature']) {
					$__finalCompiled .= '
					<li class="carousel-container">
						<div class="carousel-item">
							' . $__templater->filter($__templater->method($__vars['feature'], 'render', array('carousel', $__vars['snippetLength'], )), array(array('raw', array()),), true) . '
						</div>
					</li>
				';
				}
			}
			$__finalCompiled .= '
			</ul>

			';
			if ($__vars['hasMore']) {
				$__finalCompiled .= '
				<div class="carousel-footer">
					<a href="' . $__templater->escape($__vars['link']) . '">' . 'View all featured content' . '</a>
				</div>
			';
			}
			$__finalCompiled .= '
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
			if ($__templater->isTraversable($__vars['features'])) {
				foreach ($__vars['features'] AS $__vars['feature']) {
					$__finalCompiled .= '
						' . $__templater->filter($__templater->method($__vars['feature'], 'render', array('article', $__vars['snippetLength'], )), array(array('raw', array()),), true) . '
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