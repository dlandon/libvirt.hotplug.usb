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
		echo shell_exec("/sbin/ifconfig | head -n+1");
		break;
		
		case 'attach':
		$vmname = $_POST['VMNAME'];
		$usbid = $_POST['USBID'];
		echo shell_exec("/usr/bin/smbclient -g -L '$ip' $login 2>&1|awk -F'|' '/Disk/{print $2}'|sort");
		break;
		
		
		
		
		
		}
		
		
		

?>