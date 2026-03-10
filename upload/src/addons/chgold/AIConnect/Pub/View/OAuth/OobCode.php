<?php

namespace chgold\AIConnect\Pub\View\OAuth;

use XF\Mvc\View;

class OobCode extends View
{
    public function renderHtml()
    {
        $code = $this->params['code'];
        $forumTitle = \XF::options()->boardTitle;
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorization Code</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f5f5;
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            padding: 20px;
        }
        .code-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
            text-align: center;
        }
        h1 {
            font-size: 24px;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .subtitle {
            color: #666;
            font-size: 14px;
            margin-bottom: 30px;
        }
        .success-icon {
            width: 64px;
            height: 64px;
            margin: 0 auto 24px;
            color: #28a745;
        }
        .code-box {
            background: #f9f9f9;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            padding: 20px;
            margin: 24px 0;
        }
        .code-label {
            font-size: 12px;
            text-transform: uppercase;
            color: #666;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .code-value {
            font-family: 'Courier New', Courier, monospace;
            font-size: 16px;
            color: #1a1a1a;
            word-break: break-all;
            padding: 12px;
            background: white;
            border-radius: 4px;
            border: 1px solid #e0e0e0;
        }
        .instructions {
            color: #666;
            font-size: 14px;
            line-height: 1.6;
            margin-top: 24px;
            text-align: left;
        }
        .copy-btn {
            margin-top: 16px;
            padding: 12px 24px;
            background: #007bff;
            color: white;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .copy-btn:hover {
            background: #0056b3;
        }
        .copy-btn:active {
            transform: scale(0.98);
        }
    </style>
</head>
<body>
    <div class="code-container" style="margin: auto;">
        <svg class="success-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
        
        <h1>Authorization Approved</h1>
        <p class="subtitle">{$forumTitle}</p>
        
        <div class="code-box">
            <div class="code-label">Your Authorization Code</div>
            <div class="code-value" id="authCode">{$code}</div>
            <button class="copy-btn" onclick="copyCode()">Copy Code</button>
        </div>
        
        <div class="instructions">
            <strong>Next steps:</strong>
            <ol style="margin-top: 12px; padding-left: 20px;">
                <li>Copy the authorization code above</li>
                <li>Return to the application that requested access</li>
                <li>Paste this code when prompted</li>
            </ol>
            <p style="margin-top: 16px;">
                <strong>Note:</strong> This code will expire in 10 minutes.
            </p>
        </div>
    </div>
    
    <script>
        function copyCode() {
            const codeElement = document.getElementById('authCode');
            const code = codeElement.textContent;
            
            navigator.clipboard.writeText(code).then(function() {
                const btn = event.target;
                const originalText = btn.textContent;
                btn.textContent = 'Copied!';
                btn.style.background = '#28a745';
                
                setTimeout(function() {
                    btn.textContent = originalText;
                    btn.style.background = '#007bff';
                }, 2000);
            }).catch(function(err) {
                alert('Failed to copy code. Please copy it manually.');
            });
        }
    </script>
</body>
</html>
HTML;

        return $html;
    }
}
