<?php

(new Dotenv\Dotenv(dirname(__DIR__)))->load();

return [
    "settings" => include "settings.config.php"
];
