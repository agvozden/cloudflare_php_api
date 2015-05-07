<?php

$token_key = 'YOUR_TOKEN_KEY_HERE';
$email = 'YOUR_EMAIL';

echo 'start';

include 'class_cloudflare.php';
$cf = new cloudflare_api($email, $token_key);

// for each zone set domain on left, zone file on right
$zones = array(
	'domain.tld' => 'domain.tld.zone.txt',
);

foreach ($zones as $zone => $zone_file){

	$response = $cf->rec_load_all($zone);
	$isub_r = $response->response->recs->objs;
	$sub_r = array();

	foreach ($isub_r as $row){
		$sub_r[] = array(
			'rec'=>$row->name,
			'ttl'=>$row->ttl_ceil,
			'type'=>$row->type,
			'value'=>$row->content,
			'id'=>$row->rec_id
		);
	}
	//print_r($sub_r);

	$fin = fopen($zone_file, 'r');
	while (!feof($fin)){

		$matches = array();
		$line = trim(fgets($fin));

		// only care about lines that are ip addresses or aliases
		if (preg_match('/^(\S+)\s+(\S+)\s+(IN)\s+(A|AAAA|CNAME)\s+(\S+)$/i', $line, $matches)){
			$rec = $matches[1];
			$ttl = $matches[2];
			$in = $matches[3];
			$type = $matches[4];
			$ip_or_alias = $matches[5];
			if (substr($rec, 0, 2)!==';;')
			check_record($rec, $ttl, $in, $type, $ip_or_alias);
			//else echo ":{$rec} {$ttl} {$in} {$type} {$value}; <br>";
		}
		elseif (preg_match('/^(\S+)\s+(IN)\s+(A|AAAA|CNAME)\s+(\S+)$/i', $line, $matches)){
			$rec = $matches[1];
			$ttl = '';
			$in = $matches[2];
			$type = $matches[3];
			$ip_or_alias = $matches[4];
			if (substr($rec, 0, 2)!==';;')
			check_record($rec, $ttl, $in, $type, $ip_or_alias);
			//else echo ":{$rec} {$ttl} {$in} {$type} {$value}; <br>";
		}
		//else echo "!{$line}<br/>";

	}
	fclose($fin);
}
echo 'end';

function check_record($rec, $ttl, $in, $type, $value)
{
	global $sub_r, $cf, $zone;
	// trim subdomain name
	$rec = substr($rec, 0, -1);
	if (substr($value, -1)=='.') $value = substr($value, 0, -1);

	echo " {$rec} {$ttl} {$in} {$type} {$value}; <br>";
	$key = array_search($rec, array_column($sub_r, 'rec'));

	if ($key!==false){

		$sub = $sub_r[$key];
		if ($value!=$sub['value']){
			$cf->rec_edit($zone, $type, $sub['id'], $rec, $value, $ttl=1, $mode=1, $prio=1, $service=1, $srvname=1, $protocol=1, $weight=1, $port=1, $target=1);
			echo "update {$sub['value']}<br>";
		} else
		echo " {$sub['rec']} {$sub['ttl']} {$sub['type']} {$sub['value']}; <br>";

	} else {

		$cf->rec_new($zone, $type, $rec, $value, $ttl=1, $mode=1, $prio=1, $service=1, $srvname=1, $protocol=1, $weight=1, $port=1, $target=1);
		echo 'new record<br>';
	}

	echo '<br>';
}
