<?php
include_once 'inc/config.php';

if (!array_key_exists('id', $_REQ))
	return;

$id = intval($_REQ['id'], 0);

if (!is_numeric($id) || $id < 0 || $id > 65535)
	return;

$count = 16;

if (array_key_exists('count', $_REQ)) {
	$count = intval($_REQ['count'], 0);

	if (!is_numeric($count) || $count <= 0)
		$count = 16;

	if ($count > 64)
		$count = 64;
}

$key = "itemgrid-$id-$count";

$rhost = parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_HOST);
$rport = parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_PORT);
$rpass = parse_url($_ENV['REDISCLOUD_URL'], PHP_URL_PASS);

$rd = new Redis();
$rd->pconnect($rhost, $rport);
$rd->auth($rpass);

$itemlist = $rd->get($key);

if (!$itemlist) {
	$url = 'http://uo.cx/api/v1/itemart/';

	include_once 'inc/mongo.php';

	$c = $md->itemdata;
	$data = $c->find(['_id' => ['$gte' => $id]], ['png' => false]);
	$data->sort(['_id' => 1]);
	$data->limit($count);

	foreach($data as $ed) {
                $dh = $ed['png_height'] + 10;
                if ($dh < 50) $dh = 50;

                $cur .= '<div style="float: left; width: 330px; height: ';
                $cur .= $dh;
                $cur .= 'px;">';
                $cur .= '<div style="float: left; margin-top: 10px;">';
                $cur .=  '<img src="'.$url.$ed['_id'];
                if ($e['hue'] > 0)
                        $cur .= '/'.$ed['hue'];
                $cur .= '" width="'.$ed['png_width'].'" height="'.$ed['png_height'].'" alt="'.$name." - ".$e['cost'].' ZP">';
                $cur .= '</div>';
                $cur .= '<div><strong>'.$ed['name'].'</strong></div>';
                $cur .= '<div style="font-size: 80%; margin-top: -10px;"><strong>';
                $cur .= $ed['weight'];
                $cur .= '</strong></div>';
                $cur .= '<div style="font-size: 80%; margin-top: -10px;"><strong>';
                $cur .= $ed['flags'];
                $cur .= '</strong></div>';
                $cur .= '<div style="font-size: 80%; margin-top: -10px;"><strong>';
                $cur .= $ed['height'];
                $cur .= '</strong></div>';
                $cur .= '</div>';
	}

	$rd->set($key, 60, $cur);
}

header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=3600');
header('Vary: Accept-Encoding');
header('Content-Type: text/html');
echo $cur;
?>