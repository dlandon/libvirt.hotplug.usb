<?php
/*
 * virshcmd.php
 *
 * Attach/Detach a USB hostdev to a running VM.
 * - Attach: uses VID:PID + bus/device to create a hostdev XML snippet.
 * - Detach: extracts the exact <hostdev ...> block from `virsh dumpxml` that matches VID:PID + bus/device,
 *           then detaches using that verbatim block (most robust).
 */

header('Content-Type: application/json');

$tmpXmlFile	= '/tmp/libvirthotplugusb.xml';

$rc				= 0;
$out			= [];
$result			= [
	'rc'		=> 0,
	'output'	=> ''
];

/*
 * Return the first matching <hostdev ...>...</hostdev> block for the given USB device.
 * Matches by:
 * - vendor id (hex, 4 chars, no 0x in input)
 * - product id (hex, 4 chars, no 0x in input)
 * - address bus/device (decimal, no padding requirements)
 */
function get_attached_usb_hostdev_block(string $vmname, string $vid, string $pid, int $bus, int $device): string
{
	$block		= '';
	$xmlLines	= [];
	$xml			= '';

	exec('virsh dumpxml '.escapeshellarg($vmname).' 2>/dev/null', $xmlLines);

	if (is_array($xmlLines) && $xmlLines) {
		$xml = implode("\n", $xmlLines);

		$vid	= strtolower($vid);
		$pid	= strtolower($pid);

		/* Capture entire hostdev blocks (including any libvirt-added attributes). */
		if (preg_match_all('/(<hostdev\b[^>]*\btype=[\'"]usb[\'"][^>]*>.*?<\/hostdev>)/s', $xml, $m, PREG_SET_ORDER)) {
			foreach ($m as $hit) {
				$candidate = (string)$hit[1];

				/* Pull vendor/product and address from the candidate block. */
				if (!preg_match('/<vendor\b[^>]*\bid=[\'"]0x([0-9a-fA-F]{4})[\'"][^>]*\/>/', $candidate, $mv)) {
					continue;
				}
				if (!preg_match('/<product\b[^>]*\bid=[\'"]0x([0-9a-fA-F]{4})[\'"][^>]*\/>/', $candidate, $mp)) {
					continue;
				}
				if (!preg_match('/<address\b[^>]*\bbus=[\'"](\d+)[\'"]\s+device=[\'"](\d+)[\'"][^>]*\/>/', $candidate, $ma)) {
					continue;
				}

				$cVid	= strtolower((string)$mv[1]);
				$cPid	= strtolower((string)$mp[1]);
				$cBus	= (int)$ma[1];
				$cDev	= (int)$ma[2];

				if ($cVid === $vid && $cPid === $pid && $cBus === $bus && $cDev === $device) {
					$block = $candidate;
					break;
				}
			}
		}
	}

	return $block;
}

/* Safely read POST values (avoid undefined index + urldecode(null) deprecation). */
$actionRaw	= (string)($_POST['action'] ?? '');
$vmnameRaw	= (string)($_POST['VMNAME'] ?? '');
$usbidRaw	= (string)($_POST['USBID'] ?? '');
$usbbusRaw	= (string)($_POST['USBBUS'] ?? '');
$usbdevRaw	= (string)($_POST['USBDEVICE'] ?? '');

$action		= urldecode($actionRaw);
$vmname		= urldecode($vmnameRaw);
$usbid		= urldecode($usbidRaw);

/* IMPORTANT: libvirt requires integers here (no zero padding). */
$usbbus		= (int)urldecode($usbbusRaw);
$usbdevice	= (int)urldecode($usbdevRaw);

/* Lookup the virsh command since the path can change with different versions of Unraid. */
$virshPath = trim((string)shell_exec('/usr/bin/whereis -b virsh | cut -d " " -f 2'));

/* Basic validation. */
if ($action === '' || $vmname === '' || $virshPath === '') {
	$rc		= 1;
	$out[]	= 'error: Missing action, VM name, or virsh path.';
} else {
	$xml	= '';

	/* Build or extract XML depending on action. */
	if ($action === 'attach') {
		$usbx = ($usbid !== '') ? explode(':', $usbid, 2) : [];
		$vid = strtolower((string)($usbx[0] ?? ''));
		$pid = strtolower((string)($usbx[1] ?? ''));

		if (!preg_match('/^[0-9a-f]{4}$/', $vid) || !preg_match('/^[0-9a-f]{4}$/', $pid) || $usbbus < 0 || $usbdevice < 0) {
			$rc		= 1;
			$out[]	= 'error: Invalid USBID (vid:pid) or bus/device.';
		} else {
			$xml =
				"<hostdev mode='subsystem' type='usb'>".
					"<source>".
						"<vendor id='0x{$vid}'/>".
						"<product id='0x{$pid}'/>".
						"<address bus='{$usbbus}' device='{$usbdevice}'/>".
					"</source>".
				"</hostdev>";
		}
	} elseif ($action === 'detach') {
		$usbx = ($usbid !== '') ? explode(':', $usbid, 2) : [];
		$vid = strtolower((string)($usbx[0] ?? ''));
		$pid = strtolower((string)($usbx[1] ?? ''));

		if (!preg_match('/^[0-9a-f]{4}$/', $vid) || !preg_match('/^[0-9a-f]{4}$/', $pid) || $usbbus < 0 || $usbdevice < 0) {
			$rc		= 1;
			$out[]	= 'error: Invalid USBID (vid:pid) or bus/device.';
		} else {
			$xml = get_attached_usb_hostdev_block($vmname, $vid, $pid, $usbbus, $usbdevice);

			if ($xml === '') {
				$rc		= 1;
				$out[]	= 'error: No matching attached USB hostdev found in virsh dumpxml for this VM.';
			}
		}
	} else {
		$rc		= 1;
		$out[]	= 'error: Invalid action. Expected "attach" or "detach".';
	}

	/* Execute virsh if we have XML. */
	if ($rc === 0 && $xml !== '') {
		if (@file_put_contents($tmpXmlFile, $xml."\n") === false) {
			$rc		= 1;
			$out[]	= 'error: Failed to write XML file.';
		} else {
			$cmd = $virshPath.' '.escapeshellarg($action).'-device '.escapeshellarg($vmname).' '.escapeshellarg($tmpXmlFile).' 2>&1';

			/* Delay can help with fast UI actions. */
			sleep(1);

			$execOut = [];
			$execRc = 0;
			exec($cmd, $execOut, $execRc);

			$rc = (int)$execRc;
			if ($execOut) {
				$out = array_merge($out, $execOut);
			}
		}
	}
}

/* Single exit point response. */
$result['rc']		= (int)$rc;
$result['output']	= implode("\n", $out);

echo json_encode($result, JSON_UNESCAPED_SLASHES);
?>
