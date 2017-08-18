<?php

/**
* KChat -
* Author Ganesh Kandu
* Contact kanduganesh@gmail.com 
*/

function get_Data($data){
	if(isset($_SESSION[$data['config']['session']])){
		return @unserialize(base64_decode($_SESSION[$data['config']['session']]));
	}else{
		return false;
	}
}

if(file_exists("config/db.php")){
	$data['db'] = include "config/db.php";
	$opt = array(
		PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
		PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
		PDO::ATTR_EMULATE_PREPARES   => false,
		PDO::MYSQL_ATTR_FOUND_ROWS => TRUE
	);
	$data['pdo'] = new PDO("mysql:host=".$data['db']['db_host'].";dbname=".$data['db']['db_db'].";port=".$data['db']['db_port'].";charset=utf8", $data['db']['db_user'], $data['db']['db_pass'],$opt);
}

function _rand($len){
    $result = "";
    $chars = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    $charArray = str_split($chars);
    for($i = 0; $i < $len; $i++){
	    $randItem = array_rand($charArray);
	    $result .= "".$charArray[$randItem];
    }
    return $result;
}

function fcreate($file,$data){
	$fnf = explode("/",$file);
	$file = end($fnf);
	unset($fnf[count($fnf)-1]);
	$folders = "";
	foreach($fnf as $fols){
		$folders .= $fols;
		if(!is_dir($folders)){
			mkdir($folders);
		}
		$folders .= "/";
	}
	return file_put_contents($folders.$file,$data);
}

function __($o){ echo $o; }

function isAjax()
{
    $header = isset($_SERVER['HTTP_X_REQUESTED_WITH']) ? $_SERVER['HTTP_X_REQUESTED_WITH'] : null;
    return ($header === 'XMLHttpRequest');
}

function makefile($data,$src,$trg,$x){
	$xn = array();
	foreach($x as $key => $value){
		$xn['{{'.$key.'}}'] = $value;
	}
	$file = file_get_contents($data['config']['path']."/kchat/make/".$src);
	$file = strtr($file,$xn);
	fcreate($data['config']['path'].'/'.$trg,$file);
}

function js($data,$src,$trg){
	if(!file_exists($data['config']['path'].'/'.$trg)){
		$src .= '.js';
		$x = array(
			'url' => $data['config']['url'],
		);
		makefile($data,$src,$trg,$x);
	}
	return $data['config']['url']."/".$trg;
}

function css($data,$src,$trg){
	if(!file_exists($data['config']['path'].'/'.$trg)){
		$src .= '.css';
		$x = array(
			'url' => $data['config']['url'],
		);
		makefile($data,$src,$trg,$x);
	}
	return $data['config']['url']."/".$trg;
}

function CheckValid($pass,$hash){
	
	if($pass == $hash){
		return true;
	}
	return false;
}

function cactive($active,$name){
	if($active == $name){
		return" class=\"active\" ";
	}
}

function db($no){
	$bin = array();
	while(floor($no) > 0){
		$bin[] = $no%2;
		$no = $no/2;
	}
	return $bin;
}

function status($no,$tm){
	$bin = db($no);	
	if(!isset($bin[$tm])){
		$bin[0] = 0;
	}
	$dec = 0;
	$i = 0;
	$res = array();
	foreach($bin as $b){
		$res[] = $b;
		$dec += $b*pow(2,$i);
		$i++;
	}
	if(!isset($bin[$tm])){
		$bin[$tm] = 0;
	}
	if($bin[$tm] == 0){
		$dec += pow(2,$tm);
	}else{
		$dec -= pow(2,$tm);
	}
	return $dec;
}

function get_status($no,$tm){
	$bin = db($no);
	if(isset($bin[$tm])){
		return $bin[$tm];
	}else{
		return 0;
	}
}

function status_in($len,$no){
	$last = 0;
	$size = $len - 1;
	while($size >= 0){
		$last += pow(2,$size);
		$size--;
	}
	$numbers = 0;
	$nos = array();
	while($last >= $numbers){
		$arr = db($numbers);
		if(get_status($numbers,$no) == 0){
			$nos[] = $numbers;
		}
		$numbers++;
	}
	return $nos;
}

