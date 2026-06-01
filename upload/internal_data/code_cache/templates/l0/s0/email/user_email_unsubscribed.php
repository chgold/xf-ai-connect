<?php
// FROM HASH: f97cdfe0697ae76fdc07a11dbe652363
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<mail:subject>
	' . '' . $__templater->escape($__vars['xf']['options']['boardTitle']) . ' - Unsubscribe confirmation' . '
</mail:subject>

' . '<p>' . $__templater->escape($__vars['user']['username']) . ',</p>

<p>Your email preferences at ' . (((('<a href="' . $__templater->func('link', array('canonical:index', ), true)) . '">') . $__templater->escape($__vars['xf']['options']['boardTitle'])) . '</a>') . ' have been recently changed as requested.</p>

<p>You can make changes to your email preferences at any time by logging into your account and modifying your <a href="' . $__templater->func('link', array('canonical:account/preferences', ), true) . '">account preferences</a>.</p>

<p><strong>Note:</strong> You will still receive emails from us about important account changes.</p>' . '

';
	if ($__vars['action'] === 'all_except_dm') {
		$__finalCompiled .= '
	' . '<p>We have not currently disabled emails related to direct messages. If you wish to disable these too <a href="' . $__templater->func('link', array('canonical:email-stop/all', $__vars['user'], ), true) . '">click here</a>.' . '
';
	}
	return $__finalCompiled;
}
);