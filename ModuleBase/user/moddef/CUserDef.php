<?php

class CUserDef extends CModDef {
	protected function desc() {
		return array(
			self::MOD => array(
				self::G_NM=>'user',
				self::M_CS=>'utf-8',
				self::G_TL=>'用户信息',
				self::G_DC=>'提供用户的基本信息，包括注册，登录，认证，修改信息等'
			),
			self::FTR => array(
				'checkLogin' =>array(self::G_CS=>'CUserSession', self::G_DC=>'检查用户是否登录'),
				'checkDepLogin' =>array(self::G_CS=>'CUserDepSession', self::G_DC=>'检查用户是否登录相应的部门')
			),
			self::TBDEF => array(
				'user_info' => '(
					id                   int unsigned auto_increment not null,
				    name                 varchar(16),
				    password             char(38),
				    organization         varchar(32),
				    phone                char(11),
				    email                varchar(255),
				    IMEI                 varchar(32),
				    IMSI                 varchar(32),
				    VPDN_name            varchar(32),
				    VPDN_pass            varchar(32),
				    class_id             int unsigned,
				    reg_time             int unsigned,
				    reg_ip               varchar(32),
					pwd_modify_count     int unsigned,
					primary key(id),
					unique key(phone)
				)',
				/*'user_third_platform' => '(
					user_id int unsigned not null,
					plat_id varchar(33) not null, -- value=([1:weixin, 2:weibo, ...])+returned-id-by-third-platform
					unique key(plat_id),
					key(user_id)
				)',*/
				'user_mobile_device' => '(
					user_id int unsigned not null,
					os_type tinyint not null, -- 1:ios, 2:android, 3:...
					token varchar(32) not null,
					last_submit_ts int unsigned not null, -- last submit device token timestamp
					unique key(user_id)
				)',
				'user_class' => '(
					id                   int unsigned auto_increment not null,
					name                 varchar(16),
					code                 varchar(32),
					create_time          int unsigned,
					primary key (id),
					unique key(name),
					unique key(code)
				)',
				'user_department' => '(
					id                   int unsigned auto_increment not null,
					name           		 varchar(16),
					password             char(32),
					edit_time            int unsigned,
					primary key (id),
					unique key(name)
				)',
				'user_department_member' => '(
					id                   int unsigned auto_increment not null,
					user_id              int unsigned,
					dep_id               int unsigned,
					join_time            int unsigned,
					primary key (id),
					unique key(user_id),
					key(dep_id)
				)',
				'user_login_log'        => '(
					 user_id              int unsigned not null,
				   	 token                char(32),
				     time                 int unsigned,
				     primary key (user_id)
				)',
			),
			self::PAGES => array(
				'login' => array(
					self::P_TLE => '登录',
					self::G_DC  => '用户登录系统的入口',
					self::P_ARGS => array(
						'phone'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'11, 16'),
						'password'      => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 32'),
						'captcha'       => array(self::PA_REQ=>0, self::G_DC=>'第一次登录没有验证码，当登录失败后会出现，客户端需要做相应处理', self::PA_RNG=>'4,5'),
						'remember_me'   => array(self::PA_REQ=>0, self::G_DC=>'记住我！延长用户登录的有效期'),
						'IMEI'          => array(self::PA_REQ=>0, self::G_DC=>'当系统中记录时，需要提供。主要用于检测移动设备的有效性'),
						'IMSI'          => array(self::PA_REQ=>0, self::G_DC=>'同IMEI'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{user:{详见user_info表中字段列表}, token:32为的字符串}',
				),
				'logout' => array(
					self::P_TLE => '注销',
					self::G_DC  => '注销当前已登录的用户',
					self::P_OUT => '{retcode:"SUCCESS"}'
				),
				'edit' => array(
					self::P_TLE => '编辑',
					self::G_DC  => '编辑用户信息',
					self::P_MGR => true,
					self::P_NCD => true,
					self::P_ARGS => array(
						'name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2, 17'),
						'password'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6, 17'),
						'organization' => array(self::PA_REQ=>0, self::G_DC=>'单位', self::PA_RNG=>'2, 32'),
						'phone'        => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'手机', self::PA_RNG=>'11, 12'),
						'email'        => array(self::PA_REQ=>0, self::G_DC=>'邮箱', self::PA_RNG=>'6, 255'),
						'IMEI'         => array(self::PA_REQ=>0, self::G_DC=>'IMEI', self::PA_RNG=>'6, 32'),
						'IMSI'         => array(self::PA_REQ=>0, self::G_DC=>'IMSI', self::PA_RNG=>'6, 32'),
						'class_id'     => array(self::PA_REQ=>0, self::G_DC=>'分类id'),
						'VPDN_name'    => array(self::PA_REQ=>0, self::G_DC=>'VPDN名称', self::PA_RNG=>'6, 32'),
						'VPDN_pass'    => array(self::PA_REQ=>0, self::G_DC=>'VPDN密码', self::PA_RNG=>'6, 32'),
						'class_id'     => array(self::PA_REQ=>0, self::G_DC=>'分类id', self::PA_TYP=>'integer', self::PA_RNG=>'1, 4'),
					),
				),
				'list' => array(
					self::P_TLE => '用户管理',
					self::G_DC  => '用户的列表，也可以搜索用户(phone, name, email)，都是精确查询，不支持模糊查询',
					self::P_MGR => true
				),
				'class' => array(
					self::P_TLE => '分类管理',
					self::G_DC  => '获取、删除用户分类',
					self::P_MGR => true,
					
				),
				'class_edit' => array(
					self::P_TLE => '分类编辑',
					self::G_DC => '对分类进行批量编辑',
					self::P_MGR => true,
					self::P_NCD => true,
					self::P_ARGS => array(
						'name'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2, 16'),
						'code'         => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'编码', self::PA_RNG=>'2, 32'),
					),
				),
				'department' => array(
					self::P_TLE => '部门管理',
					self::G_DC => '添加、删除部门，及获取列表',
					self::P_MGR => true,
					
				),
				'dep_edit' => array(
					self::P_TLE => '部门编辑',
					self::G_DC => '对部门进行批量编辑, 以及加入指定用户到当前部门',
					self::P_MGR => true,
					self::P_NCD => true,
					self::P_ARGS => array(
						'name'     => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'名称', self::PA_RNG=>'2,16'),
						'password' => array(self::PA_REQ=>1, self::PA_EMP=>0, self::G_DC=>'密码', self::PA_RNG=>'6,'),
					)
				),
				'myinfo'  => array(
					self::P_TLE => '我的信息',
					self::G_DC  => '显示或修改我的信息，如果提供参数列表中的参数时，则进行修改.没有则返回当前用户信息',
					self::P_ARGS => array(
						'pwd1'     => array(self::PA_REQ=>1, self::G_DC=>'新密码', self::PA_RNG=>'6, 32'),
						'pwd2'     => array(self::PA_REQ=>1, self::G_DC=>'确认密码，须与1密码相同', self::PA_RNG=>'6, 32'),
						'src_pwd'  => array(self::PA_REQ=>1, self::G_DC=>'原来密码', self::PA_RNG=>'6, 32'),
					),
					self::P_OUT => '{retcode:"SUCCESS/ERROR_MSG", data:{user-info}',
					self::LD_FTR => array(
						array('user', 'checkLogin', true)
					),
				)
			),
		);
	}
	
	const BANNED_DEL_MAX_CLASS_ID = 3;
	
	function install($dbpool, $mempool=null){
		parent::install($dbpool, $mempool);
		
		mbs_import('', 'CUserControl', 'CUserClassControl', 'CUserDepControl');
		try {
			$ins = CUserControl::getInstance(self::$appenv, $dbpool, $mempool);
			$uid = $ins->add(array(
				'id'        => 1,
				'name'      => 'admin',
				'password'  => CUserControl::formatPassword('123321'),
				'phone'     => '13666666666',
				'reg_time'  => time(),
				'reg_ip'    => self::$appenv->item('client_ip')
			));
			$uid = $ins->add(array(
				'id'        => 2,
				'name'      => 'developer',
				'password'  => CUserControl::formatPassword('123123'),
				'phone'     => '13888888888',
				'reg_time'  => time(),
				'reg_ip'    => self::$appenv->item('client_ip')
			));
			
			//由于系统的要求，分类和部门的id需要一一对应.且在CUserDepControl中的$DEP_MAP的内容也是遵循这里的顺序
			$uc = CUserClassControl::getInstance(self::$appenv, $dbpool, $mempool);
			$pre_class = array(
				array('id' => 1, 'name' => '干警', 'code' => 'POLICE', 'create_time' => time()),
				array('id' => 2, 'name' => '厅、处领导', 'code' => 'TC_LDR', 'create_time' => time()),
				array('id' => 3, 'name' => '省委领导', 'code' => 'PV_LDR', 'create_time' => time()),
			);
			foreach($pre_class as $c){
				$uc->add($c);
			}
			
			$ud = CUserDepControl::getInstance(self::$appenv, $dbpool, $mempool);
			$pre_dep = array(
				array('id' => 1, 'name' => '业务系统查询结果', 'password'=>'123123', 'edit_time'=>time()),
				array('id' => 2, 'name' => '舆情报告', 'password'=>'123123', 'edit_time'=>time()),
				array('id' => 3, 'name' => '声像信息（视频）', 'password'=>'123123', 'edit_time'=>time()),
			);
			foreach($pre_dep as $d){
				$ud->add($d);
			}
		} catch (Exception $e) {
			throw $e;
		}
	}
}

?>