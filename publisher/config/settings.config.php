<?php
use Slim\Container;

return [
    "stage" => getenv("STAGE"),
    "displayErrorDetails" => getenv("DEBUG"),
];
