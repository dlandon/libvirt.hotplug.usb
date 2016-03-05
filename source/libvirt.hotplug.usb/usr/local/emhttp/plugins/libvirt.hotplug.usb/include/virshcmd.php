<?PHP
/* 
 *  Execute Virsh Command
 */
?>
<?

if ($_POST['mode']>0) {
  $hour = isset($_POST['hour']) ? $_POST['hour'] : '*';
  $min  = isset($_POST['min'])  ? $_POST['min']  : '*';
  $dotm = isset($_POST['dotm']) ? $_POST['dotm'] : '*';
  $day  = isset($_POST['day'])  ? $_POST['day']  : '*';
  $cron = "# Generated ssd trim schedule:\n$min $hour $dotm * $day /sbin/fstrim -v /mnt/cache | logger &> /dev/null\n\n";
} else {
  $cron = "";
}
parse_cron_cfg('dynamix', 'ssd-trim', $cron);
?>