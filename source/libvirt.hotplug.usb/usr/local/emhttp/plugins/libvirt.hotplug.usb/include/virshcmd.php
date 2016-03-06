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
			if (!empty($usbid)) {
			$usbx = explode(':', $usbid);
			$usbstr .= "<hostdev mode='subsystem' type='usb' managed='yes'>
						<source>
						<vendor id='0x".$usbx[0]."'/>
						<product id='0x".$usbx[1]."'/>
						</source>
						</hostdev>";
						}
		shell_exec("/usr/bin/echo '$usbstr' > /tmp/libvirthotplugusb.xml");
		echo shell_exec("/sbin/ifconfig | head -n+1");
		break;
		
		case 'attach':
		$vmname = $_POST['VMNAME'];
		$usbid = $_POST['USBID'];
		echo shell_exec("/sbin/ifconfig | head -n+1");
		break;
		}
?>