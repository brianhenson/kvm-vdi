<?php
/*
KVM-VDI
Tadas Ustinavičius

Vilnius University.
Center of Information Technology Development.


Vilnius,Lithuania.
2016-07-08
*/
###################Dashboard config##########################
$serviceurl='http://192.168.0.20/kvm-vdi';
$language='en_EN';
############################################################


##################VM config#################################
#Enable this if you want to reset VMs each time they are provided
#to client. This will not affect machines, whic are taken over by
#another thin client.
$reset_vm=0;
############################################################



##################Hypervisor config#########################
$temp_folder='/data/tmp';
$backend_pass='12345';
$ssh_user='VDI';
$ssh_key_path='/var/hyper_keys/';
$hypervisor_cmdline_path='/usr/local/VDI/';
$default_bridge='br0';
$default_imagepath='/data';
$default_iso_path='/var/lib/libvirt/images';

$libvirt_user='root'; //user, on which libvirtd daemon runs
$libvirt_group='root'; //group, on which libvirtd daemon runs
############################################################


####################Database config#########################
$mysql_host='localhost';
$mysql_db='vdi';
$mysql_user='vdi';
$mysql_pass='vdi';
############################################################


##################vmWare Horizon config (if used)###########
$vmView_server='view.someadress.tld';
############################################################