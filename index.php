<?php
/**
 *@project    DNS Monitor
 *@name       index.php
 *@author     OshynSong<dualyangsong@gmail.com>
 *@time       2014-12
 */ 
	require(dirname(__FILE__)."/common.php");	
	@session_start();
	$requestUri = parse_url($_SERVER['REQUEST_URI']);
	$pathParts = explode("/", trim($requestUri['path'], '/'));
	$param = isset($requestUri['query']) ? $requestUri['query'] : null;

	if (count($pathParts) >= 2)
	{
		App::run($pathParts[0], $pathParts[1], $param);
	}
	else if (($pathParts[0] == 'index' && count($pathParts) == 1)
			|| $pathParts[0] == "") //index controller
	{
		App::run('index', 'index');
	}
	else if ($pathParts[0] != 'index' && count($pathParts) == 1 )
	{
		App::run('index', $pathParts[0]);
	}
	
	//var_dump($pathParts);
	//if ($requestUri['path'] == "/" || $requestUri['path'] == "/index.php")
/*	{
		echo "<pre>";
		$ip = trim($vote->GetIP());
		$cookies['ip'] = array('name' => 'clientip', 'value' => $ip);
		$cookies['ipvotes'] = array('name' => 'ipvotes', 'value' => $vote->GetVoteTimesByIP($ip));
		//var_dump($vote->GetVoteTimes());
		
		$rowArr = array();
		foreach($vote->GetVoteTimes() as $row)
		{
			array_push($rowArr, implode('@', $row));
		}
		
		$cookies['votes'] = array('name' => 'votes', 'value' => implode('|', $rowArr));
		var_dump($cookies);//die;
		$vote->setVoteCookie($cookies);
		echo "</pre>";
		include_once "./index.html";
		exit;
	}*/
/*	else
	{
		echo 
<<<html
<div style='margin:200px auto;width:500px;text-align:center;font-size:40px;'>
	The page you visit is not found!
</div>
html;
	}*/