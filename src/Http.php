<?php
namespace src;

class Http
{
    public static function handle($server, $input)
    {
        // 注册JWT类
        Container::bind('src\JWT', function () {
            $config = config('jwt');
            return new JWT($config['id'], $config['exp']);
        });

        // 定义模型的数据库连接
        Model::setConnector(function ($model) {
            return mysql($model->getTable())->where('id', $model->id);
        });

        // 获取路由
        require __DIR__.'/../app/route.php';
        $pathInfo = isset($server['PATH_INFO']) ? ltrim($server['PATH_INFO'], '/') : '';
        $route = Route::get($server['REQUEST_METHOD'], $pathInfo) ?: response(404);
        $user = null;

        try {
            // 验证token
            if (is_array($route)) {
                if (isset($server['HTTP_AUTHORIZATION'])
                    && strpos($server['HTTP_AUTHORIZATION'], 'Bearer ') === 0
                    && ($id = Container::get('src\JWT')->decode(substr($server['HTTP_AUTHORIZATION'], 7)))
                    && $user = $route[1]::handle($id)) {
                    $route = $route[0];
                } else {
                    response(401);
                }
            }

            // 解析请求参数
            if (!$input) {
                if (isset($server['CONTENT_TYPE']) && $server['CONTENT_TYPE'] == 'application/json') {
                    $this->data = json_decode(file_get_contents('php://input'), true);
                } else {
                    parse_str(file_get_contents('php://input'), $input);
                }
            }
            // 实例化请求类
            $request = new Request($input, $user, function ($val, $table, $col) {
                return mysql($table)->exists($col, $val);
            }, config('per_page'));
            Container::instance('src\Request', $request);

            // 定位控制器方法
            $dispatch = explode('@', $route);
            $controller = '\app\controller\\'.$dispatch[0].'Controller';
            $method = new \ReflectionMethod($controller, $dispatch[1]);
            // 解析方法参数
            $args = [];
            foreach ($method->getParameters() as $param) {
                if ($class = $param->getClass()) {
                    $args[] = Container::get($class->name);
                } elseif (($paramName = $param->getName()) && isset($input[$paramName])) {
                    $args[] = $input[$paramName];
                } elseif ($param->isDefaultValueAvailable()) {
                    $args[] = $param->getDefaultValue();
                } else {
                    response(400, file_exists(__DIR__.'/.dev') ? '缺少参数：'.$paramName : '');
                }
            }
            // 调用控制器方法
            return $method->invokeArgs(new $controller, $args);

        } catch (\Exception $e) {
            response(400, $e->getMessage());
        }
    }
}
