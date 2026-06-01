<?php
// FROM HASH: 80c6fd1e9861d6e4ff6a6e0dd7264ca8
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->includeCss('editor_insert_gif.less');
	$__finalCompiled .= '

<div class="menu-row menu-row--alt menu-row--close menu-row--search">
	<div class="inputGroup">
		' . $__templater->formTextBox(array(
		'class' => 'js-gifSearch',
		'placeholder' => 'Search' . $__vars['xf']['language']['ellipsis'],
		'autofocus' => 'autofocus',
	)) . '
		' . $__templater->button('
			<span class="u-srOnly">' . 'Close' . '</span>
		', array(
		'icon' => 'close',
		'class' => 'button--iconOnly button--plain js-gifCloser',
		'title' => 'Close',
		'data-no-auto-focus' => 'true',
		'data-menu-closer' => 'true',
	), '', array(
	)) . '
	</div>
</div>
<div class="menu-scroller">
	<div class="menu-row js-gifFullRow">
		<div class="gifContainer js-gifContainer">
			';
	if ($__templater->isTraversable($__vars['trending'])) {
		foreach ($__vars['trending'] AS $__vars['id'] => $__vars['image']) {
			$__finalCompiled .= '
				<a class="gifContainer-item js-gif">
					<span class="gifContainer-cover">
						' . $__templater->fontAwesome('fa-spinner-third fa-3x', array(
			)) . '
					</span>
					<img src="' . $__templater->escape($__vars['image']['thumb']) . '" data-id="' . $__templater->escape($__vars['id']) . '"
						data-src="' . $__templater->escape($__vars['image']['src']) . '"
						data-insert="' . $__templater->escape($__vars['image']['insert']) . '"
						alt="' . $__templater->escape($__vars['image']['title']) . '" title="' . $__templater->escape($__vars['image']['title']) . '" />
				</a>
			';
		}
	}
	$__finalCompiled .= '
			';
	if ($__vars['trending']) {
		$__finalCompiled .= '
				<a class="gifContainer-item js-gifLoadMore is-loading" data-href="' . $__templater->func('link', array('editor/insert-gif', null, array('offset' => $__vars['nextOffset'], ), ), true) . '">
					<span class="gifContainer-cover">
						' . $__templater->fontAwesome('fa-spinner-third fa-3x', array(
		)) . '
					</span>
				</a>
			';
	}
	$__finalCompiled .= '
		</div>
	</div>
	<div class="menu-row js-gifSearchRow" style="display: none"></div>
</div>
<div class="menu-footer menu-footer--close menu-footer--split">
	<div class="menu-footer-opposite">
		<a href="https://giphy.com" target="_blank">
			' . $__templater->callMacro(null, 'style_variation_macros::picture_color_scheme', array(
		'light' => 'styles/default/xenforo/giphy.png',
		'dark' => 'styles/default/xenforo/giphy_dark.png',
		'width' => '100',
		'height' => '11',
		'alt' => 'Powered by GIPHY',
	), $__vars) . '
		</a>
	</div>
</div>';
	return $__finalCompiled;
}
);