<?php 
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');

class loginClassAction extends apiAction
{
	//1-1.登录接口
	public function checkAction()
	{

		$adminuser	= str_replace(' ','',$this->rock->jm->base64decode($this->post('user')));
		$adminpass	= $this->rock->jm->base64decode($this->post('pass'));
		$arr 		= m('login')->start($adminuser, $adminpass);
		if(is_array($arr)){
			$arrs = array(
				'uid' 	=> $arr['uid'],
				'name' 	=> $arr['name'],
				'user'	=> $arr['user'],
				'ranking'	=> $arr['ranking'],
				'deptname'  => $arr['deptname'],
				'deptallname' => $arr['deptallname'],
				'face'  	=> $arr['face'],
				'apptx'  	=> $arr['apptx'],
				'token'  	=> $arr['token'],
				'title'		=> getconfig('apptitle'),
				'weblogo'	=> getconfig('weblogo'),
			);
			
			$uid 	= $arr['uid'];
			$name 	= $arr['name'];
			$user 	= $arr['user'];
			$token 	= $arr['token'];
			m('login')->setsession($uid, $name, $token, $user);
			$this->showreturn($arrs);
		}else{
			$this->showreturn('', $arr, 201);
		}
	}

	public function loginexitAction()
	{
		m('login')->exitlogin();
		$this->showreturn('');
	}
	/*---------------------------------------------------------------------------*/
	//  1-1.登录接口
	public function  checksAction(){
		  // $adminuser=$this->post('phone');
		  // $password=$this->post('password');

		 $data=json_decode($this->post('data'),true);
		 $adminuser=$data['phone'];
		 $password=$data['password'];

		 //先判断输入的是否为手机号
		
	 		$sql='SELECT *from hrt_admin where user='.$adminuser;
			$r=$this->db->query($sql);
			$row=mysqli_fetch_array($r);
			
		if($row>0){
			if($password==trim($row['pass'])){
				$arr0=array(
					'user'=>$adminuser,
				);
				$r0=$this->db->getone('[Q]admin',$arr0);
				if($r0){
					$arr=array(
						'code'=>200,
						'msg'=>'成功',
						'userid'=>$r0['id'],
						'headiconurl'=>FACE.$r0['face'],
						'truename'=>$r0['name'],
						'isadmin'=>$r0['isadmin'],
						'departmentname'=>$r0['deptname'],
						'positionname'=>$r0['ranking'],
						'permission'=>array(
							'membermanagepermission'=>$row['isok'],
							'workreportmanagepermission'=>$row['isdaily'],
							'reimbursemanagepermission'=>$row['iscpower'],
							'allowpublishnoticepermission'=>$row['isinfor'],
							'allowapplyreimbursepermission'=>$row['isconsume'],
							'allowscorestorefrontpermission'=>$row['isdeptscore'],
							'allowlookchargepermission'=>$row['isexpense'],
							'allowpublishsurveypermission'=>$row['issurvey'],
							'allowmodifycompanycloudpermission'=>$row['clouddisk'],
						),
					);
					//从dailyrule表中判断是否有查看工作汇报权限
					$s_sql='SELECT *from hrt_dailyrule where  uid='.$r0['id'];
					$s_r=$this->db->query($s_sql);
					$s_row=mysqli_fetch_array($s_r);
					if($s_row >0){
						$arr['permission']['allowlookworkreportpermission']="1";
					}else{
						$arr['permission']['allowlookworkreportpermission']="0";
					}

					$this->returnjson($arr);
				}else{
					$this->showreturn('','失败',201);
				}

				 $this->returnjson($arr);
			}else{
				$this->showreturn('','密码错误',201);
			}
		}else{
			$this->showreturn('','用户不存在',201);
		}
	}
// 生成六位随机数函数
	public  function  getCode($length=6){
		$characters = '0123456789';
		$randomCode = '';
		for ($i = 0; $i < $length; $i++) {
			$randomCode .= $characters[rand(0, strlen($characters) - 1)];
		}
		return $randomCode;
	}
//发送验证码
	public function send($mobile,$content)
	{
		
		$post_data = array(
			'id'  => 'tianhongo',
			'pwd' => 'x473537',
			'content'  => iconv("utf-8","gb2312",$content),
			'to'   => $mobile,
		);

		$url    ='http://service.winic.org:8009/sys_port/gateway/index.asp';
		$string = '';
		foreach ($post_data as $k => $v)
		{
		   $string .="$k=".urlencode($v).'&';
		}

		$post_string = substr($string,0,-1);

		$ch = curl_init();
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_URL,$url);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post_string);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //如果需要将结果直接返回到变量里，那加上这句。
		$result = curl_exec($ch);
		$success = explode('/', $result)[0] === '000';
		if ($success) {
			return 1;
		} else {
			return 0;
		}

	}

	//1-4新用户邀请
	public  function  newMemberInviteAction(){
			$data=json_decode($this->post('data'),true);
			$type=$data['type'];
			$truename=$data['truename'];
			$phone=$data['phone'];
			$deptid=$data['departmentId'];
			$ranking=$data['position'];
			$isstore=$data['isStoreManager'];
			$sex=$data['sex'];
		    $invitecode=$this->getCode(6);
		 	$date=date('Y-m-d H:i:s',time());

			// $truename=$this->post('truename');
			// $type=$this->post('type');
			// $phone=$this->post('phone');
			// $deptid=$this->post('departmentId');
			// $ranking=$this->post('position');
			// $isstore=$this->post('isStoreManager');
			// $sex=$this->post('sex');
		 //    $invitecode=$this->getCode(6);
		 // 	$date=date('Y-m-d H:i:s',time());
		 	if($type==1){
		 		//发送验证码

			           //先判断admin是否已经为成员 或者是否在邀请的列表中
						$sql1='SELECT *from hrt_admin where user='.$phone;
						$r1=$this->db->query($sql1);
						$row1=mysqli_num_rows($r1);
					 	
					
						$sql2='SELECT *from hrt_admin_invite where  phone='.$phone;
						$r2=$this->db->query($sql2);
						$row2=mysqli_num_rows($r2);
						
				

						if($row1 >0 ||  $row2 >0){
					
							 $this->showreturn('','该用户已被邀请或激活',201);
						}else{


							$arr0=array(
								'truename'=>$truename,
								'phone'=>$phone,
								'deptid'=>$deptid,
								'position'=>$ranking,
								'isstore'=>$isstore,
								'sex'=>$sex,
								'invitecode'=>$invitecode,
								'date'=>$date,
							);
							

							$r=$this->db->record('[Q]admin_invite',$arr0);
							if($r){

								// echo 111;
								// 调用验证码的方法
								$content='邀请码为'.$invitecode;
								$sentMessage=$this->send($phone,$content);
								if($sentMessage==1){

									$arr=array(
											'code'=>200,
											'msg'=>'成功',
								
										);
									$this->returnjson($arr);
								}else{

									$this->showreturn('','失败',201);
							    }
							}else{
									$this->showreturn('','新用户发送邀请码失败',201);
							}
						}

		 	}else{
		 			//直接添加用户
		 				$sql1='SELECT *from hrt_admin where user='.$phone;
						$r1=$this->db->query($sql1);
						$row1=mysqli_num_rows($r1);
						if($row1 >0){
								$this->showreturn('','该手机号已被注册',201);
						}else{
							// 根据部门id查询出部门名称
							$d_sql='SELECT name from  hrt_dept where id='.$deptid;
							$d_r=$this->db->query($d_sql);
							$d_row=mysqli_fetch_array($d_r);

							$arr0=array(
								'name'=>$truename,
								'user'=>$phone,
								'pass'=>'e10adc3949ba59abbe56e057f20f883e',
								'deptid'=>$deptid,
								'deptname'=>$d_row['name'],
								'ranking'=>$ranking,
								'isstoremanager'=>$isstore,
								'sex'=>$sex,	
								'adddt'=>$date,
								'star'=>1,
							);

							$r=$this->db->record('[Q]admin',$arr0);
							if($r){

							$new_id=$this->db->insert_id();
							$ids='user'.$new_id;
							$arr1_3=array(
							'id'=>$new_id,
							'name'=>$truename,	

							);
							$r1_3=$this->db->record('[Q]userinfo',$arr1_3);
							
							//第三方添加
							$rr=$this->adduser($truename,$ids);
							if($rr==1){

									$arr=array(
										'code'=>200,
										'msg'=>'成功',

									);
									$this->returnjson($arr);
							
							}else{
								$this->showreturn('','添加用户失败',201);
							}


						}else{
							$this->showreturn('','添加用户失败',201);
						}
					 

		 	}

	}
}
	/*-----------------------------------新增的用户申请---------------------------------------*/
