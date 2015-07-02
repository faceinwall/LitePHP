### 关于LitePHP
LitePHP是一个简单, 轻量, 快速, 可扩展的框架.它可以让快速的建一个REST风格的应用程序.

```php
require 'lite/Lite.php';

$app->addRoute('/', function(){
	
	$this->body = "<h4>hello world!</h4>";

	$this->response();
});

$app->addRoute('/@name/@id', function($name, $id){
    echo "hello, $name ($id)!";
});

$app->run();

```

### Requirements

LitePHP 需要PHP5.4以上

### License

MIT license许可.

###\.安装
下载litePHP安装包

###\.配置您的服务器

Apache*, 可以如下配置:

```
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

对于 *Nginx*:

```
server {
    location / {
        try_files $uri $uri/ /index.php;
    }
}
```