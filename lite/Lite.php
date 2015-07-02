<?php 
/**
 * lite
 *
 * An open source application development framework for PHP 5.4.16 or newer
 *
 * @package		lite	
 * @author		Linhaoye
 * @copyright	Copyright (c) 2015 - ?, eLab, Inc.
 * @license		MIT license
 * @link		no link
 * @since		Version 1.0
 * @filesource
 */

namespace lite;

// ------------------------------------------------------------------------

/**
 * App Class
 *
 * The application instance
 *
 * @package		lite
 * @author		Linhaoye
 */
class App
{

	/**
	 * app instance
	 * @var object
	 */
	private static $instance = null;

	/**
	 * route list
	 *
	 * @var array
	 * @access protected
	 *
	 */
	protected $routes = [];

	/**
	 * http status code
	 * 
	 * @access protected
	 * @var string
	 * 
	 */
	protected $status = '200 OK';

	/**
	 * return document type
	 *
	 * @access protected
	 * @var string 
	 */
	protected $contenttype = 'text/html';

	/**
	 * return document content
	 * 
	 * @access protected
	 * @var string
	 * 
	 */
	protected $body = '';

	/**
	 * enable pathinfo or not
	 * 
	 * @access protected
	 * @var boolean
	 * 
	 */
	protected $pathinfo = true;

	/**
	 * __construct 
	 */
	public function __construct()
	{
		self::$instance = $this;
	}

// ------------------------------------------------------------------------
	public static function behavior(&$object)
	{

	}

// ------------------------------------------------------------------------

	/**
	 * 505 error
	 * 
	 * @access public
	 * @param  \Exception $e exception
	 *
	 */
	public function _error(\Exception $e)
	{
		$this->status = '500 Internal Server Error';
		$this->body = sprintf('<h1>500 internal Server Error</h1>'.
			'<h3>%s (%s)</h3>'.
			'<pre>%s</pre>',
			$e->getMessage(),
			$e->getCode(),
			$e->getTraceAsString());

		$this->response();
	}

// ------------------------------------------------------------------------

	/**
	 * 404 error
	 * 
	 * @access public 
	 * 
	 */
	public function _notFound()
	{
		$this->status = '404 Not Found';
		$this->body = '<h1 style="text-align:center;">404 Not Found!</h1>';
		$this->response();
	}

// ------------------------------------------------------------------------

	/**
	 * the usage tips for cli mode
	 * 
	 * @access public
	 */
	public function _cliUsage()
	{
		$usage = [
			"The framework cli usage:\n",
			"\t--route <route>              the route uri string\n",
			"\t--params <query_string>      set \$_GET parameters\n",
		];

		foreach ($usage as $line)
		{
			print $line;
		}
	}

// ------------------------------------------------------------------------

	/**
	 * output response
	 * 
	 * @access  protected
	 */
	protected function response()
	{
		//cli mode
		if (!(php_sapi_name() === 'cli'))
		{
			header('HTTP/1.1 ' . $this->status);
			header('Content-type: ' . $this->contenttype);
			header('Content-length: ' . mb_strlen($this->body));
		}

		print $this->body;
	}

// ------------------------------------------------------------------------
	/**
	 * enable pathinfo mode or not
	 * 
	 * @access  public
	 * @param  boolean $boolean false|true
	 *
	 */
	public function enablePathinfo($boolean = false)
	{
		$this->pathinfo = $boolean;
	}

// ------------------------------------------------------------------------

	/**
	 * invoke a function
	 *  
	 * @access  public
	 * @param  callback 	$func 		callback function
	 * @param  array  		&$params 	the callback parameters
	 * @return mixed 
	 *
	 */
	public static function executeFunc($func, array &$params = [])
	{
		switch (count($params))
		{
			case 0:
				return $func();
			case 1:
				return $func($params[0]);
			case 2:
				return $func($params[0], $params[1]);
			case 3:
				return $func($params[0], $params[1], $params[2]);
			case 4:
				return $func($params[0], $params[1], $params[2], $params[3]);
			case 5:
				return $func($params[0], $params[1], $params[2], $params[3], $params[4]);
			default:
				return call_user_func_array($func, $params);
		}
	}

// ------------------------------------------------------------------------

