<?php

class CPrivUserControl extends CUniqRowControl {
	private static $instance = null;
	
	protected function __construct($db, $cache, $primarykey = null){
		parent::__construct($db, $cache, $primarykey);
	}
	
	/**
	 *
	 * @param CAppEnvironment $mbs_appenv
	 * @param CDbPool $dbpool
	 * @param CMemcachePool $mempool
	 * @param string $primarykey
	 */
	static function getInstance($mbs_appenv, $dbpool, $mempool, $primarykey = null){
		if(empty(self::$instance)){
			try {
				$memconn = $mempool->getConnection();
				self::$instance = new CPrivUserControl(
						new CUniqRowOfTable($dbpool->getDefaultConnection(),
								mbs_tbname('priv_user'), 'user_id', $primarykey),
						$memconn ? new CUniqRowOfCache($memconn, $primarykey, 'CPrivUserControl') : null,
						$primarykey
				);
			} catch (Exception $e) {
				throw $e;
			}
		}
		return self::$instance;
	}
}

?>