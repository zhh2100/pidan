<?php
// [ 应用入口文件 ]
namespace pidan;
require __DIR__ . '/../vendor/autoload.php';
// 执行HTTP应用并响应
$http = (new App())->http;
print_r(app('request')->ip());exit;
$response = $http->run();
$response->send();
$http->end($response);
echo '<br>'.(microtime(true)-app()->G('?begin'));
file_put_contents('timeout.txt', microtime(true)-app()->G('?begin'));

