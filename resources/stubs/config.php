<?php

return [


    /*
    |--------------------------------------------------------------------------
    | Deployment configuration
    |--------------------------------------------------------------------------
    |
    | Here you can configure your deployment type.
    | Right now, the only deployment type available is "git"
    |
    */
    'deployment' => [
        
        'type' => 'git',

        'repository' => '',

        'branch' => 'gh-pages',

        'message' => 'Site updated: ' . date('Y-m-d H:i:s')
        
    ]
];