//申请加入(注册)
	public  function    applyRegisterAction(){
			$data=json_decode($this->post('data'),true);
			$truename=$data['truename'];
			$password=$data['password'];
			$phone=$data['phone'];
			$deptid=$data['departmentId'];
			$ranking=$data['position'];
			$isstore=$data['isStoreManager'];
			$sex=$data['sex'];
		    // $invitecode=$this->getCode(6);
		 	$date=date('Y-m-d H:i:s',time());

			// $truename=$this->post('truename');
			 //$password=$this->post('password');
			// $phone=$this->post('phone');
			// $deptid=$this->post('departmentId');
			// $ranking=$this->post('position');
			// $isstore=$this->post('isStoreManager');
			// $sex=$this->post('sex');
		 	// $invitecode=$this->getCode(6);
			// $date=date('Y-m-d H:i:s',time());

		 		$sql1='SELECT *from hrt_admin where user='.$phone;
				$r1=$this->db->query($sql1);
				$row1=mysqli_num_rows($r1);
					 	
			
				$sql2='SELECT *from hrt_admin_invite where  phone='.$phone;
				$r2=$this->db->query($sql2);
				$row2=mysqli_num_rows($r2);
				
				

				if($row1 >0 ||  $row2 >0){
					 $this->showreturn('','该用户已被注册',201);
				}else{

							$arr0=array(
								'truename'=>$truename,
								'phone'=>$phone,
								'pass'=>$password,
								'deptid'=>$deptid,
								'position'=>$ranking,
								'isstore'=>$isstore,
								'sex'=>$sex,
								'date'=>$date,
							);
							

							$r=$this->db->record('[Q]admin_invite',$arr0);
							if($r){

								// 调用验证码的方法
								// $content='邀请码为'.$invitecode;
								// $sentMessage=$this->send($phone,$content);					
									$arr=array(
											'code'=>200,
											'msg'=>'成功',
								
										);
									$this->returnjson($arr);
								
							}else{
									$this->showreturn('','注册失败',201);
							}

				}

	}

	// 同意申请
	public  function  agreeApplyAction(){

			$data=json_decode($this->post('data'),true);
			$id=$data['id'];
			// $uid=$data['userId'];

			// $id=$this->post('id');

			$arr0=array(
				'status'=>1,
				);
			$f0=array(
				'id'=>$id,
				);
			$r0=$this->db->record('[Q]admin_invite',$arr0,$f0);
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
				);
			if($r0){


				$sql1='SELECT *from hrt_admin_invite  where id='.$id;
				$r1=$this->db->query($sql1);
				$row1=mysqli_fetch_array($r1);
				$dp_sql='SELECT *from hrt_dept where id='.$row1['deptid'];
				$d_r=$this->db->query($dp_sql);
				$row=mysqli_fetch_array($d_r);

					$arr1_2=array(
						'name'=>$row1['truename'],
						'user'=>$row1['phone'],
						'pass'=>$row1['pass'],
						'deptid'=>$row1['deptid'],
						'deptname'=>$row['name'],
						'ranking'=>$row1['position'],
						'isstoremanager'=>$row1['isstore'],
						'adddt'=>date('Y-m-d H:i:s',time()),
						'sex'=>$row1['sex'],
						'star'=>1,  //默认为1星用户
					);
					    
						// print_r($arr1_2);
					$r1_2=$this->db->record('[Q]admin',$arr1_2);
					if($r1_2){
							$new_id=$this->db->insert_id();
							$ids='user'.$new_id;
							$arr1_3=array(
							'id'=>$new_id,
							'name'=>$row1['truename'],	

							);
							$r1_3=$this->db->record('[Q]userinfo',$arr1_3);
							$rr=$this->adduser($row1['truename'],$ids);
							if($rr==1){

									$arr=array(
										'code'=>200,
										'msg'=>'成功',

									);
									$this->returnjson($arr);
							}else{
								$this->showreturn('','写入失败',201);
							}
							
					}else{
							$this->showreturn('','失败',201);
					}
		
			}else{
				$this->showreturn('','同意申请失败',201);
			}

	}
 // 驳回申请
	public  function  dismissApplyAction(){
			$data=json_decode($this->post('data'),true);
			$id=$data['id'];
			$uid=$data['userId'];

			// $id=$this->post('id');

			$arr0=array(
				'id'=>$id,
				);
			$r0=$this->db->delete('[Q]admin_invite',$arr0);
				$arr=array(
				'code'=>200,
				'msg'=>'成功',
				);
			if($r0){
				$this->returnjson($arr);
			}else{
				$this->showreturn('','驳回申请失败',201);
			}



	}




/*----------------------------------------------------------------------------------------------*/
//  1-3激活帐号
	public  function   activatePhoneAction(){

			$data=json_decode($this->post('data'),true);
			$truename=$data['truename'];
			$phone=$data['phone'];
			$password=$data['password'];
			$invitecode=$data['inviteCode'];


			// $truename=$this->post('truename');
			// $phone=$this->post('phone');
			// $password=$this->post('password');
			// $invitecode=$this->post('inviteCode');


			$sql1='SELECT *from hrt_admin_invite  where phone='.$phone;
			$r1=$this->db->query($sql1);
			$row1=mysqli_fetch_array($r1);
			if($row1>0){
				
				$n=trim($row1['truename']);
				$c=trim($row1['invitecode']);

					if($truename==$n && trim($invitecode)==$c){
					
						//开始激活
//						1.先把状态改为1
						$arr1_1=array(
							'status'=>1,
						);
						$f1_1=array(
							'phone'=>$phone,
						);
						$r1_1=$this->db->record('[Q]admin_invite',$arr1_1,$f1_1);
//						2.把数据写入到admin表中
						// 根据部门id查询出部门名称
						$dp_sql='SELECT *from hrt_dept where id='.$row1['deptid'];
						$d_r=$this->db->query($dp_sql);
						$row=mysqli_fetch_array($d_r);

						$arr1_2=array(
							'name'=>$row1['truename'],
							'user'=>$row1['phone'],
							'pass'=>$password,
							'deptid'=>$row1['deptid'],
							'deptname'=>$row['name'],
							'ranking'=>$row1['position'],
							'isstoremanager'=>$row1['isstore'],
							'adddt'=>date('Y-m-d H:i:s',time()),
							'sex'=>$row1['sex'],
							'star'=>1,  //默认为1星用户
						);
					    
						// print_r($arr1_2);
						$r1_2=$this->db->record('[Q]admin',$arr1_2);


						if($r1_2){
							$new_id=$this->db->insert_id();
							$ids='user'.$new_id;
							$arr1_3=array(
							'id'=>$new_id,
							'name'=>$row1['truename'],	

							);
							$r1_3=$this->db->record('[Q]userinfo',$arr1_3);
							
							//第三方添加
							$rr=$this->adduser($truename,$ids);
							if($rr==1){

									$arr=array(
										'code'=>200,
										'msg'=>'成功',

									);
									$this->returnjson($arr);
							}

							
						}else{
							$this->showreturn('','失败',201);
						}

					}else{
						$this->showreturn('','失败',201);
					}
			}else{
				$this->showreturn('','此用户未被邀请',201);
			}
	}

			//1-2-a.找回密码/

public  function    getPasswordAction(){
			// $phone=$this->post('phone');

			$data=json_decode($this->post('data'),true);
			$phone=$data['phone'];
			//判断手机号必须是已注册过的号码
			$sql='SELECT id from hrt_admin where user='.$phone;
			$result=$this->db->query($sql);
			$num=mysqli_num_rows($result);
			if($num>0){
					$invitecode=$this->getCode(6);
					$opdate=date('Y-m-d H:i:s',time());
					$arr0=array(
						'phone'=>$phone,
						'invitecode'=>$invitecode,
						'opdate'=>$opdate,
					);
					$r0=$this->db->record('[Q]admin_password',$arr0);
					if($r0){

							$content='验证码为'.$invitecode;
							$sentMessage=$this->send($phone,$content);
							if($sentMessage==1){
									$arr=array(
											'code'=>200,
											'msg'=>'成功',
									);
								$this->returnjson($arr);
							}else{
								$arr=array(
											'code'=>201,
											'msg'=>'发送验证码失败',
									);
								$this->returnjson($arr);
							}
							
					}else{
						$this->showreturn('','失败',201);
					}
				
			}else{
				$this->showreturn('','该手机号未注册过',201);	
			}
			
}

// 1-2-b.提交新密码
public function setNewPasswordAction(){

			$data=json_decode($this->post('data'),true);
			$phone=$data['phone'];
			$password=$data['password'];
			$msgVerify=$data['msgVerify'];

			// $phone=$this->post('phone');
			// $password=$this->post('password');
			// $msgVerify=$this->post('msgVerify');

//			1.比对验证码
			$sql1='SELECT  *from hrt_admin_password where phone='.$phone;
			$r1=$this->db->query($sql1);
			$row1=mysqli_fetch_array($r1);
			if($row1>0 ){
					if($msgVerify==$row1['invitecode']){
						$arr1=array(
							'pass'=>$password,
						);
						$f1=array(
							'user'=>$phone,
						);
						$r1=$this->db->record('[Q]admin',$arr1,$f1);
						if($r1){
							// 删除验证码表中的记录,避免重复
							$dele=array(
								'phone'=>$phone,
								);
							$dr=$this->db->delete('[Q]admin_password',$dele);
							if($dr){
									$arr=array(
										'code'=>200,
										'msg'=>'成功',
									);
									$this->returnjson($arr);
							}else{
								$this->showreturn('','失败',201);
							}
							
						}else{
							$this->showreturn('','失败',201);
						}
					}else{
						$this->showreturn('','输入的验证码错误',201);
					}

			}else{
				$this->showreturn('','失败',201);
			}

}
// 1-5更换手机号
// 1-5.a  发送验证码接口
public  function     sendMsgVerifyAction(){
			// $phone=$this->post('phone');

			$data=json_decode($this->post('data'),true);
			$phone=$data['phone'];
			//判断新手机号未注册过
			$sql='SELECT id from  hrt_admin where user='.$phone;
			$result=$this->db->query($sql);	
			$row=mysqli_num_rows($result);
			if($row>0){
					$this->showreturn('','该手机号已被注册',201);
			}else{
						$invitecode=$this->getCode(6);
						$opdate=time();
						$sxdate=time()+600;//10分钟失效
						$arr0=array(
							'phone'=>$phone,
							'msgverify'=>$invitecode,
							'opdate'=>$opdate,
							'sxdate'=>$sxdate,
						);
						$r0=$this->db->record('[Q]admin_replacephone',$arr0);
						if($r0){

								$content='验证码为'.$invitecode;
								$sentMessage=$this->send($phone,$content);
								if($sentMessage==1){
										$arr=array(
												'code'=>200,
												'msg'=>'成功',
										);
									$this->returnjson($arr);
								}
								
						}else{
							$this->showreturn('','失败',201);
						}
			}
		
}


