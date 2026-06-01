<?php
// FROM HASH: 86880433d8115edc937cde7b83425561
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>' . 'Authorization Request' . ' - ' . $__templater->escape($__vars['forumTitle']) . '</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .consent-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            margin: auto;
        }
        .consent-header { text-align: center; margin-bottom: 30px; }
        .consent-header h1 { font-size: 24px; color: #1a1a1a; margin-bottom: 8px; }
        .consent-header p { color: #666; font-size: 14px; }
        .client-info {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .client-name { font-weight: 600; font-size: 18px; color: #1a1a1a; margin-bottom: 8px; }
        .client-access { color: #666; font-size: 14px; }
        .scopes-section h2 { font-size: 16px; color: #1a1a1a; margin-bottom: 16px; }
        .scope-list { list-style: none; margin-bottom: 24px; }
        .scope-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .scope-item:last-child { border-bottom: none; }
        .scope-icon { width: 20px; height: 20px; margin-right: 12px; color: #007bff; flex-shrink: 0; }
        .scope-label { color: #1a1a1a; font-size: 14px; }
        .button-group { display: flex; gap: 12px; margin-top: 24px; }
        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            font-weight: 500;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn-approve { background: #007bff; color: white; }
        .btn-approve:hover { background: #0056b3; }
        .btn-deny { background: #f0f0f0; color: #333; border: 1px solid #ddd; }
        .btn-deny:hover { background: #e0e0e0; }
        .warning {
            background: #fff8e1;
            border-left: 4px solid #ffc107;
            padding: 12px 16px;
            margin-top: 20px;
            border-radius: 4px;
        }
        .warning p { color: #666; font-size: 13px; line-height: 1.5; }
    </style>
</head>
<body>
    <div class="consent-container">
        <div class="consent-header">
            <h1>' . 'Authorization Request' . '</h1>
            <p>' . $__templater->escape($__vars['forumTitle']) . '</p>
        </div>

        <div class="client-info">
            <div class="client-name">' . $__templater->escape($__vars['client']['client_name']) . '</div>
            <p class="client-access">' . '' . $__templater->escape($__vars['client']['client_name']) . ' wants to access your ' . $__templater->escape($__vars['forumTitle']) . ' account' . '</p>
        </div>

        <div class="scopes-section">
            <h2>' . 'This application will be able to:' . '</h2>
            <ul class="scope-list">
                ';
	if ($__templater->isTraversable($__vars['scopeLabels'])) {
		foreach ($__vars['scopeLabels'] AS $__vars['scopeName'] => $__vars['label']) {
			$__finalCompiled .= '
                <li class="scope-item">
                    <svg class="scope-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="scope-label">' . $__templater->escape($__vars['label']) . '</span>
                </li>
                ';
		}
	}
	$__finalCompiled .= '
            </ul>
        </div>

        <form method="post" action="">
            <input type="hidden" name="_xfToken" value="' . $__templater->escape($__vars['csrfToken']) . '" />
            <input type="hidden" name="client_id" value="' . $__templater->escape($__vars['clientId']) . '" />
            <input type="hidden" name="redirect_uri" value="' . $__templater->escape($__vars['redirectUri']) . '" />
            <input type="hidden" name="response_type" value="' . $__templater->escape($__vars['responseType']) . '" />
            <input type="hidden" name="scope" value="' . $__templater->escape($__vars['scope']) . '" />
            <input type="hidden" name="state" value="' . $__templater->escape($__vars['state']) . '" />
            <input type="hidden" name="code_challenge" value="' . $__templater->escape($__vars['codeChallenge']) . '" />
            <input type="hidden" name="code_challenge_method" value="' . $__templater->escape($__vars['codeChallengeMethod']) . '" />

            <div class="button-group">
                <button type="submit" name="deny" value="1" class="btn btn-deny">' . 'Deny' . '</button>
                <button type="submit" name="approve" value="1" class="btn btn-approve">' . 'Authorize' . '</button>
            </div>
        </form>

        <div class="warning">
            <p>' . 'Only authorize if you trust this application. It will have access to your account according to the permissions listed above.' . '</p>
        </div>
    </div>
</body>
</html>';
	return $__finalCompiled;
}
);