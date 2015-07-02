<?php 
require 'lite/Lite.php';

$app = new lite\App();

$app->addRoute('/', function(){
	
	$this->body = "<h4>hello world!</h4>";

	$this->response();
});

$app->addRoute('/user/wang', function(){

	$this->body = "<h2>wang xiao gou</h2>";

	$this->response();
});


$app->addRoute('/app/@id:[1-9]{1,3}', function($id = ''){
	var_dump($id);
});

class UserController {
	public function login(){
		App::behavior($this);

		$this->body = <<<EOF
<html>
<head>
	<title>wang xiao login panel</title>
</head>
<body>
	<form method="post" action="/login">
		<div>
			<label>user:</label>
			<input type="text" name="user" />
		</div>

		<div>
			<label>pass:</lable>
			<input type="text" name="pass" />
		</div>

		<div>
			<input type="submit" value="sub"/>
		</div>
	</form>
</body>
</html>
EOF;
	
		if (isset($_POST['user']))
		{
			var_dump($_POST);exit(0);	
		}

		print $this->body;
	}

}

$app->addRoute("/login",[new UserController, 'login']);

// $app->run();


$arr = [
	'index'=>5,
	'cate' => 'san frna',
];

class db
{
	/**
	 * factory
	 *
	 * Sets an DataBaseBinding compatible object based on the type of database
	 * defined in $config_values['database']['db_type']
	 */

	public $conn;

	public function __construct()
	{
		$config = new config; 
		$db_type = strtolower( $config->config_values['database']['db_type'] );

		switch( $db_type )
		{
			case 'pgsql':
			case 'postgresql':
			case 'mysql':
				$config		= new config;
				$db_type	= $config->config_values['database']['db_type'];
				$hostname	= $config->config_values['database']['db_hostname'];
				$dbname		= $config->config_values['database']['db_name'];
				$db_password= $config->config_values['database']['db_password'];
				$db_username= $config->config_values['database']['db_username'];
				$db_port	= $config->config_values['database']['db_port'];

				$this->conn = new \PDO( "$db_type:host=$hostname;port=$db_port;dbname=$dbname", $db_username, $db_password );
				$this->conn-> setAttribute( \PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION );
			break;

			case 'sqlite':
				try{
				$path = APP_PATH . $config->config_values['database']['db_path'].'/'.$config->config_values['database']['db_name'].'.sq3';
				$this->conn = new \PDO( "sqlite:$path" );
				$this->conn-> setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				}
				catch( \PDOException $e )
				{
					echo $path;
				}
			break;

			default:
			throw new Exception('Database type not supported');
		}
	}
}

var_dump(compact('arr'));
 ?>