public  function  replacePhoneAction(){
		$data=json_decode($this->post('data'),true);
		$uid=$data['userId'];	
		$newphone=$data['phone'];	
		$password=$data['password'];
		$msgVerify=$data['msgVerify'];	
		$date=time();

			// $uid=$this->post('userId');
			// $newphone=$this->post('phone');
			// $password=$this->post('password');
			// $msgVerify=$this->post('msgVerify');
			// $date=time();

		 // 1.比对密码

			$sql_1='SELECT pass from hrt_admin where id='.$uid;
			$r_1=$this->db->query($sql_1);
			$row_1=mysqli_fetch_array($r_1);
			if($password==$row_1['pass']){
				//2 比对验证码和时效性

					$sql_2='SELECT msgverify,sxdate from  hrt_admin_replacephone where phone='.$newphone;
					$r2=$this->db->query($sql_2);
					$row_2=mysqli_fetch_array($r2);
				
					if($msgVerify==$row_2['msgverify'] && $date < $row_2['sxdate']){
						$arr1=array(
						   'user'=>$newphone,
						);
						$f1=array(
							'id'=>$uid,
						);
						$r1=$this->db->record('[Q]admin',$arr1,$f1);
						if($r1){

							$arr0=array(
								'phone'=>$newphone,
								);
							$r0=$this->db->delete('[Q]admin_replacephone',$arr0);
							$arr=array(
								'code'=>200,
								'msg'=>'成功',
								);	
							$this->returnjson($arr);
						}else{
							$this->showreturn('','失败',201);
						}	

					}else{
						echo 111;
						$arr0=array(
								'phone'=>$newphone,
								);
					    $r0=$this->db->delete('[Q]admin_replacephone',$arr0);
						$this->showreturn('','验证码错误或输入的验证码已超时',201);
					}

			}else{
				$this->showreturn('','原密码输入错误',201);
			}

}

//1-25.个人信息详情接口
	public function showdetailAction()
	{
//		远程测试接口使用
		// $id =$this->post('otherUserId');

		$data = json_decode($this->post('data'), true);
		$id = $data['otherUserId'];
		$userid=$data['userId'];

		$msg = '';

		$arr0=array(
			'uid'=>$userid,
			'cid'=>$id,
		);
		$result=$this->db->rows('[Q]collect',$arr0);
		if($result==1){
            $iscollected=1;
		}else{
			$iscollected=0;
		}

		$arrs = array(
			'id' => $id,
		);
		$fields1 = '`isclosecontact`,`iscollected`,`face`,`name`,`user`,
		`sex`,`deptid`,`deptname`,`ranking`,`isstoremanager`,`jobcode`,
		`contacts`,`email`,`address`,`star`';
		$fields2 = '`birthday`,`jiguan`,`minzu`,`identitycard`,`hunyin`,
		`issue`,`housedizhi`,`conperson`,`connumber`,`workdate`,`graduatedate`';
		$public = $this->db->getone('[Q]admin', $arrs, $fields1);
		$private = $this->db->getone('[Q]userinfo', $arrs, $fields2);
		$arr = array();
		if (is_array($public) && is_array($private)) {
			if(empty($private['conperson']) && empty($private['connumber'])){
				$arr = array(
						'code' => 200,
						'msg' => '成功',
						'publicuserinfo' => array(
							'isclosecontact' => $public['isclosecontact'],
							'iscollected' => $iscollected,
							'headiconurl' => FACE.$public['face'],
							'truename' => $public['name'],
							'registphone' => $public['user'],
							'sex' => $public['sex'],
							'departmentid' => $public['deptid'],
							'departmentname' => $public['deptname'],
							'positionname' => $public['ranking'],
							'isstoremanager' => $public['isstoremanager'],
							'jobcode' => $public['jobcode'],
							'contactlist' => json_decode($public['contacts'],true),
							'email' => $public['email'],
							'address' => $public['address'],
							'starlevel'=>$public['star'],
						),
						'privateuserinfo' => array(
							'birthday' => $private['birthday'],
							'jiguan' => $private['jiguan'],
							'minzu' => $private['minzu'],
							'identitycard' => $private['identitycard'],
							'ismarray' => $private['hunyin'],
							'ischild' => $private['issue'],
							'homeaddress' => $private['housedizhi'],
							'urgentcontact' =>null,
							'entrydate' => $private['workdate'],
							'graduatedate' => $private['graduatedate'],
						),
					);
			}else{
				$arr=array(
						'code' => 200,
						'msg' => '成功',
						'publicuserinfo' => array(
							'isclosecontact' => $public['isclosecontact'],
							'iscollected' => $iscollected,
							'headiconurl' => FACE.$public['face'],
							'truename' => $public['name'],
							'registphone' => $public['user'],
							'sex' => $public['sex'],
							'departmentid' => $public['deptid'],
							'departmentname' => $public['deptname'],
							'positionname' => $public['ranking'],
							'isstoremanager' => $public['isstoremanager'],
							'jobcode' => $public['jobcode'],
							'contactlist' => json_decode($public['contacts'],true),
							'email' => $public['email'],
							'address' => $public['address'],
							'starlevel'=>$public['star'],
						),
						'privateuserinfo' => array(
							'birthday' => $private['birthday'],
							'jiguan' => $private['jiguan'],
							'minzu' => $private['minzu'],
							'identitycard' => $private['identitycard'],
							'ismarray' => $private['hunyin'],
							'ischild' => $private['issue'],
							'homeaddress' => $private['housedizhi'],
							'urgentcontact' => array(
								'name' => $private['conperson'],
								'phone' => $private['connumber'],
							),
							'entrydate' => $private['workdate'],
							'graduatedate' => $private['graduatedate'],
						),
					);
			}
	
			$this->returnjson($arr);
		} else {
			$this->showreturn('', $msg = '失败', 201);
		}
	}

	//1-26.查看公司人员组织结构数据接口
	public function showdeptAction()
	{
//      远程测试接口
//  		$id = $this->post('departmentId');

		$data=json_decode($this->post('data'),true);
		$id=$data['departmentId'];
        if($id==null){
            $type = 1;
        }else{
            $data = explode(",", $id);
            if(count($data)==1){
                $type = 2;
            }else{
                $type = 3;
            }
        }
		$arr=array(
			'code'=>200,
			'msg'=>'成功',
		);
		if($type==1){
			//表示获取最顶层的结构
			$sql_1='SELECT id,name from hrt_dept where pid=1';
			$r_1=$this->db->query($sql_1);

			if($r_1){
					foreach($r_1 as $v1){
						$arr['departmentlist'][]=array(
							'id'=>$v1['id'],
							'name'=>$v1['name'],
							);
						}
			}else{
				$arr['departmentlist']=null;
			}

			$sql_2='SELECT id,face,name,ranking from  hrt_admin  where deptid=0';

			$r_2=$this->db->query($sql_2);

			if($r_2){
					foreach($r_2 as $v2){
						$arr['userlist'][]=array(
							'id'=>$v2['id'],
							'headiconurl'=>FACE.$v2['face'],
							'truename'=>$v2['name'],
							'positionname'=>$v2['ranking'],
						);
					}
			}else{
				$arr['userlist']=null;
			}

		}else if($type==2){

			$sql_1='SELECT id,name from hrt_dept where pid='.$id;
			$r_1=$this->db->query($sql_1);

			if($r_1){
					foreach($r_1 as $v1){
						$arr['departmentlist'][]=array(
							'id'=>$v1['id'],
							'name'=>$v1['name'],
							);
						}
			}else{
				$arr['departmentlist']=null;
			}

			$sql_2='SELECT id,face,name,ranking from  hrt_admin  where deptid='.$id;
			$r_2=$this->db->query($sql_2);

			if($r_2){
					foreach($r_2 as $v2){
						$arr['userlist'][]=array(
							'id'=>$v2['id'],
							'headiconurl'=>FACE.$v2['face'],
							'truename'=>$v2['name'],
							'positionname'=>$v2['ranking'],
						);
					}
			}else{
				$arr['userlist']=null;
			}
		}elseif ($type==3){
		    
		    foreach ($data as $da){
		        $sql_3='SELECT id,name from hrt_dept where id='.$da;
		        $dr1=$this->db->query($sql_3);
		        $dd1=mysqli_fetch_assoc($dr1);
		        if(!empty($dd1)){
		            $arr['departmentlist'][]=array(
		                'id'=>$dd1['id'],
		                'name'=>$dd1['name'],
		            );
		        }
		    }
		    if(empty($arr['departmentlist'])){
		        $arr['departmentlist']=null;
		    }
		}
		$this->returnjson($arr);
}
	//1-27.通过姓名/电话搜索成员接口(模糊查询)
	public function searchMemberAction()
	{
//		 远端测试接口
		// $keyword = $this->post('keyword');


		 $data=json_decode($this->post('data'),true);
		 $keyword=$data['keyword'];
		if (is_numeric($keyword)) {
			$arr1 = array(
				'user|like' => $keyword, //模糊查询如电话号码含数字186的用户
			);
			$fields1 = '`id`,`face`,`name`,`ranking`';
			$member = $this->db->getrows('[Q]admin', $arr1, $fields1);

			$arr = array();
			$arr = array(
				'code' => 200,
				'msg' => '成功',
			);
			foreach ($member as $key => $val) {
				$arr['userlist'][] = array(
					'id' => $val['id'],
					'headiconurl' => FACE . $val['face'],
					'truename' => $val['name'],
					'positionname' => $val['ranking'],
				);

			}
			echo json_encode($arr, true);
		} else {
			$arr2 = array(
				'name|like' => $keyword, //模糊查询如姓名中含'王/李/孙'的用户
			);
			$fields2 = '`id`,`face`,`name`,`ranking`';
			$member = $this->db->getrows('[Q]admin', $arr2, $fields2);

			$arr = array();
			$arr = array(
				'code' => 200,
				'msg' => '成功',
			);
			foreach ($member as $key => $val) {
				$arr['userlist'][] = array(
					'id' => $val['id'],
					'headiconurl' => FACE . $val['face'],
					'truename' => $val['name'],
					'positionname' => $val['ranking'],
				);

			}
			echo json_encode($arr, true);
		}
	}

	//1-28. 收藏用户接口
	public function userCollectAction()
	{
		//远程测试使用
		// $uid = $this->post('userId');
		// $cid = $this->post('modifiedUserId');

		 $data=json_decode($this->post('data'),true);
		 $uid=$data['userId'];
		 $cid=$data['modifiedUserId'];
      
		$arrs = array(
			'uid' => $uid,
			'cid' => $cid,
		);
		$result = $this->db->record($this->T('collect'), "`uid`='" . $uid . "',`cid`='" . $cid . "'");
		$arr = array();
		if ($result) {
			$arr = array(
				'code' => 200,
				'msg' => '成功',
			);
		} else {
			$arr = array(
				'code' => 201,
				'msg' => '失败',
			);
		}
		echo json_encode($arr, true);

	}

