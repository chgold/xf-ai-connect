<?php

namespace chgold\AIConnect\Pub\View\OAuth;

use XF\Mvc\View;

class ConsentScreen extends View
{
    public function renderHtml()
    {
        $client = $this->params['client'];
        $scopes = $this->params['scopes'];
        $clientId = $this->params['clientId'];
        $redirectUri = $this->params['redirectUri'];
        $responseType = $this->params['responseType'];
        $scope = $this->params['scope'];
        $state = $this->params['state'];
        $codeChallenge = $this->params['codeChallenge'];
        $codeChallengeMethod = $this->params['codeChallengeMethod'];
        
        $scopeLabels = [
            'read' => 'Read forum content and your profile',
            'write' => 'Create and modify content',
            'delete' => 'Delete content',
            'admin' => 'Administrative access'
        ];
        
        $csrfToken = \XF::visitor()->csrf_token_page;
        $forumTitle = \XF::options()->boardTitle;
        
        $html = <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Authorization Request</title>
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
        .consent-container {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            max-width: 500px;
            width: 100%;
            padding: 40px;
        }
        .consent-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .consent-header h1 {
            font-size: 24px;
            color: #1a1a1a;
            margin-bottom: 8px;
        }
        .consent-header p {
            color: #666;
            font-size: 14px;
        }
        .client-info {
            background: #f9f9f9;
            border-radius: 6px;
            padding: 20px;
            margin-bottom: 24px;
        }
        .client-name {
            font-weight: 600;
            font-size: 18px;
            color: #1a1a1a;
            margin-bottom: 12px;
        }
        .scopes-section h2 {
            font-size: 16px;
            color: #1a1a1a;
            margin-bottom: 16px;
        }
        .scope-list {
            list-style: none;
            margin-bottom: 24px;
        }
        .scope-item {
            display: flex;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .scope-item:last-child {
            border-bottom: none;
        }
        .scope-icon {
            width: 20px;
            height: 20px;
            margin-right: 12px;
            color: #007bff;
        }
        .scope-label {
            color: #1a1a1a;
            font-size: 14px;
        }
        .actions {
            display: flex;
            gap: 12px;
        }
        .btn {
            flex: 1;
            padding: 12px 24px;
            border: none;
            border-radius: 4px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s;
        }
        .btn-approve {
            background: #007bff;
            color: white;
        }
        .btn-approve:hover {
            background: #0056b3;
        }
        .btn-deny {
            background: #f0f0f0;
            color: #1a1a1a;
        }
        .btn-deny:hover {
            background: #e0e0e0;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 16px;
            margin-top: 24px;
            border-radius: 4px;
        }
        .warning p {
            color: #666;
            font-size: 13px;
            line-height: 1.5;
        }
    </style>
</head>
<body>
    <div class="consent-container">
        <div class="consent-header">
            <h1>Authorization Request</h1>
            <p>{$forumTitle}</p>
        </div>

        <div class="client-info">
            <div class="client-name">{$client['client_name']}</div>
            <p style="color: #666; font-size: 14px;">
                is requesting access to your account
            </p>
        </div>

        <div class="scopes-section">
            <h2>This will allow the application to:</h2>
            <ul class="scope-list">
HTML;

        foreach ($scopes as $scopeName) {
            $label = $scopeLabels[$scopeName] ?? ucfirst($scopeName);
            $html .= <<<HTML
                <li class="scope-item">
                    <svg class="scope-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                    </svg>
                    <span class="scope-label">{$label}</span>
                </li>
HTML;
        }

        $html .= <<<HTML
            </ul>
        </div>

        <form method="post">
            <input type="hidden" name="_xfToken" value="{$csrfToken}">
            <input type="hidden" name="client_id" value="{$clientId}">
            <input type="hidden" name="redirect_uri" value="{$redirectUri}">
            <input type="hidden" name="response_type" value="{$responseType}">
            <input type="hidden" name="scope" value="{$scope}">
            <input type="hidden" name="state" value="{$state}">
            <input type="hidden" name="code_challenge" value="{$codeChallenge}">
            <input type="hidden" name="code_challenge_method" value="{$codeChallengeMethod}">

            <div class="actions">
                <button type="submit" name="deny" value="1" class="btn btn-deny">
                    Deny
                </button>
                <button type="submit" name="approve" value="1" class="btn btn-approve">
                    Approve
                </button>
            </div>
        </form>

        <div class="warning">
            <p>Only approve if you trust this application. It will have access to your account data based on the permissions above.</p>
        </div>
    </div>
</body>
</html>
HTML;

        return $html;
    }
}
