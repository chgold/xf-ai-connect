<?php
// FROM HASH: 1ad029170138e885be9a4ceaa7127b40
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__templater->pageParams['pageTitle'] = $__templater->preEscaped('Applications');
	$__finalCompiled .= '

';
	$__templater->wrapTemplate('account_wrapper', $__vars);
	$__finalCompiled .= '

<div class="block">
	<div class="block-container">
		<div class="block-body">

			';
	if (!$__templater->test($__vars['clients'], 'empty', array())) {
		$__finalCompiled .= '
				<div class="block-row block-row--separated">
					' . 'These are the applications that you have authorized to access your account.' . '
				</div>

				';
		if ($__templater->isTraversable($__vars['clients'])) {
			foreach ($__vars['clients'] AS $__vars['client']) {
				$__finalCompiled .= '
					<li class="block-row block-row--separated">
						<div class="contentRow">
							<div class="contentRow-figure contentRow-figure--fixedSmall">
								';
				if ($__vars['client']['image_url']) {
					$__finalCompiled .= '
									<img src="' . $__templater->func('base_url', array($__vars['client']['image_url'], ), true) . '" alt="' . $__templater->escape($__vars['client']['title']) . '" />
								';
				} else {
					$__finalCompiled .= '
									' . $__templater->fontAwesome('fa-cog fa-3x u-muted', array(
					)) . '
								';
				}
				$__finalCompiled .= '
							</div>
							<div class="contentRow-main">
								<div class="contentRow-extra">
									' . $__templater->button('Revoke', array(
					'href' => $__templater->func('link', array('account/applications/revoke', $__vars['client'], ), false),
					'class' => 'button--link',
					'overlay' => 'true',
				), '', array(
				)) . '
								</div>

								<h3 class="contentRow-header">
									<a href="' . $__templater->func('link', array('account/applications', $__vars['client'], ), true) . '">' . $__templater->escape($__vars['client']['title']) . '</a>
								</h3>

								<div class="contentRow-snippet">' . $__templater->escape($__vars['client']['description']) . '</div>
							</div>
						</div>
					</li>
				';
			}
		}
		$__finalCompiled .= '
			';
	} else {
		$__finalCompiled .= '
				<div class="block-row block-row--separated">
					' . 'You have not authorized any applications to access your account.' . '
				</div>
			';
	}
	$__finalCompiled .= '
		</div>
	</div>
</div>';
	return $__finalCompiled;
}
);