//	 1-29.取消收藏用户接口
	public function cancelCollectAction()
	{
		//远程测试使用
		// $uid = $this->post('userId');
		// $cid = $this->post('modifiedUserId');

		 $data=json_decode($this->post('data'),true);
		 $uid=$data['userId'];
		 $cid=$data['modifiedUserId'];
		$arrs = array(
			'uid' => $uid,
			'cid' => $cid,
		);
		$result = $this->db->delete('[Q]collect', $arrs);
		if ($result) {
			$arr = array(
				'code' => 200,
				'msg' => '成功',
			);
		} else {
			$arr = array(
				'code' => 201,
				'msg' => '失败',
			);
		}
		echo json_encode($arr, true);
	}

//	1-26.我收藏的用户列表
	public function myCollectAction()
	{
//			远程测试接口使用
		// $uid = $this->post('userId');

          $data=json_decode($this->post('data'),true);
          $uid=$data['userId'];


		 $arrs = array(
			'uid' => $uid,
		);

		$fields1 = '`id`,`cid`';
		$result = $this->db->getrows('[Q]collect', $arrs, $fields1);


		// 在hrt_admin_invite表中查询出未激活的用户数量
		$sql='SELECT count(1) as count  from hrt_admin_invite where status=0';
		$r=$this->db->query($sql);
		$row=mysqli_fetch_array($r);

		if ($result) {
			
			foreach ($result as $key1 => $val1) {

				$arr1 = array(
					'id' => $val1['cid'],
				);
				$fields2 = '`id`,`face`,`name`,`ranking`';
				$results = $this->db->getrows('[Q]admin', $arr1, $fields2);
				if ($results) {
					$arr = array(
							'code' => 200,
							'msg' => '成功',
							'unactivenum'=>$row['count'],

					);
					foreach ($results as $key2 => $val2) {

						$arr['userlist'][] = array(
							'id' => $val2['id'],
							'headiconurl' => FACE . $val2['face'],
							'truename' => $val2['name'],
							'positionname' => $val2['ranking'],
							// 'unactivenum'=>$row['count'],
						);
					}
				}
			}
		
		} else {
		   	$arr = array(
				'code' => 200,
				'msg' => '成功',
				'userlist'=>null,
				'unactivenum'=>$row['count'],
			);
			
		}
		$this->returnjson($arr);
	}

	//	1-31.获取在通讯录中，无法查看其手机号的用户列表
	public function getphoneListAction()
	{
        $data=json_decode($this->post('data'),true);
		$id=$data['userId'];
		$arrs = array(
			'isclosecontact'=>1,
		);
		$field = '`id`,`face`,`name`,`ranking`';
		$result = $this->db->getrows('[Q]admin', $arrs, $field);
		if ($result) {
			$arr = array(
				'code' => 200,
				'msg' => '成功',
			);
			foreach ($result as $val) {
				$arr['userlist'][] = array(
					'id' => $val['id'],
					'headiconurl' => FACE . $val['face'],
					'truename' => $val['name'],
					'positionname' => $val['ranking'],
				);
			}
			echo json_encode($arr, true);
		} else {
			$arr = array(
				'code' => 200,
				'msg' => '成功',
			);
			$arr['userlist']=null;
			echo json_encode($arr,true);
		}
	}

//	 1-32提交在通讯录中,无法查看其手机号的用户列表
	public function referphoneAction()
	{
		//远程测试用
		// $status = $this->post('isAllMember');
		// $userlist = json_decode($this->post('userList'),true);

			  $data=json_decode($this->post('data'),true);
			  $status=$data['isAllMember'];
			  $userlist=$data['userList'];
		if($status==1){
			$isclosecontact=1;
			$result = $this->db->record('[Q]admin', "`isclosecontact`='" . $isclosecontact. "'","`isclosecontact`=0");
			if($result){
				$arr=array(
                   'code'=>200,
					'msg'=>'成功',

				);
				echo json_encode($arr,true);
			}
		}else{
			
			$field0='`id`';
			$alluser=$this->db->getrows('[Q]admin','',$field0);

		foreach($alluser as $v){
	        $uid=array(
				'id'=>$v['id'],
			);
			$isclosecontact0=0;
			$result0 = $this->db->record('[Q]admin', "`isclosecontact`='" . $isclosecontact0. "'","`id`='".$uid['id']."'");
		}
            
			foreach($userlist as $key=>$val){
				$field=array(
					'id'=>$val,
					);
				$isclosecontact=1;
				$result = $this->db->record('[Q]admin', "`isclosecontact`='" . $isclosecontact. "'","`id`='".$field['id']."'");
			}
            $arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
           echo json_encode($arr,true);

		}
	}

//  1-33. 创建小组接口
         public function createGroupAction(){
			 //远程测试使用
			 // $name=$this->post('groupName');
			 // $face=$this->post('groupIcon');
			 // $status=$this->post('isAllMember');
			 // $uid=$this->post('userId');

             $data=json_decode($this->post('data'),true);
			 $name=$data['groupName'];
			 $face=$data['groupIcon'];
			 $status=$data['isAllMember'];
		     $userList=$data['userList'];
		     $uid=$data['userId'];
		     //调用第三云旺创建组
		     $yuid='user'.$uid;
		     $push=$this->create($yuid,$name,'创建小组');

			 if(!isempt($name)){
				 $arrs=array(
					 'name'=>$name,
					 'face'=>$face,
					  'groupheaderid'=>$uid,
					  'indate'=>date('Y-m-d H:i:s',time()),
					  'yid'=>$push,
				 );
			
				
				 $result=$this->db->record('[Q]group',$arrs);
                 //查询出新建小组的id
				 if($result){
				 	$groupid=$this->db->insert_id();

					 if($status==1){  //选择全部成员
					 	 $field="`id`";
						 $row=$this->db->getrows('[Q]admin','',$field);
                         
                         foreach($row as $val){
                             $arr0=array(
								 'type'=>'gu',
								 'mid'=>$groupid,
							      'sid'=>$val['id'],
							 );
							 $resultss=$this->db->record('[Q]sjoin',$arr0);
							 //云旺被邀请成员数组
							 $y_bid[]='user'.$val['id'];
						 }
						 if($resultss){
						 		
						 		// 云旺邀请成员
						 		$pushs=$this->invite($push,$yuid,$y_bid);
						 		if($pushs==1){
							 			$arr=array(
												 'code'=>200,
												 'msg'=>'成功',
												 'id'=>$groupid,
										 );
							 			 $this->returnjson($arr);
						 		}else{
						 			$this->showreturn('','创建失败',201);
						 		}
						 }
                     
					 }else{
                  //不是选择全部成员的情况
			 	 $arr00=array(
                     'type'=>'gu',
                     'mid'=>$groupid,
                     'sid'=>$uid,
				 	);
			 	 //将创建者加入小组
				 $result00=$this->db->record('[Q]sjoin',$arr00);
                foreach($userList as $k=>$v) {
					$arr000 = array(
						'type' => 'gu',
						'mid' => $groupid,
						'sid' => $v,
					);
					$result_s = $this->db->record('[Q]sjoin', $arr000);

					$y_bid[]='user'.$v;
				}
			
					$pushs=$this->invite($push,$yuid,$y_bid);

		             if($result_s && $pushs==1){
						 $arr=array(
							 'code'=>200,
							 'msg'=>'成功',
							 'id'=>$groupid,
						 );
						  $this->returnjson($arr);
					 }else{
					 	$this->showreturn('','创建失败',201);
					 }
		             
			   }
		    }
			 }else{
				 $arr=array(
					 'code'=>201,
					 'msg'=>'失败',
				 );
				 $this->returnjson($arr);
			 }
		 }