	/**
	 * invoke a method from a class or object
	 *
	 * @access  public
	 * @param  callback 	$func 		callback handler 
	 * @param  array 		&$params 	callback parameters
	 * @return mixed
	 *
	 */
	public static function executeMethod($func, array &$params = [])
	{
		list($class, $method) = $func;

		$instance = is_object($class);

		switch (count($params))
		{
			case 0:
				return $instance ?
					$class->$method():
					$class::$method();
			case 1:
				return $instance ?
					$class->$method($params[0]):
					$class::$method($params[1]);
			case 2:
				return $instance ?
					$class->$method($params[0], $params[1]):
					$class::$method($params[0], $params[1]);
			case 3:
				return $instance ?
					$class->$method($params[0], $params[1], $params[2]):
					$class::$method($params[0], $method[1], $method[2]);
			case 4:
				return $instance ?
					$class->$method($params[0], $params[1], $params[2], $params[3]):
					$class::$method($params[0], $method[1], $method[2], $params[3]);
			case 5:
				return $instance ?
					$class->$method($params[0], $params[1], $params[2], $params[3], $params[4]):
					$class::$method($params[0], $method[1], $method[2], $params[3], $params[4]);
			default:
				return call_user_func($func, $params);
		}
	}


// ------------------------------------------------------------------------

	/**
	 * add a route to queue
	 *
	 * @access  public
	 * @param string 	$routePath     route path
	 * @param callback 	$routeCallback callback function
	 *
	 */
	public function addRoute($routePath, $routeCallback)
	{
		if (is_array($routeCallback))
		{
			$this->routes[$routePath] = is_callable($routeCallback) ? $routeCallback: [$this, '_error'];
		}
		else
		{
			$this->routes[$routePath] = $routeCallback->bindTo($this, __CLASS__);
		}
	}

// ------------------------------------------------------------------------

	/**
	 * router
	 * 
	 * @access public
	 * @param  string 	$uri 	route string
	 *
	 */
	public function route($uri)
	{
		$found = false;

		foreach ($this->routes as $routePath => $callback)
		{
			$rte = new Rte($routePath, false);

			if ($rte->matchUrl($uri))
			{
				$params = array_values($rte->params);

				if (is_array($callback) && is_callable($callback))
				{
					$this->executeMethod($callback, $params);
				}
				else
				{
					$this->executeFunc($callback, $params);
				}

				$found = true;

				break;
			}
		}

		if ($found == false) $this->_notFound();

	}

// ------------------------------------------------------------------------

	/**
	 * get route string
	 *
	 * @access  public
	 * @param  string $default set default route
	 * @return string
	 * 
	 */
	public function getUri($default = '/')
	{
		$uri = "";

		if (php_sapi_name() === 'cli')
		{
			global $argv;

			$cnt = count($argv);
			$arguments = array_slice($argv, 1);

			if ($cnt < 3)
			{
				$this->_cliUsage();
				exit(1);
			}

			$i = 0;
			$lastval = '';
			$retval = [];

			foreach ($arguments as $arg)
			{
				if ($i % 2 )
				{
					$retval[$lastval] = $arg;
				}
				else
				{
					$retval[$arg] = false;
					$lastval = $arg;
				}

				$i++;
			}

			$getarg = function ($v)
			{
				if (empty($v))
				{
					$this->_cliUsage; exit(1);
				}

				return $v;
			};

			$getarg->bindTo($this, __CLASS__);

			foreach ($retval as $k => $v)
			{ 
				switch ($k)
				{
					case '--route':
						$uri = $getarg($v);
						break;

					case '--params':
						$params = parse_str($getarg($v));
						break;

					default:
						$this->_cliUsage(); exit(1);
						break;
				}
			}
		}
		else
		{
			if ($this->pathinfo == false)
			{
				isset($_GET['r']) && $_GET['r'] && '/'.ltrim($_GET['r'], '/');
			}
			else
			{
				if (isset($_SERVER['PATH_INFO']))
				{
					$uri = $_SERVER['PATH_INFO'];
				}
				// when web server does not support pathinfo
				else
				{
					$uri = str_replace( $_SERVER['QUERY_STRING'], '' ,$_SERVER['REQUEST_URI']);
				}
			}
		}

		return $uri ? $uri : $default;
	}	

// ------------------------------------------------------------------------

