<?php
// FROM HASH: 3d378afad14d314bcd84341574f6a752
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Featured content');
	$__templater->pageParams['pageNumber'] = $__vars['page'];
	$__finalCompiled .= '

';
	$__templater->includeCss('message.less');
	$__finalCompiled .= '

';
	$__templater->setPageParam('head.' . 'rss', $__templater->preEscaped('
	<link rel="alternate" type="application/rss+xml"
		title="' . $__templater->filter('RSS feed for ' . 'Featured content' . '', array(array('for_attr', array()),), true) . '"
		href="' . $__templater->func('link', array('featured/index.rss', ), true) . '" />
'));
	$__finalCompiled .= '

';
	$__compilerTemp1 = $__vars;
	$__compilerTemp1['pageSelected'] = $__templater->preEscaped('featured');
	$__templater->wrapTemplate('whats_new_wrapper', $__compilerTemp1);
	$__finalCompiled .= '

' . $__templater->widgetPosition('featured_content_list_above_features', array()) . '

';
	if (!$__templater->test($__vars['features'], 'empty', array())) {
		$__finalCompiled .= '
	<div class="block block--articles block--messages block--previews">
		';
		$__compilerTemp2 = '';
		$__compilerTemp2 .= '
					' . $__templater->func('page_nav', array(array(
			'link' => 'featured',
			'params' => $__vars['filters'],
			'page' => $__vars['page'],
			'total' => $__vars['total'],
			'wrapperclass' => 'block-outer-main',
			'perPage' => $__vars['perPage'],
		))) . '
				';
		if (strlen(trim($__compilerTemp2)) > 0) {
			$__finalCompiled .= '
			<div class="block-outer">
				' . $__compilerTemp2 . '
			</div>
		';
		}
		$__finalCompiled .= '

		<div class="block-container">
			';
		if ($__vars['displayFilters']) {
			$__finalCompiled .= '
				<div class="block-filterBar">
					<div class="filterBar">
						<ul class="filterBar-filters">
							';
			if ($__vars['filters']['content_type']) {
				$__finalCompiled .= '
								<li>
									<a href="' . $__templater->func('link', array('featured', null, $__templater->filter($__vars['filters'], array(array('replace', array(array('content_type' => null, ), )),), false), ), true) . '"
										class="filterBar-filterToggle"
										data-xf-init="tooltip" title="' . $__templater->filter('Remove this filter', array(array('for_attr', array()),), true) . '">

										<span class="filterBar-filterToggle-label">' . 'Content type' . $__vars['xf']['language']['label_separator'] . '</span>
										' . ($__templater->escape($__templater->method($__vars['xf']['app'], 'getContentTypePhrase', array($__vars['filters']['content_type'], ))) ?: $__templater->escape($__vars['filters']['content_type'])) . '
									</a>
								</li>
							';
			}
			$__finalCompiled .= '
						</ul>

						<a class="filterBar-menuTrigger" role="button" tabindex="0"
							data-xf-click="menu" aria-expanded="false" aria-haspopup="true">

							' . 'Filters' . '
						</a>

						<div class="menu menu--wide"
							data-menu="menu" aria-hidden="true"
							data-href="' . $__templater->func('link', array('featured/filters', null, $__vars['filters'], ), true) . '"
							data-load-target=".js-filterMenuBody">

							<div class="menu-content">
								<h4 class="menu-header">' . 'Show only' . $__vars['xf']['language']['label_separator'] . '</h4>
								<div class="js-filterMenuBody">
									<div class="menu-row">' . 'Loading' . $__vars['xf']['language']['ellipsis'] . '</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			';
		}
		$__finalCompiled .= '

			<div class="block-body">
				';
		if ($__templater->isTraversable($__vars['features'])) {
			foreach ($__vars['features'] AS $__vars['feature']) {
				$__finalCompiled .= '
					' . $__templater->filter($__templater->method($__vars['feature'], 'render', array()), array(array('raw', array()),), true) . '
				';
			}
		}
		$__finalCompiled .= '
			</div>
		</div>

		';
		$__compilerTemp3 = '';
		$__compilerTemp3 .= '
					' . $__templater->func('page_nav', array(array(
			'link' => 'featured',
			'params' => $__vars['filters'],
			'page' => $__vars['page'],
			'total' => $__vars['total'],
			'wrapperclass' => 'block-outer-main',
			'perPage' => $__vars['perPage'],
		))) . '
				';
		if (strlen(trim($__compilerTemp3)) > 0) {
			$__finalCompiled .= '
			<div class="block-outer block-outer--after">
				' . $__compilerTemp3 . '
			</div>
		';
		}
		$__finalCompiled .= '
	</div>
';
	} else {
		$__finalCompiled .= '
	<div class="blockMessage">
		' . 'There is not currently any featured content.' . '
	</div>
';
	}
	$__finalCompiled .= '

' . $__templater->widgetPosition('featured_content_list_below_features', array()) . '

';
	$__templater->modifySidebarHtml('_xfWidgetPositionSidebarbd01112353dfc49e21f9acf173f3b56b', $__templater->widgetPosition('featured_content_list_sidebar', array()), 'replace');
	return $__finalCompiled;
}
);