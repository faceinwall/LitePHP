<?php 
require 'lite/Lite.php';

$app = new lite\App();

$app->addRoute('/', function(){
    $body = "<h4>index page</h4>";
    return $body;
});

$app->addRoute('/hello', function(){
    return  "hello, world";
});

$app->addRoute('/app/@id:[1-9]{1,3}', function($id = ''){
    return $id;
});

class ExampleController {
    public function handle() {
        print "example";
    }
}

$app->addRoute("/handle",[new ExampleController, 'handle']);
$app->run();