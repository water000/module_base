<?php
/*
 * @depend-lib: string array Exception PDO CPDODebug
 */
 
require_once dirname(__FILE__).'/IDebugOutput.php';
require_once dirname(__FILE__).'/CPDODebug.php';

class CDbPool implements IDebugOutput
{
	/**
	 * @desc HOST与PORT之间的分隔符
	 * @var string
	 */
	const SEP_HOST_PORT = '_';
	
	/**
	 * @desc PDO 的类名
	 * @var string
	 */
	CONST CLASS_PDO = 'PDO';
	
	/**
	 * @desc PDODebug 的类名
	 * @var string
	 */
	CONST CLASS_PDODEBUG = 'CPDODebug';
	
	private static $oInstance = null;
	private static $charset = '';
	
	private $arrConn = array();
	private $arrConnDebug = array();
	private $sClassName = self::CLASS_PDO;
	
	
	/**
	 * @desc all db instance conf(pool), like this"
	 * private $arrConfig = array(
		'127.0.0.1_3307_dbname' => array('username'=>'root', 'pwd'=>'123123'),
		'localhost_3307_dbname' => array('username'=>'root', 'pwd'=>'123123')
	);"
	NOTE,_REP_DB_CFG_POLL will be replaced with the mysql instance poll at installtion, so it was not defined in all
	 * @var all
	 */
	private static $arrConfig = array();
	
	private function __construct()
	{
	}
	
	static function setConf($arr){
		self::$arrConfig = array_merge(self::$arrConfig, $arr);
	}
	
	static function setCharset($charset){
		self::$charset = $charset;
	}
	
	static function appendConf($host, $port, $dbname, $user, $pwd){
		$key = sprintf('%s%s%d%s%s', $host, self::SEP_HOST_PORT, 
			$port, self::SEP_HOST_PORT, $dbname);
		if(isset(self::$arrConfig[$key])){
			if(self::$arrConfig[$key]['username'] != $user
				|| self::$arrConfig[$key]['pwd'] != $pwd )
				return false;
			else
				return true;
		}
		self::$arrConfig[$key] = array('username'=>$user, 'pwd'=>$pwd);
		return true;
	}
	
	function html()
	{
		echo '<div style="padding:5px;margin:10px;font-size:14px;">';
		$queryNum = 0;
		$style = '';
		$cost = 0;
		foreach($this->arrConnDebug as $sDbInfo => $rConn)
		{
			$arrLog = $rConn->getLog();
			$cost += $rConn->getTotalExec();
			echo '<fieldset><legend><i><b>'.$sDbInfo.'</b></i></legend><table style="width:100%;">';
			$output = array();
			$subQueryNum = 0;
			foreach($arrLog as $arr)
			{
				$output = array(array($arr[0], $arr[1], $arr[2]));
				if(isset($arr[3]))
				{
					$output = array_merge($output , $arr[3]);
					$subQueryNum += count($arr[3]);
				}
				else
				{
					++$subQueryNum;// the prepare was not included 
				}
				$func = $res = '';
				$stmt = '<ul>';
				foreach($output as $tmp)
				{
					$func .=  $tmp[0].'<br/>';
					$stmt .= '<li>'. str_replace(array("\n"), array('<br/>'), htmlspecialchars($tmp[1])).'</li>';
					$res .= (is_numeric(substr($tmp[2], 0, 6)) ? $tmp[2] : '<font color=red>'.htmlspecialchars($tmp[2]).'</font>').'<br/>';
				}
				$stmt .= '</ul>';
				//$style = intval($key) == $len-1 ? '' : 'style="border-bottom:1px solid #ddd;"';
				$style = 'style="border-bottom:1px solid #ddd;"';
				echo '<tr><td '.$style.'>'.$func
				.'</td><td '.$style.'>'.$stmt
				.'</td><td '.$style.'>'
				.(is_numeric(substr($res, 0, 6)) ? $res : '<font color=red>'.$res.'</font>').'</td></tr>';
			}
			$queryNum += $subQueryNum;
			echo '<tr><td></td><td></td><td><b>'.$subQueryNum.'</b> quer(ies), cost <b>'.$rConn->getTotalExec().'</b>(s)</td></tr></table></fieldset>';
		}
		echo '<div style="padding:10px 0;text-align:center;">--------total: <b>'.$queryNum.'</b> quer(ies), cost <b>'.$cost.'</b> s ------</div></div>';
	}
	
	function xml()
	{
		echo '<dbdebug><![CDATA[', $this->html() , ']]></dbdebug>';
	}
	
