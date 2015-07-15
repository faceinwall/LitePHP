<?php 
namespace lite;

/**
 * Db 
 *
 * An open source application development framework for PHP 5.4.16 or newer
 *
 * @package 	lite	
 * @author		Linhaoye
 * @copyright	Copyright (c) 2015 - ?, eLab, Inc.
 * @license		MIT license
 * @link		no link
 * @since		Version 1.0
 * @filesource
 */

// ------------------------------------------------------------------------

/**
 * Db Class
 *
 * The instance db
 *
 * @package		lite
 * @author		Linhaoye
 */

class Db
{
	/**
	 * factory
	 *
	 * Sets an DataBaseBinding compatible object based on the type of database
	 * defined in array $dsn 
	 * @var PDO
	 *
	 */
	public $conn;

// ------------------------------------------------------------------------

	/**
	 * @construct
	 *
	 * @param array $dsn
	 *
	 */
	public function __construct(array $dsn)
	{
		$db_type = @strtolower($dsn['db_type']);

		switch($db_type)
		{
			case 'pgsql':
			case 'postgresql':
			case 'mysql':
				$db_type  = isset($dsn['db_type'])  ? $dsn['db_type']: '';
				$hostname = isset($dsn['hostname']) ? $dsn['hostname']: '';
				$database = isset($dsn['database']) ? $dsn['database']: '';
				$username = isset($dsn['username']) ? $dsn['username']: '';
				$password = isset($dsn['password']) ? $dsn['password']: '';
				$db_port  = isset($dsn['db_port'])  ? $dsn['db_port']: '';
				$charset  = isset($dsn['charset'])  ? $dsn['charset']: 'utf8';

				try
				{
					$this->conn = new \PDO("$db_type:host=$hostname;port=$db_port;dbname=$database;charset=$charset", $username, $password);
					$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				}
				catch(\PDOException $e)
				{
					print "database configuration:\n";
					var_dump($dsn);
					die($e->getMessage());
				}

				break;

			case 'sqlite':
				try
				{
					$path = isset($dsn['sqlite_path']) ? $dsn['sqlite_path']: '';

					$this->conn = new \PDO("sqlite:$path");
					$this->conn->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
				}
				catch(\PDOException $e)
				{
					print "sqlite path: $path\n";
					die($e->getMessage());
				}

				break;

			default:
				throw new \Exception("Database type not supported");
		}
	}
}
 ?>