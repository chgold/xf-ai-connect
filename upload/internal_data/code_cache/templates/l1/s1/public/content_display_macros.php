<?php
// FROM HASH: 4f318665612350e0a6566acc917beaa3
return array(
'macros' => array('article' => array(
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'snippet' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'links' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'metadata' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
				';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
							';
	$__compilerTemp3 = '';
	$__compilerTemp3 .= '
									' . $__templater->renderExtension('image', $__vars, $__extensions) . '
								';
	if (strlen(trim($__compilerTemp3)) > 0) {
		$__compilerTemp2 .= '
								' . $__compilerTemp3 . '
							';
	}
	$__compilerTemp2 .= '

							';
	$__compilerTemp4 = '';
	$__compilerTemp4 .= '
										';
	$__compilerTemp5 = '';
	$__compilerTemp5 .= '
														' . $__templater->renderExtension('title', $__vars, $__extensions) . '
													';
	if (strlen(trim($__compilerTemp5)) > 0) {
		$__compilerTemp4 .= '
											<div class="articlePreview-headline">
												<h2 class="articlePreview-title">
													' . $__compilerTemp5 . '
												</h2>
											</div>
										';
	}
	$__compilerTemp4 .= '

										';
	$__compilerTemp6 = '';
	$__compilerTemp6 .= '
																' . $__templater->renderExtension('snippet', $__vars, $__extensions) . '
															';
	if (strlen(trim($__compilerTemp6)) > 0) {
		$__compilerTemp4 .= '
											<div class="articlePreview-content">
												<div class="message-content">
													<div class="message-userContent">
														<article class="message-body">
															' . $__compilerTemp6 . '
														</article>
													</div>
												</div>
											</div>
										';
	}
	$__compilerTemp4 .= '

										';
	$__compilerTemp7 = '';
	$__compilerTemp7 .= '
													' . $__templater->renderExtension('links', $__vars, $__extensions) . '
												';
	if (strlen(trim($__compilerTemp7)) > 0) {
		$__compilerTemp4 .= '
											<div class="articlePreview-links">
												' . $__compilerTemp7 . '
											</div>
										';
	}
	$__compilerTemp4 .= '
									';
	if (strlen(trim($__compilerTemp4)) > 0) {
		$__compilerTemp2 .= '
								<div class="articlePreview-text">
									' . $__compilerTemp4 . '
								</div>
							';
	}
	$__compilerTemp2 .= '
						';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__compilerTemp1 .= '
					<div class="articlePreview-main">
						' . $__compilerTemp2 . '
					</div>
				';
	}
	$__compilerTemp1 .= '

				';
	$__compilerTemp8 = '';
	$__compilerTemp8 .= '
								' . $__templater->renderExtension('meta', $__vars, $__extensions) . '
							';
	if (strlen(trim($__compilerTemp8)) > 0) {
		$__compilerTemp1 .= '
					<footer class="articlePreview-footer">
						<ul class="articlePreview-meta listPlain">
							' . $__compilerTemp8 . '
						</ul>
					</footer>
				';
	}
	$__compilerTemp1 .= '

				' . $__templater->renderExtension('metadata', $__vars, $__extensions) . '
			';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
		';
		$__templater->includeCss('message.less');
		$__finalCompiled .= '

		<article class="message message--article message--articlePreview">
			' . $__compilerTemp1 . '
		</article>
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
),
'simple' => array(
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'extra' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'metadata' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
				';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
							' . $__templater->renderExtension('image', $__vars, $__extensions) . '
						';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__compilerTemp1 .= '
					<div class="contentRow-figure">
						' . $__compilerTemp2 . '
					</div>
				';
	}
	$__compilerTemp1 .= '

				';
	$__compilerTemp3 = '';
	$__compilerTemp3 .= '
							' . $__templater->renderExtension('title', $__vars, $__extensions) . '

							';
	$__compilerTemp4 = '';
	$__compilerTemp4 .= '
											' . $__templater->renderExtension('meta', $__vars, $__extensions) . '
										';
	if (strlen(trim($__compilerTemp4)) > 0) {
		$__compilerTemp3 .= '
								<div class="contentRow-minor contentRow-minor--hideLinks">
									<ul class="listInline listInline--bullet">
										' . $__compilerTemp4 . '
									</ul>
								</div>
							';
	}
	$__compilerTemp3 .= '

							' . $__templater->renderExtension('extra', $__vars, $__extensions) . '
						';
	if (strlen(trim($__compilerTemp3)) > 0) {
		$__compilerTemp1 .= '
					<div class="contentRow-main contentRow-main--close">
						' . $__compilerTemp3 . '
					</div>
				';
	}
	$__compilerTemp1 .= '

				' . $__templater->renderExtension('metadata', $__vars, $__extensions) . '
			';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
		<div class="contentRow">
			' . $__compilerTemp1 . '
		</div>
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
),
'carousel' => array(
'extensions' => array('image' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'title' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'snippet' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'meta' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
},
'metadata' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	
	return $__finalCompiled;
}),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '
	';
	$__compilerTemp1 = '';
	$__compilerTemp1 .= '
				';
	$__compilerTemp2 = '';
	$__compilerTemp2 .= '
							' . $__templater->renderExtension('image', $__vars, $__extensions) . '
						';
	if (strlen(trim($__compilerTemp2)) > 0) {
		$__compilerTemp1 .= '
					<div class="contentRow-figure">
						' . $__compilerTemp2 . '
					</div>
				';
	}
	$__compilerTemp1 .= '

				';
	$__compilerTemp3 = '';
	$__compilerTemp3 .= '
							';
	$__compilerTemp4 = '';
	$__compilerTemp4 .= '
										' . $__templater->renderExtension('title', $__vars, $__extensions) . '
									';
	if (strlen(trim($__compilerTemp4)) > 0) {
		$__compilerTemp3 .= '
								<h4 class="contentRow-title">
									' . $__compilerTemp4 . '
								</h4>
							';
	}
	$__compilerTemp3 .= '

							';
	$__compilerTemp5 = '';
	$__compilerTemp5 .= '
										' . $__templater->renderExtension('snippet', $__vars, $__extensions) . '
									';
	if (strlen(trim($__compilerTemp5)) > 0) {
		$__compilerTemp3 .= '
								<div class="contentRow-lesser">
									' . $__compilerTemp5 . '
								</div>
							';
	}
	$__compilerTemp3 .= '

							';
	$__compilerTemp6 = '';
	$__compilerTemp6 .= '
											' . $__templater->renderExtension('meta', $__vars, $__extensions) . '
										';
	if (strlen(trim($__compilerTemp6)) > 0) {
		$__compilerTemp3 .= '
								<div class="contentRow-minor contentRow-minor--smaller contentRow-minor--hideLinks">
									<ul class="listInline listInline--bullet">
										' . $__compilerTemp6 . '
									</ul>
								</div>
							';
	}
	$__compilerTemp3 .= '
						';
	if (strlen(trim($__compilerTemp3)) > 0) {
		$__compilerTemp1 .= '
					<div class="contentRow-main">
						' . $__compilerTemp3 . '
					</div>
				';
	}
	$__compilerTemp1 .= '

				' . $__templater->renderExtension('metadata', $__vars, $__extensions) . '
			';
	if (strlen(trim($__compilerTemp1)) > 0) {
		$__finalCompiled .= '
		<div class="contentRow">
			' . $__compilerTemp1 . '
		</div>
	';
	}
	$__finalCompiled .= '
';
	return $__finalCompiled;
}
)),
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '

' . '

';
	return $__finalCompiled;
}
);