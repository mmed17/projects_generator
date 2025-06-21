<?php

return [
    "routes" => [
        [
            "name" => "project_api#create",
            "url" => "/api/v1/projects",
            "verb" => "POST",
        ],
        [
            "name" => "project_api#search",
            "url" => "/api/v1/projects/search",
            "verb" => "GET",
        ],
        [
            "name" => "project_api#list",
            "url" => "/api/v1/projects/list",
            "verb" => "GET",
        ],
        [
            "name" => "project_api#getUserProjects",
            "url" => "/api/v1/users/{userId}/projects",
            "verb" => "GET",
        ],
        [
            'name' => 'project_api#getOpenTasks',
            'url' => '/api/v1/tasks/open',
            'verb' => 'GET'
        ],
        [
            'name' => 'project_api#getOverdueTasks',
            'url' => '/api/v1/tasks/overdue',
            'verb' => 'GET'
        ],
        [
            'name' => 'project_api#getUpcomingTasks',
            'url' => '/api/v1/tasks/upcoming',
            'verb' => 'GET'
        ],
        [
            "name" => "page#index",
            "url" => "/",
            "verb" => "GET",
        ]
    ]
];