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
		echo shell_exec("/usr/sbin/virsh detach-device '$vmname' /tmp/libvirthotplugusb.xml 2>&1");
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
		echo shell_exec("/usr/sbin/virsh attach-device '$vmname' /tmp/libvirthotplugusb.xml 2>&1");
		break;
}
?>