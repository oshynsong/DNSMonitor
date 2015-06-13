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
	
	class DNSMonitor extends WebSocket
	{
		private $dataFile  = '../../data/CSession_01_points';
		private $freqFile  = '../../data/freq_01';
		private $offset = 0;
		public $delayRatio = 1;
		
		private $prevTime = 0;
		private $timeGap  = 0;
		private $isFirstTime  = true;
		private $fh;
		
		public function __construct($a, $p)
		{	
			parent::__construct($a, $p);
			$this->fh = fopen($this->dataFile, 'r');
		}
		
		function process($user, $msg)
		{
			$msg = $this->unwrap($user->socket, $msg);
			$this->say('< '.$msg);			
			if (is_null($msg))
			{
				$this->disconnect($user->socket);
			}
			
			//收到消息后开始处理		
			if ($msg == "d") //实时显示数据
			{
				fseek($this->fh, $this->offset);
				$line = fgets($this->fh);
				$line = preg_split('/#/i', $line);
				if ($this->isFirstTime)
				{
					$this->prevTime = $this->timeToLong($line[0]);
					$this->isFirstTime = false;
					
					$this->say('> ' . $line[0].'#'.$line[1]);
					$this->send($user->socket, $line[0].'#'.$line[1]);
				}
				else
				{
					$now = $this->timeToLong($line[0]);
					
					$this->timeGap = $this->getTimeGap($this->prevTime, $now);
					usleep($this->timeGap * $this->delayRatio);
					$this->say('> ' . $line[0].'#'.$line[1]);
					$this->send($user->socket, $line[0].'#'.$line[1]);
					$this->prevTime = $now;
				}
				
				$this->offset = ftell($this->fh);
			}
			else if ($msg == 'p')  //暂停
			{
				$this->say('> ' . 'Pause');
			}
			else if ($msg == '1')  //显示频繁数据
			{
				$f = fopen($this->freqFile, 'r');
				while(!feof($f))
				{
					$line = fgets($f);
					$this->say('> ' . $line);
					$this->send($user->socket,  $line);
					usleep(200000);
				}
				fclose($f);
			}
			/*$prevTime = 0;
			$timeGap  = 0;      $i=0;
			$isFirstLine  = true;
			$fh = fopen($this->dataFile, 'r');
			fseek($th, $this->offset);
			while(!feof($fh))
			{
				$line = fgets($fh);
				$line = preg_split('/#/i', $line);
				if ($isFirstLine)
				{
					$prevTime = $this->timeToLong($line[0]);
					$isFirstLine = false;
					
					$this->say('> ' . $line[1]);
					$this->send($user->socket, $line[1]);
					continue;
				}
				$now = $this->timeToLong($line[0]);
				
				$timeGap = $this->getTimeGap($prevTime, $now);
				usleep($timeGap * $this->delayRatio);
				$this->say('> ' . $line[1]);
				$this->send($user->socket,  $line[1]);
				//var_dump($timeGap);var_dump($line[1]);
				
				$prevTime = $now;
				$this->offset = ftell($fh);
				$i++;
				if ($i >= 1000)
					break;
			}
			fclose($fh);
			$this->disconnect($user->socket);
			*/
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
		
	}
	
	$st = new DNSMonitor("localhost",8888);
	$st->run();
?>