	function cli()
	{
		$queryNum = 0;
		foreach($this->arrConnDebug as $sDbInfo => $rConn)
		{
			$arrLog = $rConn->getLog();
			echo $sDbInfo , "\n";
			foreach($arrLog as $arr)
			{
				$output = array(array($arr[0], $arr[1], $arr[2]));
				if(!empty($arr[3]))
				{
					$output = array_merge($output , $arr[3]);
					$queryNum += count($arr[3]);
				}
				foreach($output as $arr)
				{
					echo sprintf("\t%20s%60s | %s\n", $arr[0], $arr[1], $arr[2]);
				}
			}
			$queryNum += count($arrLog);
		}
		echo sprintf("total: %d quer(ies), cost:%f\n", $queryNum, $rConn->getTotalExec());
	}
	
	/**
	 * @desc 获取CDbPool的实例
	 * @return instance of CDbPool
	 */
	public static function getInstance()
	{
		if(empty(self::$oInstance))
		{
			self::$oInstance = new CDbPool();
		}
		return self::$oInstance;
	}
	
	/**
	 * @desc 设置当前的PDO类名，默认为'pdo'
	 * @param string $sClassName
	 * @return null
	 */
	public function setClass($sClassName=self::CLASS_PDO)
	{
		$this->sClassName = $sClassName;
	}
	
	public static function getFormatedConfItem($host, $port, $dbname, $username, $pwd)
	{
		return sprintf("'%s%s%d%s%s' => array('username'=>'%s', 'pwd'=>'%s')"
			, $host, self::SEP_HOST_PORT, $port, self::SEP_HOST_PORT, $dbname, $username, $pwd);
	}
	
	/**
	 * @desc 判断指定的host, prot, dbname是否存在列表中
	 * @param string $host
	 * @param string $port
	 * @param string $dbname
	 * @return true on exists, false otherwise
	 */
	public static function hasConfExists($host, $port, $dbname)
	{
		return array_key_exists($host.self::SEP_HOST_PORT.$port.self::SEP_HOST_PORT.$dbname, self::$arrConfig);
	}
	
	/**
	 * @desc 获取数据库配置列表
	 * @return array
	 */
	public static function getHostList()
	{
		return self::$arrConfig;
	}
	
	/**
	 * @desc 获取默认的数据库配置
	 * @return a string that like 'host_port_dbname'
	 */
	public function getDefaultConf()
	{
		return key(self::$arrConfig);
	}
	
	/**
	 * @desc 获取默认的数据库连接对象。注：此方法会抛出PDOException异常
	 * @exception PDOException
	 * @return a instance of pdo
	 */
	public function getDefaultConnection()
	{
		if(empty(self::$arrConfig))
			return null;
		
		list($host, $port, $dbname) = explode(self::SEP_HOST_PORT, key(self::$arrConfig), 3);
		try
		{
			$ret = $this->getConnection($host, $port, $dbname);
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		return $ret;
	}
	
	/**
	 * @desc 获取指定参数的数据库连接对象。注：此方法会抛出PDOException异常
	 * @param string $sHost 主机名, 默认为localhost
	 * @param string $port 端口号， 默认为3306
	 * @param string $sDbName 数据库名称, 默认为self::DEF_DB_NAME
	 * @return 如果存在此配置，则返回相应连接，否则返回FALSE
	 */
	public function getConnection($sHost, $port, $sDbName)
	{
		if(empty($sHost) || empty($port) || empty($sDbName))
			return null;
		
		if('127.0.0.1' == $sHost)
			$sHost = 'localhost';
		$sKey = $sHost.self::SEP_HOST_PORT.$port.self::SEP_HOST_PORT.$sDbName;
		if(!isset(self::$arrConfig[$sKey]))
			return false;
			
		$prekey = $this->sClassName.$sKey;
		if(isset($this->arrConn[$prekey]))
			return $this->arrConn[$prekey];
			
		$conf = self::$arrConfig[$sKey];
		$sDSN = sprintf('mysql:host=%s;port=%d;dbname=%s;', 
			$sHost, $port, $sDbName);
		$obj = null;
		try
		{
			if($this->sClassName == self::CLASS_PDODEBUG)
				$this->arrConnDebug[$sKey] = $obj =
					 new CPDODebug($sDSN, $conf['username'], $conf['pwd']);
			else
				$obj = new PDO($sDSN, $conf['username'], $conf['pwd']);
				
			if(!empty(self::$charset))
				$obj->query('set names '.str_replace('-', '',self::$charset));
		}
		catch(PDOException $e)
		{
			throw $e;
		}
		$this->arrConn[$prekey] = $obj;
		return $obj;
	}
}
?>