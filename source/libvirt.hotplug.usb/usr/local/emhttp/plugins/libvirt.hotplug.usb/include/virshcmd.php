<?PHP
/* 
 *  Execute Virsh Command
 */
?>
<?

switch ($_POST['action']) {


		case 'detach':
		$ip = urldecode($_POST['IP']);
		$user = isset($_POST['USER']) ? urlencode($_POST['USER']) : NULL;
		$pass = isset($_POST['PASS']) ? urlencode($_POST['PASS']) : NULL;
		$login = $user ? ($pass ? "-U '{$user}%{$pass}'" : "-U '{$user}' -N") : "-U%";
		echo shell_exec("/usr/bin/smbclient -g -L '$ip' $login 2>&1|awk -F'|' '/Disk/{print $2}'|sort");
		break;
		
		case 'attach':
		$ip = urldecode($_POST['IP']);
		$user = isset($_POST['USER']) ? urlencode($_POST['USER']) : NULL;
		$pass = isset($_POST['PASS']) ? urlencode($_POST['PASS']) : NULL;
		$login = $user ? ($pass ? "-U '{$user}%{$pass}'" : "-U '{$user}' -N") : "-U%";
		echo shell_exec("/usr/bin/smbclient -g -L '$ip' $login 2>&1|awk -F'|' '/Disk/{print $2}'|sort");
		break;
		
		
		
		
		
		}
		
		
		

?>