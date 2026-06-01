<?php
// FROM HASH: 63922e0286de035e5c9b995c1f9134d2
return array(
'code' => function($__templater, array $__vars, $__extensions = null)
{
	$__finalCompiled = '';
	$__finalCompiled .= '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>' . 'Authorization Approved' . ' - ' . $__templater->escape($__vars['forumTitle']) . '</title>
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
        .container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
            margin: auto;
        }
        h1 { font-size: 24px; color: #28a745; margin-bottom: 16px; }
        .subtitle { color: #555; font-size: 15px; margin-bottom: 20px; }
        .code-box {
            background: #f9f9f9;
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 20px;
            margin: 20px 0;
            font-family: \'Courier New\', monospace;
            font-size: 14px;
            word-break: break-all;
            color: #28a745;
            user-select: all;
        }
        .btn {
            display: inline-block;
            padding: 12px 28px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 15px;
            cursor: pointer;
            transition: background 0.2s;
        }
        .btn:hover { background: #0056b3; }
        .return-note { color: #888; font-size: 13px; margin-top: 20px; }
    </style>
</head>
<body>
    <div class="container">
        <h1>&#x2713; ' . 'Authorization Approved' . '</h1>
        <p class="subtitle">' . 'Your authorization code:' . '</p>
        <div class="code-box" id="authCode">' . $__templater->escape($__vars['code']) . '</div>
        <button
            class="btn"
            id="copyBtn"
            data-label-copy="' . 'Copy Code' . '"
            data-label-copied="' . 'Copied!' . '"
            onclick="navigator.clipboard.writeText(document.getElementById(\'authCode\').textContent.trim()); this.textContent = this.getAttribute(\'data-label-copied\');"
        >' . 'Copy Code' . '</button>
        <p class="return-note">' . 'Return to your application and paste this code.' . '</p>
    </div>
</body>
</html>';
	return $__finalCompiled;
}
);