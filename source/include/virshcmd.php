<?PHP
/* 
 *  Execute Virsh Command
 */

$action = $_POST['action'];
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

echo "\n".shell_exec("/usr/sbin/virsh ".escapeshellarg($action)."-device ".escapeshellarg($vmname)." /tmp/libvirthotplugusb.xml 2>&1");
?>
