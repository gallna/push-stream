<?php
use Slim\Container;

return [
    "stage" => getenv("STAGE") ?: "development",
    "displayErrorDetails" => getenv("DEBUG"),
];
