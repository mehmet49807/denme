<?php

return [
    'repository' => env('DEPLOY_GITHUB_REPO', 'https://github.com/mehmet49807/denme'),
    'branch' => env('DEPLOY_GITHUB_BRANCH', 'master'),
    'actions_url' => env('DEPLOY_GITHUB_ACTIONS_URL', 'https://github.com/mehmet49807/denme/actions/workflows/deploy.yml'),
];
