<?php
// Brickyard framework
// @version 1.1.dev
class brickyard
{
	public $inDevelMode = false;
	
	public $router = null;
	
	public $view = null;
	
	public $logger = null;
	
	public $libPath = '.';
	
	public $indexPath = '.';
	
	public $throwTheseErrors = E_ALL;
	
	protected $_instance = array();
	protected $_factory = array();
	
	public function __construct(){
		$this->router = new brickyard_router_default;
		$this->logger = new brickyard_logger_null;
		$this->libPath = dirname(__FILE__);
		$this->view = new brickyard_view_default(dirname(__FILE__) . DIRECTORY_SEPARATOR . "tpl");
	}
	
	public function getRouter()
	{
		return $this->router;
	}
	
	public function getView()
	{
		return $this->view;
	}
	
	public function getLogger()
	{
		return $this->logger;
	}
	
	public function getIndexPath()
	{
		return $this->indexPath;
	}
	
	public function getDependency($name)
	{
		if (isset($this->_factory[$name])) {
			$args = func_get_args();
			$args[0] = $this;
			return call_user_func_array($this->_factory[$name],$args);
		} elseif (isset($this->_instance[$name])) {
			return $this->_instance[$name];
		} else {
			throw new Exception('Cannot find dependency called ' . $name);
		}
	}
	
	public function setRouter(brickyard_router_interface $router)
	{
		$this->router = $router;
	}
	
	public function setView(brickyard_view_interface $view)
	{
		$this->view = $view;
	}
	
	public function setLogger(brickyard_logger_interface $logger)
	{
		$this->logger = $logger;
	}
	
	public function setIndexPath($indexFilePath)
	{
		$this->indexPath = dirname($indexFilePath);
	}
	
	public function setInstance($name, $data)
	{
		$this->_instance[$name] = $data;
	}
	
	
	public function autoload($className){
		$filename=$this->libPath . DIRECTORY_SEPARATOR;
		$filename.=str_replace("_", DIRECTORY_SEPARATOR, $className);
		$filename.=".php";
		if (file_exists($filename)){
			require $filename;
			if (!class_exists($className, false) && !interface_exists($className, false)){
				throw new brickyard_exception_autoload('Class ' . $className . ' expected to be in ' . $filename . '!');
			}
		} else {
			throw new brickyard_exception_autoload('Class ' . $className . ' not found! Tried to find it in ' . $filename . '.');
		}
		
	}
	
	function error_handler($errno, $errstr, $errfile, $errline )
	{
		if ($this->throwTheseErrors & $errno) {
			throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
		}
	}
	
	public function exception_handler($e)
	{
		if ($this->inDevelMode){
			$this->bluescreen($e);
		} else {
			$this->logger->logException($e);
			if ($e instanceof brickyard_exception_404){
				$err = 404;
				$status = 404;
			} elseif ($e instanceof brickyard_exception_403){
				$err = 403;
				$status = 403;
			} else {
				$status  = 500;
				$err = 'error';
			}	
			$messages = array(404=>'Not Found', 403=>'Forbidden', 500=>'Internal Server Error');
			if (file_exists($this->libPath . DIRECTORY_SEPARATOR . $err . '.html')){
				ob_clean();
				//header($messages[$status], 1, $status);
				echo file_get_contents($this->libPath . DIRECTORY_SEPARATOR . $err . '.html');
				exit; //to prevent more errors
			}else{
				echo "An error occured. Also error page is missing.";
			}
			
		}
		
	}
	
	public function shutdown_handler()
	{
		$error = error_get_last();
		if ($error['type'] & (E_ERROR | E_CORE_ERROR | E_COMPILE_ERROR | E_PARSE)) {
			$fatal = new ErrorException($error['message'], $error['type'], 0, $error['file'], $error['line']);
			$this->exception_handler($fatal);
		}
		
	}
	
