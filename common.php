<?php
/**
 *@project    DNS Monitor
 *@name       index.php
 *@author     OshynSong<dualyangsong@gmail.com>
 *@time       2014-12
 */ 
 
class App
{
	private $_dbh;
	
	private $_config;
	
	function __construct()
	{	
		date_default_timezone_set("PRC");
		set_time_limit(20*60);
		if(connection_status() != 0) die;		
		
		$config = parse_ini_file(dirname(__FILE__)."/config.ini", true);
		$this->_config = $config;
	}

	public static function run($controller, $action, $param = null)
	{
		include( dirname(__FILE__) . '/app/controller/' . $controller . '.php' );
		$con = new $controller( new App());
		$con->$action();
	}
	/*
	public function GetPassByUname($uname)
	{
		$this->ConnectDB();
		$sql = 'SELECT upass,token,id
				FROM snscollect 
				WHERE isvalid = \'1\' AND uname = \''. $uname .'\'';
		$result = mysql_query($sql, $this->_dbh);
				
		$result = mysql_fetch_assoc($result);
		
		return $result;
	}
	
	public function Register($user)
	{
		$this->ConnectDB();
		$sql = 'INSERT INTO snscollect(uname,upass,email,rgtime,isvalid)
				VALUES(	\''.$user->uname.'\', \''.$user->upass.'\', \''.$user->email.'\', 
						\''.date('Y-m-d H:i:s', $user->rgtime).'\', \'1\')';
		$status = mysql_query($sql, $this->_dbh);
		
		$uid = mysql_insert_id($this->_dbh);
		
		if ($status && $uid != 0)
		{
			$token = md5('hack' . $uid . 'day');
			$token = substr($token, 12, 8);
			$token = base64_encode($token);
			$token = substr($token, 0, 6) .'_'. $uid;
			
			$sql = sprintf(
					'UPDATE snscollect SET token = \'%s\'
					WHERE id = %d',
					mysql_real_escape_string($token),
					$uid);
			mysql_query($sql, $this->_dbh);
			if (mysql_errno($this->_dbh) != 0)
				return false;
		}
		return $uid;
	}

	public function GetWeibo()
	{
		if (isset($_SESSION['token']))
		{
			$this->ConnectDB();
			$sql = 'SELECT * FROM snscontent WHERE snsid =				
						(SELECT DISTINCT snsid FROM snsrelation WHERE uid = \''.$_SESSION['uid'].'\' 
						 AND type = \'1\')
						 AND type = \'1\' ';
			$result = mysql_query($sql, $this->_dbh);
		
			$rtn = array();
			while ($row = mysql_fetch_assoc($result)) 
			{
				array_push($rtn, $row);
			}
			
			return $rtn;
		}
	}
	
	public function GetKongjian()
	{
		if (isset($_SESSION['token']))
		{
			$this->ConnectDB();
			$sql = 'SELECT * FROM snscontent WHERE snsid =				
						(SELECT DISTINCT snsid FROM snsrelation WHERE uid = \''.$_SESSION['uid'].'\'
						 AND type = \'2\') 
						AND type = \'2\'';
			$result = mysql_query($sql, $this->_dbh);
		
			$rtn = array();
			while ($row = mysql_fetch_assoc($result)) 
			{
				array_push($rtn, $row);
			}
			
			return $rtn;
		}
	}
	
	public function GetVoteTimesByIP($ip)
	{
		$this->ConnectDB();
		$sql = 'SELECT COUNT(*) AS votetimes FROM vote 
				WHERE ip = \''.$ip.'\'';
				
		$result = mysql_query($sql, $this->_dbh);
		
		$result = mysql_fetch_assoc($result);
		return $result['votetimes'];
	}
	
	public function GetVoteTimesByUnum($unum)
	{
		$this->ConnectDB();
		$sql = 'SELECT COUNT(*) AS votetimes FROM vote 
				WHERE unum = \''.$unum.'\'';
				
		$result = mysql_query($sql, $this->_dbh);
		
		$result = mysql_fetch_assoc($result);
		return $result['votetimes'];
	}
	
	public function VoteAction($unum, $ip, $selection, $uroom)
	{
		$this->ConnectDB();
		$sql = 'INSERT INTO vote (unum, ip, selection, uroom, time)
				VALUES (\''.$unum.'\', \''.$ip.
						'\', \''.$selection.'\', \''.$uroom.'\', NOW())';
		
		$status = mysql_query($sql, $this->_dbh);
		
		return $status;
	}
	
	public function GetVoteTimes()
	{
		$this->ConnectDB();
		$sql = 'SELECT selection, COUNT(*) AS votetimes FROM vote WHERE isdel = \'0\'
				GROUP BY selection';
		
		$result = mysql_query($sql, $this->_dbh);
		$rtnArr = array();
		while($row = mysql_fetch_assoc($result))
		{
			array_push($rtnArr, $row);
		}
		return $rtnArr;
	}
	
	function GetIP()
	{
		$ip = "127.0.0.1";
		if (isset($_SERVER['HTTP_X_REAL_IP']))
		{
			$ip = $_SERVER["HTTP_X_REAL_IP"];
		}
		else if (isset($_SERVER["HTTP_X_FORWARDED_FOR"]))
		{
			$ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
		}
		else if (isset($_SERVER["REMOTE_ADDR"]))
		{
			$ip = $_SERVER["REMOTE_ADDR"];
		}
		return $ip;
	}
	
	function setVoteCookie($cookies)
	{
		foreach($cookies as $cookie)
		{
			setcookie($cookie['name'], $cookie['value'], time() + 5*60, '/');  //'oshynsong.duapp.com'
		}
	}
	*/
	function ConnectDB()
	{
		try
		{
			$this->_dbh = @mysql_connect(
								$this->_config['db']['host'].":".$this->_config['db']['port'],
								$this->_config['db']['user'],
								$this->_config['db']['password']
							);
			mysql_select_db($this->_config['db']['dbname'], $this->_dbh);
		}
		catch(Exception $e)
		{
			die('Connection db failed: ' . $e->getMessage());
		}
	}
	
}