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
            "name" => "page#index",
            "url" => "/",
            "verb" => "GET",
        ]
    ]
];