	public function bluescreen($e)
	{
		ob_clean();
		$out="<html><head><title>error</title></head><body><h1>:-(</h1>";
		$out.="<div>" . get_class($e). " at " . $e->getFile() . ':' . $e->getLine() . "</div>";
		$out.="<div>" . nl2br( htmlspecialchars( $e->getMessage() ) ) . "</div>";
		$out.="<pre>" . $e->getTraceAsString() . "</pre>";
		$out.="</body></html>";
		echo $out;
		exit;
	}
	
	public function init()
	{
		spl_autoload_register(array($this,"autoload"));
		set_error_handler(array($this,"error_handler"));
		register_shutdown_function(array($this, "shutdown_handler"));
		set_exception_handler(array($this,"exception_handler"));
		ob_start();
	}
	
	public function run()
	{
		$controllerName = "c_" . $this->router->getController();
		$methodName = $this->router->getMethod();
		$args = $this->router->getArgs();
		try {
			$controllerInstance = new $controllerName;
		} catch(brickyard_exception_autoload $e) {
			throw new brickyard_exception_404($e->getMessage() );
		}
		$controllerInstance->framework=$this;
		$call=array($controllerInstance, $methodName);
		if (is_callable($call)){
			call_user_func_array($call,$args);
		}else{
			throw new brickyard_exception_404('Method ' . $methodName . ' is invalid!');
		}
		
	}
}

class brickyard_exception_autoload extends Exception{}

class brickyard_exception_404 extends Exception{}

class brickyard_exception_403 extends Exception{}

interface brickyard_router_interface
{

	public function getController();

	public function getMethod();
	
	public function getArgs();
	
	public function getLink($controller = null, $method = null, $args=array() );
}

class brickyard_router_default implements brickyard_router_interface
{
	public $controller = "home";

	public $method = "index";

	public $args = array();

	function analyze()
	{
		$path=( isset($_SERVER["PATH_INFO"]) ? explode("/",$_SERVER["PATH_INFO"]) : array() );
		if (count($path)>1 and $path[1]!=''){$this->controller=$path[1];}
		if (count($path)>2  and $path[2]!=''){$this->method=$path[2];}
		if (count($path)>3){$this->args=array_slice($path,3);}
	}

	public function getController()
	{
		$this->analyze();
		return $this->controller;
	}

	public function getMethod()
	{
		$this->analyze();
		return $this->method;
	}

	public function getArgs()
	{
		$this->analyze();
		return $this->args;
	}

	public function getLink($controller = null, $method = null, $args=array() )
	{
		$url = $_SERVER["SCRIPT_NAME"];
		if ($controller){
			$url .= '/' . $controller;
			if ($method){
				$url .= '/' . $method;
				if (count($args)>0){
					$url .= '/' . implode('/', $args);
				}
			}
		}
		return $url;
	}
}

interface brickyard_view_interface
{
	public function show($templateName, array $data);
}

class brickyard_view_default implements brickyard_view_interface
{
	private $tplPath="tpl";

	function __construct($tplPath)
	{
		$this->tplPath = $tplPath;
	}

	public function show($tplName, array $data)
	{
		$tplFile = $this->tplPath . DIRECTORY_SEPARATOR . $tplName . ".php";
		if (file_exists($tplFile)) {
			$data['view'] = $this;
			extract($data, EXTR_SKIP);
			ob_start();
			include $tplFile;
			$output = ob_get_contents();
			ob_end_clean();
			return $output;
		} else {
			throw new Exception('Template '.$tplName.' not found in file '.$tplFile);
		}
	}
}

interface brickyard_logger_interface
{
	public function logException(Exception $e);
}

class brickyard_logger_null implements brickyard_logger_interface
{
	function logException(Exception $e) {}
}

class brickyard_logger_file implements brickyard_logger_interface
{
	private $logFileName="log.txt";

	function __construct($logFileName)
	{
		$this->logFileName = $logFileName;
	}

	function logException(Exception $e)
	{
		$logged = '== ' . date('Y-m-d H:i:s') . PHP_EOL .
		$e->getMessage() . PHP_EOL .
		$e->getFile() . ':' . $e->getLine() . PHP_EOL .
		$e->getTraceAsString() . PHP_EOL;
		file_put_contents($this->logFileName, $logged, FILE_APPEND);
	}
}