	/**
	 * run a application instance
	 * 
	 * @access public
	 *
	 */
	public function run()
	{
		$this->route($this->getUri());
	}
}

// ------------------------------------------------------------------------

/**
 * Rte Class
 *
 * match the url
 *
 * @package		src	
 * @author		Linhaoye
 */
class Rte
{
	/**
	 * string URL pattern
	 * 
	 * @var string
	 * 
	 */
	public $pattern;

	/**
	 * route parameters
	 * 
	 * @var array
	 *
	 */
	public $params = [];

	/**
	 * string mathching regular express
	 * 
	 * @var string
	 * 
	 */
	public $regex;

	/**
	 * pass self in callback parameters whether or not
	 *
	 * @var boolean
	 *
	 */
	public $pass = false;

	/**
	 * url splat content
	 * 
	 * @var string
	 * 
	 */
	public $splat = '';

// ------------------------------------------------------------------------

    /**
     * Constructor.
     *
     * @access  public
     * @param string 	$pattern 	URL pattern
     * @param mixed 	$callback 	Callback function
     * @param array 	$methods 	HTTP methods
     * @param boolean 	$pass 		Pass self in callback parameters
     *
     */
	public function __construct($pattern, $pass)
	{
		$this->pattern = $pattern;
		$this->pass = $pass;
	}


// ------------------------------------------------------------------------

    /**
     *
     * Checks if a URL matches the route pattern. Also parses named parameters in the URL.
     *
     * @access  public
     * @param 	string 		$url Requested URL
     * @return 	boolean 	Match status
     *
     */
	public function matchUrl($url)
	{
		if ($this->pattern === '*' || $this->pattern === $url)
		{
			if ($this->pass){
				$this->params[] = $this;
			}
			return true;
		}

		$ids = [];

		$last_char = substr($this->pattern, -1);

		if ($last_char === '*')
		{
			$n = 0;
			$len = strlen($url);
			$cnt = substr_count($this->pattern, '/');

			for ($i = 0; $i < $len; $i++)
			{
				if ($url[$i] == '/') $n++;
				if ($n == $cnt) break;
			}
			$this->splat = (string)substr($url, $i + 1);
		}

		$regex = str_replace([')', '/*'], [')?','(/?|/.*?)'], $this->pattern);

		$regex = preg_replace_callback(
			'#@([\w]+)(:([^/\(\)]*))?#',
			function($matches) use(&$ids)
			{
				$ids[$matches[1]] = null;
				if (isset($matches[3]))
				{
					return '(?P<'.$matches[1].'>'.$matches[3].')';
				}

				return '(?P<'.$matches[1].'>[^/\?]+)';
			},
			$regex);


		$regex .= ($last_char === '/') ? '?': '/?';

		if (preg_match('#^'.$regex.'(?:\?.*)?$#i', $url, $matches))
		{
			foreach ($ids as $k => $v)
			{
				$this->params[$k] = (array_key_exists($k, $matches)) ? urldecode($matches[$k]) : null;		
			}

			if ($this->pass)
			{
				$this->params[] = $this;
			}

			$this->regex = $regex;

			return true;
		}

		return false;
	}
}
 ?>