//       1-34我加入的小组列表
          public function mygroupListAction(){
			  //远程测试接口使用
			  // $id=$this->post('userId');

			  //app调用使用
           $data=json_decode($this->post('data'),true);
		   $id=$data['userId'];
            

              $arr1=array(
				  'sid'=>$id,
			  );
              $field='`mid`';
			  //查询出我的加入的小组的id(循环输出)
			  $arr=array(
				  'code'=>200,
				  'msg'=>'成功',

			  );

			  $row=$this->db->getrows('[Q]sjoin',$arr1,$field);

			  if($row){
				  foreach($row as $val){

					  $arr2=array(
						  'id'=>$val['mid'],
					  );

					  $fields='`id`, `name`,`face`,`yid`';
					  $order='indate  desc ';
					  $result=$this->db->getrows('[Q]group',$arr2,$fields,$order);
					  foreach($result as $vals){
						  $arr['list'][]=array(
							  'id'=>$vals['id'],
							  'name'=>$vals['name'],
							  'headicon'=>FACE.$vals['face'],
							  'ywid'=>$vals['yid'],
						  );
					  }
				  }
				 
			  }else{
                 $arr=array(
					 'code'=>200,
					 'msg'=>'成功',
					 'list'=>null,
				 );
				
			  }
			  
			   $this->returnjson($arr); 
		  }
//         1-35.公开小组列表
        public function publicGroupAction(){
			//远端测试使用
			// $id=$this->post('userId');


			$data=json_decode($this->post('data'),true);
			$id=$data['userId'];
			//先查询出我已经加入的小组
              $sql1="select *from hrt_group where id in
            (select mid from hrt_sjoin where sid=$id group by mid)
             and  isPrivate=1";
			$row1=$this->db->query($sql1);
			
			$arr=array();
			if($row1){
				$arr=array(
					'code'=>200,
					' msg'=>'成功',
				);
				foreach($row1 as $v1){
					$arr['list'][]=array(
						'id'=>$v1['id'],
						'headicon'=>FACE.$v1['face'],
						'name'=>$v1['name'],
						'isjoined'=>1,
						'ywid'=>$v1['yid'],
					);
				}
				$sql2="select *from hrt_group where id not in
           		 (select mid from hrt_sjoin where sid=$id group by mid)
           		  and  isPrivate=1";
				$row2=$this->db->query($sql2);
				foreach($row2 as $v2){
					$arr['list'][]=array(
						'id'=>$v2['id'],
						'headicon'=>FACE.$v2['face'],
						'name'=>$v2['name'],
						'isjoined'=>0,
					    'ywid'=>$v1['yid'],
					);
				}
				 echo json_encode($arr,true);

			}else{
               $this->showreturn($arr);
			}

		}
//  1-36-a 小组详情信息接口
        public function groupDetailAction(){
			//远程测试
			// $id=$this->post('groupId');

			$data=json_decode($this->post('data'),true);
			$id=$data['groupId'];
			$uid=$data['userId'];
            $arr1=array(
				'id'=>$id,
			);
			$field='`name`,`face`,`isPrivate`,`groupheaderid`,`groupheader`,`groupheaderimg`,`yid`';
            
			$group=$this->db->getone('[Q]group',$arr1,$field);
			
			if($group) {
				$arr2 = array(
					'mid' => $id,
				);
				$arr0=array(
                    'id'=>$group['groupheaderid'],
					);
				$fields='`face`,`name`';
				$result=$this->db->getone('[Q]admin',$arr0,$fields);
				
				
				$arr = array(
					'code' => 200,
					'msg' => '成功',
					'name' => $group['name'],
					'groupid'=>$id,
					'ywid'=>$group['yid'],
					'headicon' =>FACE.$group['face'],
					'type' => $group['isPrivate'],
					'groupheaduserid' => $group['groupheaderid'],
					'groupheaduser' => $result['name'],
					'groupheadheadicon' =>FACE.$result['face'],
				);
                	//判断我是否加入这个小组中
                $sql="select *from hrt_sjoin where mid=$id and sid=$uid";
				$result=$this->db->query($sql);
				$nums=mysqli_num_rows($result);
			    if($nums>0){
                   $arr['isjoined']=1;
					$num=($this->db->rows('[Q]sjoin',$arr2))-1;//小组成员数量,组长在-1
				}else{
					$arr['isjoined']=0;
					$num=($this->db->rows('[Q]sjoin',$arr2));
				}
				$arr['membercount']=$num;



				
				// $field2 = '`id`,`name`,`face`,`ranking`';
				// $field1 = '`sid`';
				// $memberid = $this->db->getrows('[Q]sjoin', $arr2, $field1);
				// foreach ($memberid as $val) {
				// 	$sid = array(
				// 		'id' => $val['sid'],
				// 		'id|neq'=>$uid,
				// 	);
				// 	$result = $this->db->getrows('[Q]admin', $sid, $field2);
				// 	foreach ($result as $v) {
				// 		$arr['memberlist'][] = array(
				// 			'id' => $v['id'],
				// 			'truename' => $v['name'],
				// 			'headiconurl' => $v['face'],
				// 			'positionname' => $v['ranking'],

				// 		);
				// 	}
				// }
				$this->returnjson($arr);
			}else{
				$this->showreturn('', '失败', 201);
			}
		}
// 1-36-b.根据云旺yid查询小组详情
		
        public function groupDetailsAction(){
			//远程测试
			// $yid=$this->post('ywid');
			// $uid=$this->post('userId');

			$data=json_decode($this->post('data'),true);
			$yid=$data['ywid'];
			$uid=$data['userId'];
            $arr1=array(
				'yid'=>$yid,
			);

			$field='`id`,`name`,`face`,`isPrivate`,`groupheaderid`,`groupheader`,`groupheaderimg`';
            
			$group=$this->db->getone('[Q]group',$arr1,$field);
		
			if($group) {

				$arr2 = array(
					'mid' => $group['id'],
				);
				$arr0=array(
                    'id'=>$group['groupheaderid'],
					);
				$fields='`face`,`name`';
				$result=$this->db->getone('[Q]admin',$arr0,$fields);
				
				
				$arr = array(
					'code' => 200,
					'msg' => '成功',
					'name' => $group['name'],
					'groupid'=>$group['id'],
					'ywid'=>$yid,
					'headicon' =>FACE.$group['face'],
					'type' => $group['isPrivate'],
					'groupheaduserid' => $group['groupheaderid'],
					'groupheaduser' => $result['name'],
					'groupheadheadicon' =>FACE.$result['face'],
				);
                	//判断我是否加入这个小组中
                $sql='SELECT  *from hrt_sjoin where mid='.$group['id'].' and sid='.$uid;
				$result=$this->db->query($sql);
				$nums=mysqli_num_rows($result);
			    if($nums>0){
                   $arr['isjoined']=1;
					$num=($this->db->rows('[Q]sjoin',$arr2))-1;//小组成员数量,组长在-1
				}else{
					$arr['isjoined']=0;
					$num=($this->db->rows('[Q]sjoin',$arr2));
				}
				$arr['membercount']=$num;


				$this->returnjson($arr);
			}else{
				$this->showreturn('', '失败', 201);
			}
		}


//      1-37加入小组接口
        public function  addGroupAction(){
             //远程测试使用
			// $id=$this->post('userId');
			// $groupid=$this->post('groupId');

			$arr=array();

		   $data=json_decode($this->post('data'),true);
           $id=$data['userId'];
           $groupid=$data['groupId'];

           $ybid='user'.$id;   // 加入云旺小组的成员
           	// 根据groupid查询出yid
	 		$sql='SELECT *from hrt_group where id='.$groupid;
	 		$r=$this->db->query($sql);
	 		$row=mysqli_fetch_array($r);
	 		if($row>0){
	 			$yid=$row['yid'];
	 			$uid='user'.$row['groupheaderid'];
	 		}


			if(!isempt($groupid)&& !isempt($id)){
				$arr0=array(
					'mid'=>$groupid,
					'sid'=>$id,
				);
				$row=$this->db->getone('[Q]sjoin',$arr0);
				if($row){
                    $this->showreturn('','该成员已在小组中',201);
				}else{
                     $arrs=array(
					'type'=>'gu',
					'mid'=>$groupid,
					'sid'=>$id,
				);
                $result=$this->db->record('[Q]sjoin',$arrs);
				if($result){

					$push=$this->invite($yid,$uid,$ybid);
					$arr=array(
						'code'=>200,
						'msg'=>'成功',
					);
					$this->returnjson($arr);
				}
		}
              
			}else{
				$this->showreturn('', '失败', 201);
			}
		}

//  1-38小组中添加成员接口
     public function addgroupMemberAction(){
            //远端测试使用
		 // $groupid=$this->post('groupId');
		 // $status=$this->post('isAllMember');
		 // $userList=json_decode($this->post('userList'),true);

		 $data=json_decode($this->post('data'),true);
		 $groupid=$data['groupId'];
		 $status=$data['isAllMember'];
		 $userList=$data['userList'];

		   	// 根据groupid查询出yid
	 		$sql='SELECT *from hrt_group where id='.$groupid;
	 		$r=$this->db->query($sql);
	 		$row=mysqli_fetch_array($r);
	 		if($row>0){
	 			$yid=$row['yid'];
	 			$uid='user'.$row['groupheaderid'];
	 		}

		 if($status==1){
            //小组中添加全部成员,先选出admin表中全部成员

			 $field1='`id`';
			 $allMember=$this->db->getrows('[Q]admin','',$field1);

			 if($allMember){
				foreach($allMember as $val){
                    $arrs=array(
						'type'=>'gu',
						'mid'=>$groupid,
						'sid'=>$val['id'],
					);
					$result=$this->db->record('[Q]sjoin',$arrs);
					$bid[]='user'.$val['id'];
				}
				 if($result){
				 	 $push=$this->invite($yid,$uid,$bid);
				 	 if($push==1){
						 	$arr=array(
									 'code'=>200,
									 'msg'=>'成功',
							 );
						 $this->returnjson($arr);
				 	 }else{
				 	 		 	$arr=array(
									 'code'=>201,
									 'msg'=>'失败',
							 );
						 $this->returnjson($arr);
				 	 }
					
				 }else{
					 $this->showreturn('','失败',201);
				 }
			 }
		 }else{
			  foreach($userList as $k=>$v) {
				 $arr000 = array(
					 'type' => 'gu',
					 'mid' => $groupid,
					 'sid' => $v,
				 );
				 $result_s = $this->db->record('[Q]sjoin', $arr000);
				 $bid[]='user'.$v;
			 }
			 if($result_s){
			 	  $push=$this->invite($yid,$uid,$bid);
				 	 if($push==1){
						 	$arr=array(
									 'code'=>200,
									 'msg'=>'成功',
							 );
						 $this->returnjson($arr);
				 	 }else{
				 	 		 	$arr=array(
									 'code'=>201,
									 'msg'=>'失败',
							 );
						 $this->returnjson($arr);
				 	 }
					
			 }else{
				 $this->showreturn('','失败',201);
			 }
		 }

	 }
