<?php
/**
 *@project    DNS Monitor
 *@name       index.php
 *@author     OshynSong<dualyangsong@gmail.com>
 *@time       2014-12
 */ 

	class Index
	{
		private $app;

		private $rtn = array('status' => null, 'info' => null);

		public function __construct($app)
		{
			$this->app = $app;
		}

		public function index()
		{
			if ('GET' == $_SERVER['REQUEST_METHOD'])
			{
				header("Content-type:text/html;Charset=utf-8");
				echo file_get_contents(dirname(__FILE__).'/../view/index.html');
				return;
			}
			else if('POST' == $_SERVER['REQUEST_METHOD'])
			{
				$uname = addslashes(trim($_POST['uname']));
				$upass = addslashes(trim($_POST['upass']));
				if (isset($_SESSION['uname']) && $_SESSION['uname'] == $uname)
				{
					$this->rtn['status'] = '1';
					$this->rtn['info'] = '登录成功';
				}
				else 
				{
					$validpass = $this->app->GetPassByUname($uname);
					$_SESSION['token'] = $validpass['token'];
					$_SESSION['uid'] = $validpass['id'];
					if (empty($validpass['upass']))
					{
						$this->rtn['status'] = '0';
						$this->rtn['info'] = '用户名不存在！';
					}
					else if (($upass) != $validpass['upass'])
					{
						$this->rtn['status'] = '0';
						$this->rtn['info'] = '密码输入错误！';
					}
					else
					{
						$_SESSION['uname'] = $uname;
						$this->rtn['status'] = '1';
						$this->rtn['info'] = '登录成功';
					}
				}
				echo json_encode($this->rtn);
			}			
		}

		public function register()
		{
			if ('GET' == $_SERVER['REQUEST_METHOD'])
			{
				echo file_get_contents(dirname(__FILE__).'/../view/register.html');
				return;
			}
			else if('POST' == $_SERVER['REQUEST_METHOD'])
			{
				$user = new User();
				$user->uname = addslashes(trim($_POST['uname']));
				$unameconfirm = addslashes(trim($_POST['unameconfirm']));
				$user->upass = addslashes(trim($_POST['upass']));
				$user->email = addslashes(trim($_POST['email']));
				if (strlen($user->uname) > 30 || strlen($user->uname) < 6 || 
					!preg_match('/[a-z0-9\-]*/is', $user->uname))
				{
					$this->rtn['status'] = '0';
					$this->rtn['info'] = '注册失败,用户名不合法!';
					echo json_encode($this->rtn);
					return;
				}
				if (strlen($user->upass) > 30 || strlen($user->upass) < 6 ||
					!preg_match('/[^\'\"\`]*/is', $user->uname))
				{
					$this->rtn['status'] = '0';
					$this->rtn['info'] = '注册失败,密码不合法!';
					echo json_encode($this->rtn);
					return;
				}
				if ($user->upass != $unameconfirm)
				{
					$this->rtn['status'] = '0';
					$this->rtn['info'] = '两次输入密码不一致!';
					echo json_encode($this->rtn);
					return;
				}
				if (strlen($user->email) > 30 || strlen($user->email) < 6 || 
					!preg_match('/^[a-zA-Z0-9\-]*@([\w\-]*\.)+[\w\-]*$/', $user->email))
				{
					$this->rtn['status'] = '0';
					$this->rtn['info'] = '注册失败,邮箱格式不合法!';
					echo json_encode($this->rtn);
					return;
				}
				$user->rgtime = time();
				$uid = $this->app->Register($user);
				if ($uid > 0)
				{
					$_SESSION['uname'] = $user->uname;
					$this->rtn['status'] = '1';
					$this->rtn['info'] = '注册成功!';
				}
				else
				{
					$this->rtn['status'] = '0';
					$this->rtn['info'] = '注册失败,请重试!';
				}
				echo json_encode($this->rtn);
			}
		}
		
		public function logout()
		{
			$_SESSION = array();
			session_destroy();
		}


	}
	
	class User
	{
		public $uname;
		public $upass;
		public $email;
		public $rgtime;
		public $token;
	}