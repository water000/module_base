<?php 

mbs_import('', 'CUserDepControl', 'CUserDepMemberControl', 'CUserControl');
$udep = CUserDepControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$udepmbr = CUserDepMemberControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());
$usr = CUserControl::getInstance($mbs_appenv, 
		CDbPool::getInstance(), CMemcachedPool::getInstance());

$list = array();
$error = array();
if(isset($_REQUEST['dep_id'])){
	if(isset($_REQUEST['join_member'])){
		foreach($_REQUEST['user_id'] as $uid){
			$dep_mbr = array(
					'dep_id'    => $_REQUEST['dep_id'],
					'user_id'   => $uid,
					'join_time' => time(),
			);
			$ret = $udepmbr->addNode($dep_mbr);
			if(!$ret){
				$error[] = $mbs_appenv->lang('member_exists').'(user-id:'.$uid.')';
			}
		}
	}
	else if(isset($_REQUEST['remove_member']) && isset($_REQUEST['user_id'])){
		$udepmbr->setPrimaryKey($_REQUEST['dep_id']);
		foreach($_REQUEST['user_id'] as $uid){
			$udepmbr->setSecondKey($uid);
			$ret = $udepmbr->delNode();
			if(!$ret){
				$error[] = 'user-id:'.$uid.'('.$udepmbr->error().')';
			}
		}
	}
	$udep->setPrimaryKey($_REQUEST['dep_id']);
	$dep = $udep->get();
	
	$udepmbr->setPrimaryKey($_REQUEST['dep_id']);
	$mbrlist = $udepmbr->get();
}
?>
<!doctype html>
<html>
<head>
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
<meta name="viewport" content="width=device-width,initial-scale=1.0,user-scalable=no,minimum-scale=1.0,maximum-scale=1.0">
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('reset.css')?>" type="text/css" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('global.css')?>" />
<link rel="stylesheet" href="<?php echo $mbs_appenv->sURL('allInfo.css')?>">
<style type="text/css">
.col1{width:60px;}
.col2{width:185px;}
.col3{width:285px;}
.col4{width:520px;}
.name{font-size:14px; color:#111;}
.dep-desc{margin:22px 8px 0;;font-size:14px;}
.dep-desc span{color:rgb(0,67,144);margin-right:20px;}
.dep-desc i{margin-right:5px;position:inherit;left:0;right:0;display:inline-block;width:16px;vertical-align:middle;}
.ico-dep{background-position:-3px -236px}
.ico-mbr{background-position:-4px -216px}
</style>
</head>
<body>
<div class="allInfo">
	<h2 class="tit">
		<?php echo $mbs_appenv->lang(array('dep_member', 'manage'))?>
		<a href="<?php echo $mbs_appenv->toURL('department')?>" class="btn-cancel"><span class="back-icon"></span><?php echo $mbs_appenv->lang('back')?></a>
	</h2>
	
	<?php if(!empty($error)){ ?>
	<div class=error><?php  foreach($error as $e){?><p><?php echo CStrTools::txt2html($e)?></p><?php }?>
	<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
	</div>
	<?php }else if(isset($_REQUEST['join_member']) || isset($_REQUEST['remove_member'])){ ?>
	<div class=success><?php echo $mbs_appenv->lang('operation_success', 'common')?>
		<a href="#" class=close onclick="this.parentNode.parentNode.removeChild(this.parentNode)" >&times;</a>
	</div>
	<?php }?>
	
	<div class=dep-desc>
		<i class="ico ico-dep"></i><?php echo $mbs_appenv->lang('department'), '&nbsp;:&nbsp;<span>', $dep['name'], '</span>'?>
		<i class="ico ico-mbr"></i><?php echo $mbs_appenv->lang(array('member', 'num')), '&nbsp;:&nbsp;<span>', count($mbrlist), '</span>'?>
		<a href="javascript:;" class="btn-create" style="position: relative;top:0;right:0;display:inline-block;margin-left:50px;">
			+<?php echo $mbs_appenv->lang(array('add', 'member'))?></a>
	</div>
    <div class="box-tabel mb17" style="margin-top:28px;">
		<form name="_form" method="post" action="">
			<input type="hidden" name="remove_member" value="1" />
			<table class="info-table" style="width: 100%;margin-top:1em;">
			    <thead>
			        <tr>
			            <th class="first-col col1"><input type="checkbox" onclick="_checkall(this, document._form)" /></th>
			            <th class="col2"><?php echo $mbs_appenv->lang('name')?></th>
			            <th class="col4"><?php echo $mbs_appenv->lang('join_time')?></th>
			        </tr>
			    </thead>
			    <tbody>
			    <?php foreach($mbrlist as $k=>$row){ $usr->setPrimaryKey($row['user_id']); $uinfo=$usr->get();?>
			        <tr>
			            <td class="first-col">
			            	<input type="checkbox" name="user_id[]" value="<?php echo $row['user_id']?>" />
			            </td>
			            <td class=name><?php echo empty($uinfo) ? '(delete)' : $uinfo['name']?></td>
			            <td><?php echo date('Y-m-d H:i', $row['join_time'])?></td>
			        </tr>
			    <?php }?>
			    </tbody>
			</table>
			<div style="margin-top:10px;" class=box-bottom>
				<a href="javascript:if(confirm('<?php echo $mbs_appenv->lang('confirmed')?>')) document._form.submit();" class="btn-del" >
					<i class="ico"></i><?php echo $mbs_appenv->lang('delete')?></a>
			</div>
		</form>
		<form action="" method="post" name="form_join">
			<input type="hidden" name="join_member" />
		</form>
    </div>
</div>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery-1.3.1.min.js')?>"></script>
<script type="text/javascript" src="<?php echo $mbs_appenv->sURL('jquery.avgrund.js')?>"></script>
<script type="text/javascript">
var link = window.top.document.createElement("link");
window.top.document.body.appendChild(link);
link.href = "<?php echo $mbs_appenv->sURL('avgrund.css')?>"; 
link.rel="stylesheet";

$('.avgrund-popin', window.top.document).remove();
var g_avgrund = $('.btn-create').avgrund({
	height: 555,
	width: 760,
	holderClass: 'avgrund-custom',
	showClose: true,
	showCloseText: '<?php echo $mbs_appenv->lang('close')?>',
	title: '<?php echo $mbs_appenv->lang(array('select', 'member'))?>',
	onBlurContainer: '.container',
	body: window.top.document.getElementsByTagName("div")[0],
	template: function(obj){
		return '<iframe style="width:100%;height:500px;" src="<?php echo $mbs_appenv->toURL('list')?>'+'"></iframe>';
	}
});

window.top.on_user_selected = function(arr){
	g_avgrund.deactivate();
	
	var inp;
	for(var i=0; i<arr.length; i++){
		inp = document.createElement("input");
		inp.type = "hidden";
		inp.name = "user_id[]";
		inp.value = arr[i][0];
		document.form_join.appendChild(inp);
	}
	document.form_join.submit();
}
window.onbeforeunload = function(e){
	window.top.on_user_selected = null;
}
function _checkall(chkbox, form){
	var i, boxes=form.elements["user_id[]"];
	boxes = boxes.length ? boxes : [boxes];
	for(i=0; i<boxes.length; i++){
		boxes[i].checked = chkbox.checked;
	}
}
</script>
</body>
</html>