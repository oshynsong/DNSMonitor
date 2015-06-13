<?php
/**
 *@author OshynSong
 *@time   2012-12
 *@desc	  Data transport by WebSocket
 */
	require_once (__DIR__ . '/websocket.class.php');
	
	date_default_timezone_set("PRC");
	set_time_limit(0);
	error_reporting(E_ALL);
	ob_implicit_flush();

	$dataFile = '../../data/CSession_01_csv';
	
	var_dump($dataFile);
	
	function process()
	{
		$prevTime = 0;
		$timeGap  = 0;$i=0;
		$isFirstLine  = true;
		$fh = fopen('../../data/CSession_01_csv', 'r');
		while(!feof($fh))
		{
			$line = fgets($fh);
			$line = preg_split('/\|/i', $line);
			if ($isFirstLine)
			{
				$prevTime = timeToLong($line[0]);
				$isFirstLine = false;
				
				var_dump($line[1]);
				continue;
			}
			$now = timeToLong($line[0]);
			
			$timeGap = getTimeGap($prevTime, $now);
			$i++;
			//var_dump($timeGap);
			usleep($timeGap*10);
			var_dump($timeGap);var_dump($line[1]);
			
			$prevTime = $now;
			if ($i > 10)
				break;
		}
		fclose($fh);
		
		//var_dump($startAbsTime);
		
		//$t = preg_split('/\./i', $startAbsTime);
		//var_dump(strtotime($t[0]));
		//var_dump($t[1]);
		//var_dump(date('Y-m-d H:i:s', strtotime($startAbsTime)));
	}
	function timeToLong($t)
	{
		$t = preg_split('/\./i', $t);
		$intPart   = strtotime($t[0]);
		$digitPart = (float)$t[1] / pow(10, strlen($t[1]));
		return array($intPart, $digitPart);
	}
	function getTimeGap($prev, $now)
	{
		$intPart   = 0;
		$digitPart = 0.0;
		$intPart   = $now[0] - $prev[0];
		$digitPart = $now[1] * pow(10, 6) - $prev[1] * pow(10, 6);
		return abs($intPart * pow(10, 6) + $digitPart); //单位微妙
	}
	process();
	
?>