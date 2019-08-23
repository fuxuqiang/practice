#!/usr/bin/env php
<?php

require __DIR__.'/app.php';

$commands = ['region' => 'RegionSpider'];

$command = '\app\command\\'.$commands[$argv[1]];
(new $command)->handle();