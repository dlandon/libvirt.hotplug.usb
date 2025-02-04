<?php
/* 
 *  Execute Virsh Command
 */

$action	= htmlspecialchars(urldecode($_POST['action']));
$vmname	= htmlspecialchars(urldecode($_POST['VMNAME']));
$usbid	= htmlspecialchars(urldecode($_POST['USBID']));
$usbstr	= '';

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

file_put_contents('/tmp/libvirthotplugusb.xml', $usbstr);

/* Lookup up the virsh command since the path can change with different versions of Unraid. */
$virshPath = trim(shell_exec('whereis -b virsh | cut -d " " -f 2'));

/* Execute the virsh command. */
$command = "{$virshPath} ".escapeshellarg($action)."-device ".escapeshellarg($vmname)." /tmp/libvirthotplugusb.xml 2>&1";

echo "\n".shell_exec($command);
?>
