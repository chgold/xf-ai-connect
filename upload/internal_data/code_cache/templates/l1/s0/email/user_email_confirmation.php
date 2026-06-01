<?php
// FROM HASH: a5b94701c3031772ac6091092378827f
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<mail:subject>
	' . '' . $__templater->escape($__vars['xf']['options']['boardTitle']) . ' - Account confirmation required' . '
</mail:subject>

' . '<p>' . $__templater->escape($__vars['user']['username']) . ', in order to complete your registration or reactivate your account at ' . (((('<a href="' . $__templater->func('link', array('canonical:index', ), true)) . '">') . $__templater->escape($__vars['xf']['options']['boardTitle'])) . '</a>') . ', you need to confirm your email address by clicking the button below.</p>' . '

<p><a href="' . $__templater->func('link', array('canonical:account-confirmation/email', $__vars['user'], array('c' => $__vars['confirmation']['confirmation_key'], ), ), true) . '" class="button">' . 'Confirm your email' . '</a></p>

' . '<p class="minorText">Or, paste the following link into your browser: ' . $__templater->func('link', array('canonical:account-confirmation/email', $__vars['user'], array('c' => $__vars['confirmation']['confirmation_key'], ), ), true) . '</p>';
	return $__finalCompiled;
}
);