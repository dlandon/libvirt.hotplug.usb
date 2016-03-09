<?PHP
/* 
 *  Execute Virsh Command
 */
?>
<?

switch ($_POST['action']) {

case 'detach':
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
$cmd = '/usr/sbin/virsh detach-device '.escapeshellarg($VMNAME).' /tmp/libvirthotplugusb.xml 2>&1';
echo shell_exec($cmd);
break;
		
case 'attach':
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
$cmd = '/usr/sbin/virsh attach-device '.escapeshellarg($VMNAME).' /tmp/libvirthotplugusb.xml 2>&1';
echo shell_exec($cmd);
break;
		}
?>