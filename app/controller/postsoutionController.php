<?php
	require_once('curlController.php');
	class postsoutionController extends curlFacade 
	{
		private $sub;
		private $MODEL;
		public function __construct(& $sub,$oj)
		{
			$this->MODEL=MysqlDatabase::getMysqlDatabase();
			$this->sub=& $sub;
			if($oj=='uva')$this->uva();
			if($oj=='uvalive')$this->uvalive();
			if($oj=='codeforces')$this->codeforces();
			if($oj=='spoj')$this->spoj();
		}
		protected function uva_live_login($url1,$url2,$oj)
		{
				$response=$this->grab_page($url1,$oj);
				if (!(strpos($response, 'Logout') !== false)&&(strpos($response, 'Login') !== false))
				{
					
					$exploded = explode('<input type="hidden" name="cbsecuritym3" value="', $response);
					$cbsecuritym3 = explode('"', $exploded[1])[0];
					
					$exploded = explode('<input type="hidden" name="return" value="', $response);
					$return = explode('"', $exploded[1])[0];
					
					$exploded = explode('<input type="hidden" name="cbsecuritym3" value="', $response);
					$exploded = explode('<input type="hidden" name="', $exploded[1]);
					$any = explode('"', $exploded[1])[0];
				
					
					$params = array(
						'username' => 'Our_Judge',
						'passwd' => 'moha4422med',
						'op2' => 'login',
						'lang' => 'english',
						'force_session' => '1',
						'return' => $return,
						'message' => '0',
						'loginfrom' => 'loginmodule',
						'cbsecuritym3' =>  $cbsecuritym3,
						 $any => '1',
						'remember' => 'yes',
						'Submit' => 'Login',
					);
					
					$data=http_build_query($params);
					$this->login($url2,http_build_query($params),$oj);
					
				}	
		}
		function uva_live_submit($url,$oj)
		{
			$this->sub['Language']=substr($_POST["lang"],1,50);
			$this->sub['Soultion']=$_POST["solution"];
			$this->sub['Problem_id']=$_POST["id"];
			
			$code=$_POST["solution"];
			$lang=substr($_POST["lang"],0,1);
			$pro_id=$_POST['IID'];

			$params = array(
			'problemid' => $pro_id,
			'category' => '',
			'language' => $lang,	
			'code' => $code,
			'codeupl' => '',
			);
			$data=http_build_query($params);
			$response=$this->post_data($url,$data,$oj,true);
			if(substr_count($response, 'Submission+received+with+ID')==0)
			{
				$exploded = explode('mosmsg=', $response);
				$this->sub['Verdict'] = urldecode(explode('"', $exploded[2])[0]);
			}

		}
		private function uva()
		{
			if(!isset($_POST["id"])||!isset($_POST["IID"])||!isset($_COOKIE["user_handle"])&&!isset($_POST["solution"]))
			{
					redirect("http://localhost/our3/index.php");
			}
			$response=$this->grab_page('https://uva.onlinejudge.org','uva');
			if (!(strpos($response, 'UVa Online Judge - Offline') !== false)&&strlen($response)!=0)
			{
				$this->uva_live_login('https://uva.onlinejudge.org','https://uva.onlinejudge.org/index.php?option=com_comprofiler&task=login','uva');
				$this->uva_live_submit('https://uva.onlinejudge.org/index.php?option=com_onlinejudge&Itemid=8&page=save_submission','uva');
			}
			else
			{
				$this->sub['Language']=substr($_POST["lang"],1,50);
				$this->sub['Soultion']=$_POST["solution"];
				$this->sub['Problem_id']=$_POST["id"];
				$this->sub['Verdict']="Judge Error";
			}
		}
		private function uvalive()
		{
			if(!isset($_POST["id"])||!isset($_POST["IID"])||!isset($_COOKIE["user_handle"])&&!isset($_POST["solution"]))
			{
					redirect("http://localhost/our3/index.php");
			}
			$this->uva_live_login('https://icpcarchive.ecs.baylor.edu','https://icpcarchive.ecs.baylor.edu/index.php?option=com_comprofiler&task=login','uvalive');
			$this->uva_live_submit('https://icpcarchive.ecs.baylor.edu/index.php?option=com_onlinejudge&Itemid=8&page=save_submission','uvalive');
		}
		private function codeforce_login()
		{
			$response=$this->grab_page('http://codeforces.com','codeforces');
			if (!(strpos($response, 'Logout') !== false)) {

				$response=$this->grab_page('http://codeforces.com/enter','codeforces');
				
				$exploded = explode("name='csrf_token' value='", $response);
				$token = explode("'/>", $exploded[2])[0];
				
				$params = array(
					'csrf_token' => $token,
					'action' => 'enter',
					'ftaa' => '',
					'bfaa' => '',
					'handle' => 'Our_Judge',
					'password' => 'moha4422med',
					'remember' => true,
				);
				$this->login('http://codeforces.com/enter',http_build_query($params),'codeforces');
			}
		}
		private function codeforces_submit()
		{
			
			$this->sub['Language']=substr($_POST["lang"],2,50);
			$this->sub['Soultion']=$_POST["solution"];
			$this->sub['Problem_id']=$_POST["id"];
			$s_num=$this->MODEL->count_soulution($this->sub['Soultion']);
			$space='';
			for($i=0;$i<$s_num;$i++)$space.=' ';
			$contestId = $_POST["CID"];
			$submittedProblemIndex = $_POST["IID"];
			$var=substr($_POST["lang"],0,2);
			$programTypeId=$var;
			if($var[0]==0)$programTypeId=$var[1];
			$source =($space.chr(10).$_POST["solution"]);
			
			
			$response=$this->grab_page("codeforces.com/contest/{$_POST['CID']}/submit","codeforces");
		
			
			$exploded = explode("name='csrf_token' value='", $response);
			$token = explode("'/>", $exploded[2])[0];

			$params = array(
				'csrf_token' => $token,
				'action' => 'submitSolutionFormSubmitted',
				'ftaa' => '',
				'bfaa' => '',
				'submittedProblemIndex' => $submittedProblemIndex,
				'programTypeId' => $programTypeId,
				'source' => $source,
				'sourceFile' => '',
			);

			$response=$this->post_data("codeforces.com/contest/{$_POST['CID']}/submit?csrf_token=".$token,http_build_query($params),"codeforces",true);
			if (substr_count($response, 'My Submissions')!=2)
			{
				$exploded = explode('<span class="error for__source">', $response);
				$this->sub['Verdict'] = explode("</span>", $exploded[1])[0];
			}
		}
		private function codeforces()
		{
			if(!isset($_POST["id"])||!isset($_POST["CID"])||!isset($_POST["IID"])||!isset($_COOKIE["user_handle"])&&!isset($_POST["solution"]))
			{
				redirect("http://localhost/our3/index.php");
			}
			$this->codeforce_login();
			$this->codeforces_submit();
		}
		function spoj_login()
		{
				$response=$this->grab_page('http://www.spoj.com','spoj');
				if (!(strpos($response, 'sign-out') !== false))
				{
					
					$params = array(
						'next_raw' => "/",
						'autologin' => '1',
						'login_user' => 'our_judge',
						'password' => 'moha4422med'
					);

					$data=http_build_query($params);
					$this->login('http://www.spoj.com/login',$data,'spoj');
					
				}	
		}
		function multiexplode ($delimiters,$string) 
		{
			$ready = str_replace($delimiters, $delimiters[0], $string);
			$launch = explode($delimiters[0], $ready);
			return  $launch;
		}
		function spoj_submit()
		{
			$x=0;
			for($i=0;$i<strlen($_POST["lang"]);$i++)
			{
				if(is_numeric($_POST["lang"][$i]))$x++;
				else break;
			}
			$this->sub['Language']=substr($_POST["lang"],$x,strlen($_POST["lang"]));
			$this->sub['Soultion']=$_POST["solution"];
			$this->sub['Problem_id']=$_POST["id"];
			$lang=substr($_POST["lang"],0,$x);
		
			$params = array(
			'subm_file' => '',
			'file' => $_POST["solution"],
			'lang' => $lang,
			'problemcode' => $_POST['IID'],
			'submit' => 'Submit!',
			);
			$data=http_build_query($params);
			$response=$this->post_data('http://www.spoj.com/submit/complete/',$data,'spoj',true);
			if(substr_count($response, 'Solution submitted!')==0)
			{
				$exploded = explode('<p align="center">', $response);
				$this->sub['Verdict'] = $this->multiexplode(array("!","."), $exploded[1])[0];
			}
		}
		private function spoj()
		{
			if(!isset($_POST["id"])||!isset($_POST["IID"])||!isset($_POST["IID"])||!isset($_COOKIE["user_handle"])&&!isset($_POST["solution"]))
			{
				redirect("http://localhost/our3/index.php");
			}
			$this->spoj_login();
			$this->spoj_submit();
		}
		
	}



?>