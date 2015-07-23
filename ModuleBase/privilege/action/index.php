<?php 

mbs_import('user', 'CUserSession');
$us = new CUserSession();
$user_id = $us->checkLogin();
if(empty($user_id)){
	echo $us->getError();
	exit(0);
}

mbs_import('privilege', 'CPrivUserControl', 'CPrivGroupControl');

$priv_info = null;
try {
	$pu = CPrivUserControl::getInstance($mbs_appenv,
			CDbPool::getInstance(), CMemcachedPool::getInstance());
	$priv_info = $pu->getDB()->search(array('user_id' => $user_id));
} catch (Exception $e) {
	echo $mbs_appenv->lang('db_exception', 'common');
	exit();
}
if(empty($priv_info) || !($priv_info = $priv_info->fetchAll(PDO::FETCH_ASSOC))){
	echo 'access denied(1)';
	exit(0);
}
$priv_info = $priv_info[0];

$pg = CPrivGroupControl::getInstance($mbs_appenv, CDbPool::getInstance(), 
	CMemcachedPool::getInstance(), $priv_info['priv_group_id']);
$priv_list = $pg->get();
if(empty($priv_list)){
	echo 'access denied(2)';
	exit(0);
}

$priv_group = CPrivGroupControl::decodePrivList($priv_list['priv_list']);


function _fn_icon($mod, $ac){
	static $icon_map = array(
		'info.edit' => 'ico1',
		'info.list' => 'ico2',

		'info_push.push'         => 'ico3',
		'info_push.push_list'    => 'ico4',
		'info_push.comment_list' => 'ico5',
	);
	echo isset($icon_map[$mod.'.'.$ac]) ?  '<i class="ico '. $icon_map[$mod.'.'.$ac]. '"></i>' : '';
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
	<title><?php mbs_title()?></title>
	<!--[if lt ie 9]>
		<script>
			document.createElement("article");
			document.createElement("section");
			document.createElement("aside");
			document.createElement("footer");
			document.createElement("header");
			document.createElement("nav");
	</script>
	<![endif]-->
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css"/>
	<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>">
	<style type="text/css">
	iframe{width:100%;border:0;}
	</style>
</head>
<body>
<div class=top-win>
	<!-- 头部 -->
	<header class="header"><?php echo $mbs_appenv->lang('header_html')?></header>
	<!-- 头部end -->
	<!-- 左边栏 -->
	<nav id="navBar">
		<dl class="navBar">
		<?php 
		if(isset($priv_group[CPrivGroupControl::PRIV_TOPMOST])){
			$list = $mbs_appenv->getModList();
			foreach($list as $mod){
				if('core' == $mod) continue;
				$moddef=mbs_moddef($mod);
				if(empty($moddef)) continue;
				$actions = $moddef->filterActions(CModDef::P_MGR);
				if(empty($actions)) continue;
		?>
		<dt class="group-type"><?php echo $moddef->item(CModDef::MOD, CModDef::G_TL)?></dt>
		<?php foreach($actions as $ac => $def){ if(isset($def[CModDef::P_NCD])) continue; ?>
		<dd class="type"><a href="#" class="link-type" data="<?php echo $mbs_appenv->toURL($ac, $mod)?>" onclick="_to(this)">
			<?php _fn_icon($mod, $ac)?><?php echo $def[CModDef::P_TLE]?></a></dd>
		<?php }}}else{  ?>
		<?php foreach($priv_group as $mod => $actions){ if('core' == $mod) continue; $moddef=mbs_moddef($mod);if(empty($moddef)) continue; ?>
		<dt class="group-type"><?php echo $moddef->item(CModDef::MOD, CModDef::G_TL)?></dt>
		<?php foreach($actions as $ac){ $def=$mod->item(CModDef::PAGES, $ac); if(isset($def[CModDef::P_NCD])) continue;?>
		<dd class="type"><a href="#" data="<?php echo $mbs_appenv->toURL($ac, $mod)?>" onclick="_to(this)">
			<?php echo _fn_icon($mod, $ac)?><?php echo $def[CModDef::P_TLE]?></a></dd>
		<?php }}} ?>
		</dl>
	</nav>
	<!-- 左边栏end -->
	<!-- 内容主体 -->
	<section class="wrap" id="wrap">
		<iframe src=""></iframe>
	</section>
	<!-- 内容主体end -->

	<!-- 加载jquery.js -->
	<!--[if ie 6]>
	<script src="<?php echo $mbs_appenv->sURL('jquery-1.3.1.min.js')?>"></script>
	<script src="<?php echo $mbs_appenv->sURL('fixIE6.js')?>"></script>
	<![endif]-->
</div>
<script type="text/javascript">
var frame = document.getElementsByTagName("iframe")[0], prev = null, visit_actions = [];
var links = document.getElementsByTagName("a"), i, j=0, firstlink=null;

for(i=0; i<links.length; i++){
	if("DD" == links[i].parentNode.tagName){
		if(document.location.search.indexOf("to=") != -1){
			if(document.location.href.indexOf(encodeURIComponent(links[i].getAttribute("data"))) != -1){
				_to.call(links[i], decodeURIComponent(document.location.search.substr(4)));
				break;
			}
		}
		else{
			_to(links[i]);
			break;
		}
	}
}

function _to(link, is_redirect){
	var url;
	
	if("object" == typeof link){
		url = link.getAttribute("data");
	}else{
		url = link;
		link = this;
	}

	is_redirect = 1 == arguments.length ? true : is_redirect;
	
	if(prev != null){
		prev.className = prev.className.replace("check", "");
	}
	link.className += " check";
	prev = link;
	
	if(is_redirect){
		frame.src = url;
		frame.onload = frame.onreadystatechange = function(e){ //onload: for chrom
			if (frame.contentWindow.document.readyState=="complete"){
				frame.style.height=(document.getElementsByTagName("html")[0].clientHeight-65)+"px";
				document.title = frame.contentWindow.document.title;
				history.pushState(null, null, "<?php echo $mbs_appenv->item('cur_action_url') ?>?to="
						+encodeURIComponent( frame.contentWindow.location.href));
				//frame.contentWindow.document.body.onclick = function(e){
				//	if(prev)
				//		prev.className = "blur_a";
				//}
				
				if( -1 == frame.contentWindow.document.location.href.indexOf(prev.getAttribute("data")) ){
					for(var i=0; i<links.length; i++){
						if(frame.contentWindow.document.location.href.indexOf(links[i].getAttribute("data")) != -1){
							_to(links[i], false);
							break;
						}
					}
					/*if(i == links.length){
						document.location = frame.contentWindow.document.location.href;
					}*/
				}
			}
		}
	}
}

document.onkeydown = frame.contentWindow.document.onkeydown = function(e){
	e = e || this.parentWindow.event;
	if(116 == (e.keyCode || e.which)){ // forriden F5 key in parent window
		frame.contentWindow.location.reload();
		e.returnValue = false;
		e.cancelBubble = true;
		e.keyCode = 0;
		return false;
	}
}




</script>
</body>
</html>