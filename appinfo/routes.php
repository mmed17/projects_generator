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
            "name" => "project_api#get",
            "url" => "/api/v1/projects/{projectId}",
            "verb" => "GET",
        ],
        [
            "name" => "project_api#getProjectFolderContents",
            "url" => "/api/v1/projects/{projectId}/files",
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