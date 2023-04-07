<?php

/**
 * 获取配置的环境变量
 */
function env(string $name)
{
    static $env;
    $env || $env = parse_ini_file(__DIR__ . '/../.env', true);
    return $env[$name] ?? null;
}

/**
 * 记录日志
 */
function logError(Throwable $content): void
{
    file_put_contents(
        __DIR__ . '/../runtime/error.log',
        '[' . timestamp() . ']' . PHP_EOL . $content . PHP_EOL . PHP_EOL,
        FILE_APPEND | LOCK_EX
    );
}

/**
 * 获取时间
 */
function timestamp(int $time = null): string
{
    return date('Y-m-d H:i:s', $time ?: time());
}

/**
 * 启动会话
 */
function sessionStart(): bool
{
    static $started;
    return $started || $started = session_start();
}

/**
 * 响应的数据格式
 */
function data($data): array
{
    return ['data' => $data];
}

/**
 * 获取错误信息格式
 */
function error($msg): array
{
    return ['error' => $msg];
}

/**
 * 获取信息提示格式
 */
function msg($msg): array
{
    return ['msg' => $msg];
}

/**
 * 获取runtime文件夹中的文件路径
 */
function runtimePath($file): string
{
    return __DIR__ . '/../runtime/' . $file;
}
