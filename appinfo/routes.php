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
            "name" => "project_api#updateProjectStatus",
            "url" => "/api/v1/projects/{projectId}/status",
            "verb" => "PUT",
        ],
        [
            "name" => "page#index",
            "url" => "/",
            "verb" => "GET",
        ],
    ]
];