#!/usr/bin/env php
<?php

require __DIR__.'/app.php';

$command = '\app\command\\'.ucfirst($argv[1]);
(new $command)->handle();