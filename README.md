# NGINX配置
location / {
    rewrite .* /index.php;
}