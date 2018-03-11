<?PHP
/* 
 *  Execute Virsh Command
 */
?>

<?
$vmname = $_POST['VMNAME'];
$usbid = $_POST['USBID'];
$usbstr = '';
if (!empty($usbid)) 
{
	$usbx = explode(':', $usbid);
	$usbstr .= "<hostdev mode='subsystem' type='usb'>
<source>
<vendor id='0x".$usbx[0]."'/>
<product id='0x".$usbx[1]."'/>
</source>
</hostdev>";
}
file_put_contents('/tmp/libvirthotplugusb.xml',$usbstr);

switch ($_POST['action']) {
	case 'detach':
		$rc = shell_exec("/usr/sbin/virsh detach-device '$vmname' /tmp/libvirthotplugusb.xml 2>&1");
		break;
		
	case 'attach':
		$rc = shell_exec("/usr/sbin/virsh attach-device '$vmname' /tmp/libvirthotplugusb.xml 2>&1");
		break;
}

echo $rc;
?>
