<?php

return [
    "routes" => [
        [
            "name" => "project_api#create",
            "url" => "/api/v1/projects",
            "verb" => "POST",
        ],
        [
            "name" => "project_api#list",
            "url" => "/api/v1/projects/list",
            "verb" => "GET",
        ],
        [
            "name" => "project_api#projectFilesByCircleId",
            "url" => "/api/v1/projects/circle/{circleId}/files",
            "verb" => "GET",
        ],
        [
            "name" => "page#index",
            "url" => "/",
            "verb" => "GET",
        ],
    ]
];