function ago($datetime, $full = false)
{
	$now = new DateTime();
    $ago = new DateTime($datetime);
    $diff = $now->diff($ago);

    $diff->w = floor($diff->d / 7);
    $diff->d -= $diff->w * 7;

    $string = array(
        'y' => 'year',
        'm' => 'month',
        'w' => 'week',
        'd' => 'day',
        'h' => 'hour',
        'i' => 'minute',
        's' => 'second',
    );
    foreach ($string as $k => &$v) {
        if ($diff->$k) {
            $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
        } else {
            unset($string[$k]);
        }
    }
    if (!$full) $string = array_slice($string, 0, 1);
    return $string ? implode(', ', $string) . ' ago' : 'just now';
}

function getip(){
    $ipaddress = '';
    if (getenv('HTTP_CLIENT_IP'))
        $ipaddress = getenv('HTTP_CLIENT_IP');
    else if(getenv('HTTP_X_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
    else if(getenv('HTTP_X_FORWARDED'))
        $ipaddress = getenv('HTTP_X_FORWARDED');
    else if(getenv('HTTP_FORWARDED_FOR'))
        $ipaddress = getenv('HTTP_FORWARDED_FOR');
    else if(getenv('HTTP_FORWARDED'))
        $ipaddress = getenv('HTTP_FORWARDED');
    else if(getenv('REMOTE_ADDR'))
        $ipaddress = getenv('REMOTE_ADDR');
    else
        $ipaddress = 'UNKNOWN';
 
    return $ipaddress;
}

function reverse($a){
	$b = array();
	for($i = count($a) - 1;$i >= 0 ; $i--){
		$b[] = $a[$i];
	}
	return $b;
}

function _msgencode($data,$txt){
	
	$pattern = "/<img alt=('|\")(.*?)('|\") class=('|\")emojioneemoji('|\") src=('|\")https:\/\/cdnjs.cloudflare.com\/ajax\/libs\/emojione\/(.*?)\/assets\/png\/(.*?).png('|\")>/";
	
	preg_match_all($pattern,$txt,$out);
	
	$rep = array();
	
	if(file_exists("cache/Pictograph.json")){
		$json = json_decode(file_get_contents("cache/Pictograph.json"),true);
	}else{
		$json = array();
	}
	
	foreach($out[0] as $key => $value){
		$json[$out[8][$key]] = array(
			'pictograph' => $data['unicode']->decode($out[2][$key]),
			'version' => $out[7][$key]
		);
		$rep[$value] = "~:".$out[8][$key].":~";
	}
	
	fcreate("cache/Pictograph.json",json_encode($json));
	
	return strtr($txt,$rep);
}

function msgencode($data,$txt){
	return trim(json_encode($txt),'"');
}

function _msgdecode($data,$txt){
	$rep = array();
	if(file_exists("cache/Pictograph.json")){
		$json = json_decode(file_get_contents("cache/Pictograph.json"),true);
	}else{
		$json = array();
	}
	foreach($json as $key => $value){
		$rep["~:".$key.":~"] = "<img alt=\"".$data['unicode']->encode($value['pictograph'])."\" class=\"emojioneemoji\" src=\"https:\/\/cdnjs.cloudflare.com\/ajax\/libs\/emojione\/".$value['version']."\/assets\/png\/".$key.".png\" >";
	}
	return strtr($txt,$rep);
}

function msgdecode($data,$txt){
	return json_decode('"'.$txt.'"');
}

function menu($data,$key,$value){
	$id = "";
	if(isset($value['id'])){
		$id = "id=\"".$value['id']."\"";
	}
	if(!isset($value['active'])){
		$value['active'] = "****";
	}
	echo "<li ".cactive($value['active'],$data['active'])." >";
		echo "<a href=\"".$value['url']."\" ".$id." >";
			echo "<span class=\"".$value['glyphicon']."\"></span>";
			echo $key;
		echo "</a>";
	echo "</li>";
}

function submenu($data,$key,$value){
    $id = "submenuid".rand(10000,99999);
	echo  "<li class=\"panel panel-default\" id=\"dropdown\">";
		echo  "<a data-toggle=\"collapse\" href=\"#".$id."\">";
			echo  "<span class=\"".$value['glyphicon']."\"></span>";
			echo  "".$key."";
			echo  "<span class=\"caret\"></span>";
		echo  "</a>";
		echo  "<div id=\"".$id."\" class=\"panel-collapse collapse\">";
			echo  "<div class=\"panel-body\">";
				echo  "<ul class=\"nav navbar-nav\">";
					sitebar($data,$value['submenu']);
				echo "</ul>";
			echo  "</div>";
		echo  "</div>";
	echo  "</li>";
}

function sitebar($data,$sitebar){
	foreach($sitebar as $key => $value){
		if(isset($value['menu'])){
			menu($data,$key,$value);
		}else{
			submenu($data,$key,$value);
		}
	}
}

function strchunk($txt,$len = 20){
	$i = 0;
	$chunk = array();
	while(substr($txt,$i*$len,$len)){
		$chunk[] = substr($txt,$i*$len,$len);
		$i++;
	}
	return $chunk;
}

function getGroup($data){
	if(isset($data['param'][0])){
		return base64_decode(urldecode($data['param'][0]));
	}else{
		return 'NO_GROUP';
	}
}

function getBrowser(){
	if(!empty($_SERVER['HTTP_USER_AGENT'])){
		return $_SERVER['HTTP_USER_AGENT'];
	}else{
		return false;
	}
}
	
function k_random($n){
	$char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$result = '';
	while($n > 0){
		$result .= $char[rand(0,61)];
		$n--;
	}
	return $result;
}

function base($n,$bf,$bt){
	$char = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
	$result = '';
	$offset = 0;
	while($n != 0){
		if(($n % $bt) < 0){
			$offset = - ($n % $bt);
		}else{
			$offset = ($n % $bt);
		}
		$result = $char[$offset].$result;
		$n = floor($n/$bt);
	}
	return ($result);
}

function kchat_rand(){
	return base((round(microtime(true) * 10000) . rand(100,999)),10,62);
}

function isReq($id){
	if(isset($_POST['req'][$id])){
		if($_POST['req'][$id] == 'true'){
			return true;
		}
	}
	return false;
}

function reqps($data){
	$ser = @unserialize(@file_get_contents('cache/reqps.temp'));
	if($data['reqps'] > $ser['s']){
		@file_put_contents('cache/reqps.cache',$ser[1 - $data['reqps']%2]);
	}
	if(isset($ser[$data['reqps']%2])){
		++$ser[$data['reqps']%2];
	}
	$ser[1 - $data['reqps']%2] = 0;
	$ser['s'] = $data['reqps'];
	$req = @serialize($ser);
	@file_put_contents('cache/reqps.temp',$req);
}

function psql($string,$data) {
	$indexed=$data==array_values($data);
	foreach($data as $k=>$v) {
		if(is_string($v)) $v="'$v'";
		if($indexed) $string=preg_replace('/\?/',$v,$string,1);
		else $string=str_replace(":$k",$v,$string);
	}
	return $string;
}

function nextQuery($fp) {
	$sql="";
	while ($line = fgets($fp, 40960)) {
		$line = trim($line);
		if (strlen($line)>1) {
			if ($line[0]=="-" && $line[1]=="-") {
				continue;
			}
		}
		$sql.=$line.chr(13).chr(10);
		if (strlen($line)>0){
			if ($line[strlen($line)-1]==";"){
				break;
			}
		}
	}
	return $sql;
}

function presql($data,$sql){
	return str_replace("%dbprefix%",$data['db']['db_prefix'],$sql);
}

function cclear(){
	$file = 'config\.htaccess';
	if(!file_exists($file)){
		file_put_contents($file,'deny to all');
	}
	if((time() - filemtime($file)) > 3){
		touch($file);
		return true;
	}else{
		return false;
	}
}