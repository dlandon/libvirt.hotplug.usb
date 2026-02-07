<?php
/*
 *  Execute Virsh Command
 */

$action		= urldecode($_POST['action']);
$vmname		= urldecode($_POST['VMNAME']);
$usbid		= urldecode($_POST['USBID']);
$usbbus		= urldecode($_POST['USBBUS']);
$usbdevice	= urldecode($_POST['USBDEVICE']);

if ($usbid)
{
	$usbx	= explode(':', $usbid);
	$usbstr	= "<hostdev mode='subsystem' type='usb'><source><vendor id='0x".$usbx[0]."'/><product id='0x".$usbx[1]."'/><address type='usb' bus='" . $usbbus . "' device='" . $usbdevice . "'/></source></hostdev>";
} else {
	$usbstr	= '';
}

file_put_contents('/tmp/libvirthotplugusb.xml', $usbstr);

/* Lookup up the virsh command since the path can change with different versions of Unraid. */
$virshPath = trim(shell_exec('/usr/bin/whereis -b virsh | cut -d " " -f 2'));

/* Execute the virsh command. */
$command = "{$virshPath} ".escapeshellarg($action)."-device ".escapeshellarg($vmname)." /tmp/libvirthotplugusb.xml 2>&1";

echo "\n".shell_exec($command);
?>