//  1-39小组中移除成员
     public function removeMemberAction(){
		    //远端测试
		 // $groupid=$this->post('groupId');
		 // $removeUserId=$this->post('removeUserId');


		 $data=json_decode($this->post('data'),true);
		 $groupid=$data['groupId'];
		 $removedUserId=$data['removedUserId'];
		 $uid=$data['userId'];


		 $yuid='user'.$uid;
		 $tid='user'.$removedUserId;

	    	// 根据groupid查询出yid
 		$sql='SELECT *from hrt_group where id='.$groupid;
 		$r=$this->db->query($sql);
 		$row=mysqli_fetch_array($r);
 		if($row>0){
 			$yid=$row['yid'];
 			// $uid='user'.$row['groupheaderid'];
 		}


		 $arrs=array(
			 'mid'=>$groupid,
			 'sid'=>$removedUserId,
		 );
		 $result=$this->db->delete('[Q]sjoin',$arrs);
		
		 if($result){
		 	$push=$this->expel($yid,$yuid,$tid);
		 	if($push==1){
			 			$arr=array(
							 'code'=>200,
							 'msg'=>'成功',
			    		  );
            	 $this->returnjson($arr);
		 	}else{
		 		 $this->showreturn('','失败',201);	
		 	}
		 }else{
			 $this->showreturn('','失败',201);
		 }
	 }
//         1-40 设置小组的状态
      public function  setStatusAction(){
		    //远端测试
		  // $groupid=$this->post('groupId');
		  // $type=$this->post('type');

		  $data=json_decode($this->post('data'),true);
		  $groupid=$data['groupId'];
		  $type=$data['type'];

		  $result=$this->db->record('[Q]group',"`isPrivate`='".$type."'","`id`='$groupid'");
		  $arr=array(
			  'code'=>200,
			  'msg'=>'成功',
		  );
		  if($result){
			  $this->returnjson($arr);
		  }else{
			  $this->showreturn('','失败',201);
		  }
	  }
//    1-41该小组下的用户列表
       public function   showgroupListAction(){
		   //远程测试用
		   // $groupid=$this->post('groupId');
		   // $uid=$this->post('userId');
              $data=json_decode($this->post('data'),true);
		      $groupid=$data['groupId'];
		      $uid=$data['userId'];
		      $arr0=array(
				  'id'=>$groupid,
			  );
		      $field0='`groupheaderid`';
		      $row=$this->db->getone('[Q]group',$arr0,$field0);
		      $arr1=array(
				  'id'=>$row['groupheaderid'],
			  );
		      $field1='`id`,`name`,`deptname`,`ranking`,`face`';
		      $admin=$this->db->getone('[Q]admin',$arr1,$field1);
		      // print_r($admin);
		      // die;
		      if($admin){
				  $arr=array(
					  'code'=>200,
					  'msg'=>'成功',

				  );
				  $arr['header']=array(
					  'id'=>$admin['id'],
					  'truename'=>$admin['name'],
					  'departmentname'=>$admin['deptname'],
					  'positionname'=>$admin['ranking'],
					  'headiconurl'=>FACE.$admin['face'],
				  );

				  $arr2=array(
					  'mid'=>$groupid,

				  );
				  $field2='`sid`';
				  $member=$this->db->getrows('[Q]sjoin',$arr2,$field2);

				  $field3='`id`,`name`,`deptname`,`ranking`,`face`';

				  foreach($member as $val){
					   $arr3=array(
						   'id'=>$val['sid'],
						   'id|neq'=>$uid,
					   );
					  $list=$this->db->getrows('[Q]admin',$arr3,$field3);

					  foreach($list as $v){

						  $arr['memberlist'][]=array(
							  'id'=>$v['id'],
							  'truename'=>$v['name'],
							  'departmentname'=>$v['deptname'],
							  'positionname'=>$v['ranking'],
							  'headiconurl'=>FACE.$v['face'],
						  );
					  }
				  }
				  echo json_encode($arr,true);
			  }else{
				  $this->showreturn('','失败',201);
			  }
	   }
	   //	1-42解散小组接口
             public  function dismissGroupAction(){
				 //远程测试使用
				 // $groupid=$this->post('groupId');
				 // $uid=$this->post('userId');

				 $data=json_decode($this->post('data'),true);
				 $groupid=$data['groupId'];
				 $uid=$data['userId'];
				 $yuid='user'.$uid;
				 if(empty($groupid)){
				 	$this->showreturn('','群组ID不能为空',201);
				 }else{
				 		// 根据groupid查询出yid
				$sql='SELECT *from hrt_group where id='.$groupid;
		 		$r=$this->db->query($sql);
		 		$row=mysqli_fetch_array($r);
		 		if($row>0){
		 			$yid=$row['yid'];
		 		}
				 $push=$this->dismiss($yuid,$yid);
				 if($push==1){

		                 $arr0=array(
							 'id'=>$groupid,
						 );
						 $arr00=array(
							 'mid'=>$groupid,
			 			 );
						 $result=$this->db->delete('[Q]group',$arr0);
						 $results=$this->db->delete('[Q]sjoin',$arr00);
						  if ($result && $results) {
				 			$arr = array(
								 'code' => 200,
								 'msg' => '成功',
							 );
				 		
								 } else {
									 $arr = array(
										 'code' => 201,
										 'msg' => '失败',
									 );
								 }
						$this->returnjson($arr);
				}else{
						$this->showreturn('','失败',201);
				}
				 }
				
				
			 }
//	1-43退出小组接口
    public function  exitGroupAction(){
		 //远程测试用
		// $uid=$this->post('userId');
		// $groupid=$this->post('groupId');

		$data=json_decode($this->post('data'),true);
		$uid=$data['userId'];
		$groupid=$data['groupId'];

		$yuid='user'.$uid;

		// 根据groupid查询出yid
 		$sql='SELECT *from hrt_group where id='.$groupid;
 		$r=$this->db->query($sql);
 		$row=mysqli_fetch_array($r);
 		if($row>0){
 			$yid=$row['yid'];
 		}

	

		$arr0=array(
			'mid'=>$groupid,
			'sid'=>$uid,
		);

		$result=$this->db->delete('[Q]sjoin',$arr0);

		if($result){

		      $push=$this->toexit($yuid,$yid);
		      if($push==1){
				      	 $arr=array(
							  'code'=>200,
							  'msg'=>'成功',
					 	 );
		      }else{
		      		$arr=array(
							'code'=>201,
							'msg'=>'失败',
						);
		      }
             
			echo json_encode($arr,true);
		}else{
			$arr=array(
				'code'=>201,
				'msg'=>'失败',
			);
			echo json_encode($arr,true);
		}
	}
//	1-44修改小组名称
      public function editGroupNameAction(){
		   //远程测试用
		  // $uid=$this->post('userId');
		  // $groupid=$this->post('groupId');
		  // $groupname=$this->post('groupName');

		  $data=json_decode($this->post('data'),true);
		   $uid=$data['userId'];
		   $groupid=$data['groupId'];
		   $groupname=$data['groupName'];

		   	// 根据groupid查询出yid
 		$sql='SELECT *from hrt_group where id='.$groupid;
 		$r=$this->db->query($sql);
 		$row=mysqli_fetch_array($r);
 		if($row>0){
 			$yid=$row['yid'];
 		}


		   $yuid='user'.$uid;
         $arr0=array(
			 'name'=>$groupname,
		 );
		  $result=$this->db->record('[Q]group',$arr0,"`id`='$groupid'");
		  if($result){
		  		//云旺修改小组数据
		  	  $push=$this->modifytribeinfo($yuid,$groupname,'群组名称被修改',$yid);
		  	  if($push==1){
			  	  	 $arr=array(
						  'code'=>200,
						  'msg'=>'成功',
				  );
			 	 echo json_encode($arr,true);
		  	  }else{
		  	  	 $this->showreturn('','云旺群组名称修改失败',201); 	
			 		
		  	  }

		  }else{
			  $arr=array(		
				  'code'=>200,
				  'msg'=>'失败',
			  );
			  echo json_encode($arr,true);
		  }
	  }
