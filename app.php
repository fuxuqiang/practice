<?php

spl_autoload_register(function ($class) {
    require __DIR__.'/'.str_replace('\\', '/', $class).'.php';
});

require __DIR__.'/helpers.php';