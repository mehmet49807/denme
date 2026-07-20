<?php

return [
    'repository' => env('DEPLOY_GITHUB_REPO', 'https://github.com/mehmet49807/denme'),
    'branch' => env('DEPLOY_GITHUB_BRANCH', 'master'),
    'actions_url' => env('DEPLOY_GITHUB_ACTIONS_URL', 'https://github.com/mehmet49807/denme/actions/workflows/deploy.yml'),
    'compare_url' => env('DEPLOY_GITHUB_COMPARE_URL', 'https://github.com/mehmet49807/denme/compare'),
    'secrets_url' => env('DEPLOY_GITHUB_SECRETS_URL', 'https://github.com/mehmet49807/denme/settings/secrets/actions'),
    'pulls_url' => env('DEPLOY_GITHUB_PULLS_URL', 'https://github.com/mehmet49807/denme/pulls'),
    'repo_slug' => env('DEPLOY_GITHUB_REPO_SLUG', 'mehmet49807/denme'),
    'workflow_file' => env('DEPLOY_GITHUB_WORKFLOW', 'deploy.yml'),
    'github_token' => env('DEPLOY_GITHUB_TOKEN', env('GITHUB_TOKEN', '')),

    'web_url' => env('DEPLOY_WEB_URL', 'https://gonulkoprusu.com'),
    'admin_url' => env('DEPLOY_ADMIN_URL', 'https://admin.gonulkoprusu.com'),
    'setup_key' => env('SETUP_CACHE_KEY', 'gk-cpanel-setup-2026'),

    'alert_email' => env('DEPLOY_ALERT_EMAIL', env('MAIL_FROM_ADDRESS', 'destek@gonulkoprusu.com')),

    'required_secrets' => [
        'FTP_WEB_USER',
        'FTP_WEB_PASSWORD',
        'FTP_ADMIN_USER',
        'FTP_ADMIN_PASSWORD',
        'SETUP_CACHE_KEY',
    ],

    'paths' => [
        'web' => [
            'web-site/app → app',
            'web-site/routes → routes',
            'web-site/resources → resources',
            'web-site/public/images → images',
        ],
        'admin' => [
            'admin-panel/app → app',
            'admin-panel/routes → routes',
            'admin-panel/config → config',
            'admin-panel/resources → resources',
            'logo & admin CSS → images/, css/',
        ],
    ],
];