//	1-45修改小组头像
public function  editGroupIconAction(){
		//测试用
  // 		$groupId=$this->post('groupId');
		// $groupIcon=$this->post('groupIcon');

		$data=json_decode($this->post('data'),true);
		$groupId=$data['groupId'];
		$groupIcon=$data['groupIcon'];

		$arr0=array(
			'face'=>$groupIcon,

		);
		$f0=array(
			'id'=>$groupId,
		);
		$r0=$this->db->record('[Q]group',$arr0,$f0);
		$arr=array(
			'code'=>200,
			'msg'=>'成功',
		);
		if($r0){
			$this->returnjson($arr);
		}else{
			$this->showreturn('','失败',201);
		}


}
//	1-46小组转让给其他用户
   public function   assignGroupAction(){
	   //远程测试
	   // $uid=$this->post('userId');
	   // $groupid=$this->post('groupId');
	   // $assignToUserId=$this->post('assignToUserId');

	   $data=json_decode($this->post('data'),true);
	   $uid=$data['userId'];
	   $groupid=$data['groupId'];
	   $assignToUserId=$data['assignToUserId'];

	   $arr0=array(
		   'groupheaderid'=>$assignToUserId,
	   );
	   $result=$this->db->record('[Q]group',$arr0,"id='$groupid'");
	   if($result){
          $arr=array(
			  'code'=>200,
			  'msg'=>'成功',
		  );
		   echo  json_encode($arr,true);
	   }else{
           $arr=array(
			   'code'=>201,
			   'msg'=>'失败',
		   );
		   echo json_encode($arr,true);
	   }
   }
//   1-47获取管理权限的用户列表
     public function getMemberPermissionAction(){
			// $uid=$this->post('userId');
			// $type=$this->post('type');

		$data=json_decode($this->post('data'),true);
	 	$uid=$data['userId'];
	 	$type=$data['type'];
		  $arr=array();
          if($type==1){
			//是否有成员管理权限
			$sql1='SELECT id,name,face ,isadmin from hrt_admin where isok=1  order by id  asc ';
			$r1=$this->db->query($sql1);
			if($r1 &&mysqli_num_rows($r1)>0){
				$arr=array(
					'code'=>200,
					'msg'=>'成功',
				);
				foreach($r1 as $v1){
					$arr['userlist'][]=array(
						'id'=>$v1['id'],
						'headiconurl'=>FACE.$v1['face'],
						'truename'=>$v1['name'],
						'isadmin'=>$v1['isadmin'],
					);
				}
			}else{
				$arr=array(
					'code'=>200,
					'msg'=>'成功',
					'userlist'=>null,
				);
			}

		  }else if($type==2){
			  //获取工作汇报管理权限列表
			 $sql2='SELECT id,name,face ,isadmin from hrt_admin where isdaily=1  order by id  asc';
			 $r2=$this->db->query($sql2);
			  if($r2 && mysqli_num_rows($r2)>0){
				  $arr=array(
					  'code'=>200,
					  'msg'=>'成功',
				  );
				  foreach($r2 as $v2){
					$arr['userlist'][]=array(
						'id'=>$v2['id'],
						'headiconurl'=>FACE.$v2['face'],
						'truename'=>$v2['name'],
						'isadmin'=>$v2['isadmin'],
					);
				  }

			  }else{
				  $arr=array(
					  'code'=>200,
					  'msg'=>'成功',
					  'userlist'=>null,
				  );
			  }

		  }else{
				//报销管理权限
			  $sql3='SELECT id,name,face,isadmin from hrt_admin where iscpower=1  order by id  asc ';
			  $r3=$this->db->query($sql3);
			  if($r3 && mysqli_num_rows($r3)>0){
				  $arr=array(
					  'code'=>200,
					  'msg'=>'成功',

				  );
					foreach($r3 as $v3){
						$arr['userlist'][]=array(
							'id'=>$v3['id'],
							'headiconurl'=>FACE.$v3['face'],
							'truename'=>$v3['name'],
							'isadmin'=>$v3['isadmin'],
						);
					}
			  }else{
				  $arr=array(
					  'code'=>200,
					  'msg'=>'成功',
					  'userlist'=>null,
				  );
			  }
		  }

		 $this->returnjson($arr);
	 }
//1-48 提交管理权限下的用户
 		public function  refertoPermissionAction(){

			// $type=$this->post('type');
			// $userlist=json_decode($this->post('userList'),true);


			$data=json_decode($this->post('data'),true);
			$type=$data['type'];
			$userlist=$data['userList'];
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
			if($type==1){
               //成员管理权限
		
					foreach($userlist as $v1){
						$arr1=array(
							'isok'=>1,
						);
						$r1=$this->db->record('[Q]admin',$arr1,"`id`='$v1'");

						$to_users[]='user'.$v1;
					  }
					if($r1){
						     // 第三方平台推送
                            $user_sende='admin';

                            
                            $summary='您已获得成员管理权限';
                            $content='您已获得成员管理权限';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=1,1,$summary,$content);

						$this->returnjson($arr);

					}else{
						$this->showreturn('','失败',201);
					}
			
			}else if($type==2){
				//工作汇报管理权限
		
					foreach($userlist as $v2){
					$arr2=array(
						'isdaily'=>1,
					);
					$r2=$this->db->record('[Q]admin',$arr2,"`id`='$v2'");

					$to_users[]='user'.$v2;
				}
				if($r2){
					   // 第三方平台推送
                            $user_sende='admin';

                            
                            $summary='您已获得工作汇报管理权限';
                            $content='您已获得工作汇报管理权限';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=2,1,$summary,$content);
					$this->returnjson($arr);

				}else{
					$this->showreturn('','失败',201);
				}

			}else{
				//报销管理权限
		
					foreach($userlist as $v3){
					$arr3=array(
						'iscpower'=>1,
					);
					$r3=$this->db->record('[Q]admin',$arr3,"`id`='$v3'");
					$to_users[]='user'.$v2;
				}
				if($r3){
					   // 第三方平台推送
                            $user_sende='admin';

                            
                            $summary='您已获得报销管理权限';
                            $content='您已获得报销管理权限';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=3,1,$summary,$content);
					$this->returnjson($arr);

				}else{
					$this->showreturn('','失败',201);
				}
		
				
		}

 }
 		// 1-49  删除管理权限下的用户
	 public  function deletePermissionAction(){

	 	  	// $type=$this->post('type');
	 	  	// $removedUserId=$this->post('removedUserId');

	 		$data=json_decode($this->post('data'),true);
	 		$type=$data['type'];
	 		$removedUserId=$data['removedUserId'];
	 		$arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
			if($type==1){
               //成员管理权限
		
					
					$arr1=array(
						'isok'=>0,
					);
					$r1=$this->db->record('[Q]admin',$arr1,"`id`='$removedUserId'");
					
					if($r1){
						  // 第三方平台推送
                            $user_sende='admin';

                        
                            $to_users='user'.$removedUserId;
                            $summary='您的成员管理权限已被移除';
                            $content='您的成员管理权限已被移除';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=1,0,$summary,$content);
						$this->returnjson($arr);

					}else{
						$this->showreturn('','失败',201);
					}
			
			}else if($type==2){
				//工作汇报管理权限
		
					
					$arr2=array(
						'isdaily'=>0,
					);
					$r2=$this->db->record('[Q]admin',$arr2,"`id`='$removedUserId'");
			
				if($r2){
					  // 第三方平台推送
                            $user_sende='admin';
                            $to_users='user'.$removedUserId;
                            $summary='您的工作汇报管理权限已被移除';
                            $content='您的工作汇报管理权限已被移除';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=2,0,$summary,$content);
					$this->returnjson($arr);

				}else{
					$this->showreturn('','失败',201);
				}

			}else{
				//报销管理权限
		
				
					$arr3=array(
						'iscpower'=>0,
					);
					$r3=$this->db->record('[Q]admin',$arr3,"`id`='$removedUserId'");
			
				if($r3){
					 // 第三方平台推送
                            $user_sende='admin';
                            $to_users='user'.$removedUserId;
                            $summary='您的报销管理权限已被移除';
                            $content='您的报销管理权限已被移除';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=3,0,$summary,$content);
					$this->returnjson($arr);

				}else{
					$this->showreturn('','失败',201);
				}
		
				
		}


	 }



			//1-50所有未处理数据信息
	public  function  myuntreatedNewsAction(){
		// $uid=$this->post('userId');

		$data=json_decode($this->post('data'),true);
		$uid=$data['userId'];


//		1.未读消息数量
		$sql1='SELECT count(1) as unreadnews from hrt_infor  a
				left join hrt_inforead  b  on b.pid=a.id
				where b.rid='.$uid.' and b.isread is null';
		$r1=$this->db->query($sql1);
		$row1=mysqli_fetch_array($r1);
//		echo $row1['unreadnews']; 未读消息数量


//		2.未审核流程数量(待我审核)
		$sql2='SELECT count(1) as unauditflow from hrt_flows_bill
				where nowcheckuserid='.$uid.' and nowstatus=0 and isdel=0';
		$r2=$this->db->query($sql2);
		$row2=mysqli_fetch_array($r2);
//		echo $row2['unauditflow']; 未审核流程数量


//		3.未完成任务数量(我接收的)
		$sql3="SELECT count(1) as unfinishedtask  from hrt_work
		where  distid like  '%$uid%' and  isend=0";
		$r3=$this->db->query($sql3);
		$row3=mysqli_fetch_array($r3);
//		echo $row3['unfinishedtask'];

//		4.未审批的报销单数量(该我审批未审批)
		$sql4='SELECT  count(1) as  unconsumebills from hrt_consume_process where nowid='.$uid.' and fstate=0';
		$r4=$this->db->query($sql4);
		$row4=mysqli_fetch_array($r4);
//		echo $row4['unconsumebills'];

//		5.未支付的报销单数量(我该支付)

		$sql5='SELECT count(1) as  unpayreimburse  from hrt_consume_process where teller='.$uid.' and ispayed=0 and fstate=2';
		$r5=$this->db->query($sql5);
		$row5=mysqli_fetch_array($r5);
//		echo $row5['unpayreimburse'];
		// 6未完成的民意调查的数量
		$sql6='SELECT count(1) as unfinishedsurveycount from hrt_surveyrefer a 
			right join  hrt_survey  b  on a.sid=b.id  where a.rid='.$uid.' and a.status=0';
		$r6=$this->db->query($sql6);
		$row6=mysqli_fetch_array($r6);




		if($r1 && $r2 && $r3 && $r4 && $r5 && $r6){
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
				'msgcount'=>array(
					'unreadnoticecount'=>$row1['unreadnews'],
					'unauditflowcount'=>$row2['unauditflow'],
					'unfinishedtaskcount'=>$row3['unfinishedtask'],
					'unauditreimbursecount'=>$row4['unconsumebills'],
					'unpayreimbursecount'=>$row5['unpayreimburse'],
					'unfinishedsurveycount'=>$row6['unfinishedsurveycount'],
				),
			);

			$this->returnjson($arr);
		}else{
			$this->showreturn('','失败',201);
		}

	}
