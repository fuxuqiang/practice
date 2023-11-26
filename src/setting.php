z<?php

date_default_timezone_set(env('timezone'));

// 设置模型的数据库连接
\Fuxuqiang\Framework\Model\Model::setConnector(\Src\Mysql::getInstance());

return runtimePath('route.php');