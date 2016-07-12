<?php
/*
KVM-VDI
Tadas Ustinavičius

Vilnius University.
Center of Information Technology Development.


Vilnius,Lithuania.
2016-07-05
*/
include ('functions/config.php');
require_once('functions/functions.php');
slash_vars();
if (isset ($_POST['username'])){
    $username=$_POST['username'];
    $password=$_POST['password'];
    $sql_reply=get_SQL_line("SELECT id,password FROM clients WHERE username LIKE '$username'");
    if (hash_equals($sql_reply[1], crypt($password, $sql_reply[1]))) {
	session_start();
	$_SESSION['client_logged']='yes';
	$_SESSION['userid']=$sql_reply[0];
	$_SESSION['username']=$username;
	$ip = $_SERVER['REMOTE_ADDR'];
	add_SQL_line("UPDATE clients SET lastlogin=now(), ip='$ip' WHERE id='$sql_reply[0]'");
	header("Location: $serviceurl/client_pools.php");
	exit;
    }
    else {
	//header("Location: $serviceurl/client_index.php?error=1");
	echo 'LOGIN_FAILURE';
	exit;
    }
}
if (!check_client_session()){
    header ("Location: $serviceurl/client_index.php?error=1");
    exit;
}
reload_vm_info();
set_lang();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="author" content="Tadas Ustinavičius">
    <title>KVM-VDI</title>
    <link href="inc/css/bootstrap.min.css" rel="stylesheet">
    <link href="inc/css/bootstrap-theme.min.css" rel="stylesheet">
    <link href="inc/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css">
    <link href="inc/css/custom.css" rel="stylesheet">
    <link href="inc/css/sb-admin-2.css" rel="stylesheet">
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.2/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
    <div class="container">
	<div class="row">
<?php 
    $userid=$_SESSION['userid'];
    $username=$_SESSION['username'];
    $pool_reply=get_SQL_array("SELECT pool.id, pool.name FROM poolmap  LEFT JOIN pool ON poolmap.poolid=pool.id WHERE clientid='$userid'");
    $x=0;
    while ($x<sizeof($pool_reply)){
	    $vm_count=get_SQL_array("SELECT COUNT(*) FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}' AND vms.maintenance!='true' AND hypervisors.maintenance!=1");
	    $vm_count_available=get_SQL_array("SELECT COUNT(*) FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id  WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}' AND vms.maintenance!='true'  AND hypervisors.maintenance!=1 AND vms.lastused < DATE_SUB(NOW(), INTERVAL 5 MINUTE) ");
	    $already_have=get_SQL_array("SELECT COUNT(*) FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id  WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}'AND vms.maintenance!='true' AND hypervisors.maintenance!=1 AND vms.clientid='$userid' AND vms.lastused > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
	    $vm_image="text-warning";
	    $provided_vm[0]['name']="none";
	    if ($already_have[0][0]==1){
		$vm_image="text-success";
		$provided_vm=get_SQL_array("SELECT vms.name,vms.state,vms.id FROM poolmap_vm LEFT JOIN vms ON poolmap_vm.vmid=vms.id LEFT JOIN hypervisors ON vms.hypervisor=hypervisors.id  WHERE poolmap_vm.poolid='{$pool_reply[$x]['id']}'AND vms.maintenance!='true' AND hypervisors.maintenance!=1 AND vms.clientid='$userid' AND vms.lastused > DATE_SUB(NOW(), INTERVAL 5 MINUTE)");
	    }
	    if ($vm_count_available[0][0]==0)
		$vm_image="text-muted";
	    if ($provided_vm[0]['state']=='running'){
		$pm_icons='<a href="#" class="shutdown"  id="' . $provided_vm[0]['id'] . '"><i class="pull-left fa fa-stop-circle-o text-danger" title="Shutdown machine"></i></a>';
		$pm_icons=$pm_icons.'<a href="#" class="terminate"  id="' . $provided_vm[0]['id'] . '"><i class="pull-left fa fa-times-circle-o text-danger" title="Terminate machine"></i></a>';
	    }
	    echo'<div class="col-md-2">';
		echo '<div class="pool_placeholder"></div>';
		echo '<div class="row text-info">
		    <div class="pool_square">
			<div><small>' . $pm_icons . $provided_vm[0]['name'] . '<small></div>
			<div>
		    	    <a href="#" id="' . $pool_reply[$x]['id'] . '" class="pools">
	    <span class="fa-stack fa-4x">
		<i class="fa fa-square-o fa-stack-2x"></i>
		<i class="fa fa-power-off fa-stack-1x ' . $vm_image . '"></i>
	    </span>
	    </a>
			</div>
			<div>
			    <span>' . $pool_reply[$x]['name'] . '</span>
    		</div>
			<div>
			    <span class="text-muted pull-left"><small>Pool size: ' . $vm_count[0][0] . '</small></span>
			    <span class="text-muted pull-right"><small>Available: ' . $vm_count_available[0][0] . '</small></span>
    		</div>
		</div>
	</div>
</div>'."\n";
	    ++$x;
	    if ((($x % 3) / 3)==0)
		echo '</div>' . "\n". '<div class="row">' . "\n";

    }?>
	</div>
    </div>
    <script src="inc/js/jquery.min.js"></script>
    <script src="inc/js/bootstrap.min.js"></script>
<script>
$(document).ready(function(){
    $('.pools').click(function() {
	document.title = ""
	document.title = "kvm-vdi-msg:" + $(this).attr('id')
    })
    $('.shutdown').click(function() {
	document.title = ""
	document.title = "kvm-vdi-msg:PM:shutdown:" + $(this).attr('id')
    })
    $('.terminate').click(function() {
	document.title = ""
	document.title = "kvm-vdi-msg:PM:destroy:" + $(this).attr('id')
    })
    function PM(vmid,action){
    $.ajax({
            type : 'POST',
            url : 'client_power.php',
            data: {
                vm : vmid,
                action : action,
            },
	})
    }
})
</script>
  </body>
</html>