//	1-51获取用户所有的权限
	   public  function  getUserPermissionAction(){
			// $uid=$this->post('userId');

		   	$data=json_decode($this->post('data'),true);
		   	$uid=$data['userId'];
		   	// 查找管理员信息
		   	$sql0='SELECT *from hrt_admin where isadmin=1';
		   	$r0=$this->db->query($sql0);
		   	$row0=mysqli_fetch_array($r0);
		   	$aArr=array(
		   		'userid'=>$row0['id'],
		   		'truename'=>$row0['name'],
		   		);

		   $sql='SELECT *from hrt_admin  where id='.$uid;
		   $r=$this->db->query($sql);
		   $row=mysqli_fetch_array($r);
		   if($row>0){
			   $pArr=array(
				   'membermanagepermission'=>$row['isok'],
				   'workreportmanagepermission'=>$row['isdaily'],
				   'reimbursemanagepermission'=>$row['iscpower'],
				   'allowpublishnoticepermission'=>$row['isinfor'],
				   'allowapplyreimbursepermission'=>$row['isconsume'],
				   'allowscorestorefrontpermission'=>$row['isdeptscore'],
				   'allowlookchargepermission'=>$row['isexpense'],
				   'allowpublishsurveypermission'=>$row['issurvey'],
				   'allowmodifycompanycloudpermission'=>$row['clouddisk'],
			   );

			   //从dailyrule表中判断是否有查看工作汇报权限
				$s_sql='SELECT *from hrt_dailyrule where  uid='.$uid;
			    $s_r=$this->db->query($s_sql);
			    $s_row=mysqli_fetch_array($s_r);
			    if($s_row >0){
					$pArr['allowlookworkreportpermission']="1";
				}else{
					$pArr['allowlookworkreportpermission']="0";
				}

			  	$arr=array(
					'code'=>200,
					'msg'=>'成功',
					'admin'=>$aArr,
					'permission'=>$pArr,
				);
				$this->returnjson($arr);
		   }else{
				$this->showreturn('','失败',201);
		   }

	   }

//1-52未激活的用户列表
   public  function   unactivatedMemberListAction(){
		$sql='SELECT *from hrt_admin_invite where status=0';
	   	$result=$this->db->query($sql);
	    $row=mysqli_fetch_array($result);
	   	if($row >0){
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
			foreach($result as $v){
				//根据部门id 求得部门的名称
				$dpsql='SELECT *from hrt_dept where id='.$v['deptid'];
				$d_r=$this->db->query($dpsql);
				$d_row=mysqli_fetch_array($d_r);

				$arr['userlist'][]=array(
					'id'=>$v['id'],
					'truename'=>$v['truename'],
					'departmentname'=>$d_row['name'],
					'positionname'=>$v['position'],
					'sex'=>$v['sex'],
					'phone'=>$v['phone'],

				);
			}
		}else{
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
				'userlist'=>null,
			);
		}
		$this->returnjson($arr);
   }


//第三方那个平台函数

   // 注册用户
   public function adduser($nick,$userid){
        $c = new TopClient();
        $req = new OpenimUsersAddRequest;
        $userinfos = new Userinfos;
        $userinfos->nick=$nick;
        // $userinfos->icon_url=$icon_url;
        $userinfos->userid=$userid;
        $req->setUserinfos(json_encode($userinfos));

        $list = $this->toArray($c->execute($req));
        //返回添加失败的用户
        if(!empty($list['uid_fail'])){

            // return $list['uid_fail']; 
           // print_r($list['uid_fail']) ;
         print_r($list['fail_msg']) ; 
        }else {
           return  1;
        }
    }
    //权限消息推送
        public function custmsgpush($user_sende,$to_users,$type,$status,$summary,$content){
        $list = array(
            'customizeMessageType'=>'Permission',
            'type'=>$type,
            'status'=>$status,
            'content'=>$content
        );
        $c = new TopClient;
        $req = new OpenimCustmsgPushRequest;
        $custmsg = new CustMsg;
        $custmsg->from_user=$user_sende;
        $custmsg->to_users=$to_users;
        $custmsg->summary=$summary;
        $custmsg->data=json_encode($list);
        $req->setCustmsg(json_encode($custmsg));
        $resp = $c->execute($req);
    }



       function toArray($simplexml_obj, $array_tags=array(), $strip_white=1)
    {
        if($simplexml_obj)
        {
            // if( count($simplexml_obj)==0 )
            //     return $strip_white?trim((string)$simplexml_obj):(string)$simplexml_obj;
            // $attr = array();
            // foreach ($simplexml_obj as $k=>$val) {
            //     if( !empty($array_tags) && in_array($k, $array_tags) ) {
            //         $attr[] = self::toArray($val, $array_tags, $strip_white);
            //     }else{
            //         $attr[$k] = self::toArray($val, $array_tags, $strip_white);
            //     }
            // }
            // return $attr;

            $arr = (array)$simplexml_obj;
            foreach ($arr as $k => $value) {
            	if (is_object($value)) {
            		$arr[$k] = $this->toArray($value);
            	}
            }
            return $arr;
        }
        return false;
    }

		
  /*
     * 创建群
     * $uid 用户信息
     * $tribe_name 群名称
     * $notice 群公告
     * $tribe_type 群类型有两种tribe_type = 0 普通群 普通群有管理员角色，对成员加入有权限控制tribe_type = 1 讨论组 讨论组没有管理员，不能解散   注：（传过来的数字是字符串） $tribe_type="0";
     * 群ID：1989717626
     * 讨论组ID：1901248914
     * 
     * $uid,$tribe_name,$notice,$tribe_type
     */
      public function create($uid,$tribe_name,$notice){
        $c = new TopClient;
        $req = new OpenimTribeCreateRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeName($tribe_name);
        $req->setNotice($notice);
        $req->setTribeType("1");
        $members = new OpenImUser;
        $members->uid=$uid;
        $members->taobao_account="false";
        $members->app_key=APPKEY;
        $req->setMembers(json_encode($members));
        $list = $this->toArray($c->execute($req));      
        if(!empty($list['tribe_info']['tribe_id'])){
            return  $list['tribe_info']['tribe_id'];
        }else{
        	// echo 1111;
            return "创建失败";
        }
    }
    /*
     * 群邀请加入
     * $tribe_id ： 群id 
     * $uid : 用户id
     * $bid : 被邀请用户id
     * $tribe_id,$uid,$bid
     * 1901248914
     */
  public function invite($tribe_id,$uid,$bid){
        $c = new TopClient;
        $req = new OpenimTribeInviteRequest;
        $req->setTribeId($tribe_id);
        $memberList = array();
        foreach ($bid as $v){
            $members = new OpenImUser;
            $members->uid=$v;
            $members->taobao_account="false";
            $members->app_key=APPKEY;
            array_push($memberList,$members);
        }
        $req->setMembers(json_encode($memberList));
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $list = $c->execute($req);
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
            return  1 ;
        }else{
            return 0;
        }
    }

    /*
     * 踢出群成员
     * $tribe_id : 群ID
     * $uid ： 用户ID
     * $tid ： 被踢走用户ID
     */
    public function expel($tribe_id,$uid,$tid){  
        $c = new TopClient;
        $req = new OpenimTribeExpelRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $member = new OpenImUser;
        $member->uid=$tid;
        $member->taobao_account="false";
        $member->app_key=APPKEY;
        $req->setMember(json_encode($member));
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
             return 1; //成功
        }else{
             return 0; //失败
        }
    }
    /*
     * 群解散
     */
    public function dismiss($uid,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeDismissRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $list = $this->toArray($c->execute($req)); 
       // var_dump($c->execute($req));    
         if($list['tribe_code']==0){
            return 1;  //成功
        }else{
            return 0;  //失败
        }
    }
    
    /*
     * OPENIM群成员退出
     * $uid:用户id
     * $tribe_id：群id
     */
    public function toexit($uid,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeQuitRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $resp = $c->execute($req);
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
             return 1;  //成功
        }else{
             return 0;  //失败
        }
    }
    /*
     * 获取群消息
     * 
     */
    public function gettribeinfo($uid,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeGettribeinfoRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $list = $this->toArray($c->execute($req));
        if(!empty($list['code'])){
            return   '获取失败';
        }else{
            return $list;
        }
    }
     /*
     * OPENIM群信息修改
     * $uid  用户ID
     * $tribe_name  群名称
     * $notice 群公告
     * $tribe_id 群id 
     */
     public function modifytribeinfo($uid,$tribe_name,$notice,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeModifytribeinfoRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeName($tribe_name);
        $req->setNotice($notice);
        $req->setTribeId($tribe_id);
        $resp = $c->execute($req);
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
            return  1;
        }else{
            return  0;
        }
    }
    

	public function   textAction(){
		$id='1993203664';
		$push=$this->gettribeinfo('user1',$id);
		print_r($push);
}

















 // <!-类结束->
 }     