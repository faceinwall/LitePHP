###关于LitePHP
LitePHP是一个简单, 轻量, 快速, 可扩展的框架.它可以让快速的建一个REST风格的应用程序.

```php
require 'lite/Lite.php';

$app->addRoute('/', function(){
	
	$this->body = "<h4>hello world!</h4>";

	$this->response();
});
```