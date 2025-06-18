<?php

return [
    "routes" => [
        [
            "name" => "project_api#create",
            "url" => "/api/v1/projects",
            "verb" => "POST",
        ],
        [
            "name" => "project_api#update",
            "url" => "/api/v1/projects/{id}",
            "verb" => "PUT"
        ],
        [
            "name" => "project_api#delete",
            "url" => "/api/v1/projects/{id}",
            "verb" => "DELETE"  
        ],
        [
            "name" => "project_api#list",
            "url" => "/api/v1/projects",
            "verb" => "GET",
        ],
        [
            "name" => "page#index",
            "url" => "/",
            "verb" => "GET",
        ]
    ]
];