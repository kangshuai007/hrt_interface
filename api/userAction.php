<?php 
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
class userClassAction extends apiAction
{
	//1-6 修改密码接口
	public function editpassAction()
	{
			//远程测试接口
		// $id= $this->post('userId');
		// $oldpass=$_POST['oldPassword'];
		// $pasword=$_POST['newPassword'];
     //   $oldpass	= $this->post('oldPassword');
	    // $pasword	= $this->post('newPassword');
		$data=json_decode($this->post('data'),true);
		$id=$data['userId'];
		$oldpass=$data['oldPassword'];
		$pasword=$data['newPassword'];
		$msg		= '';
		$code=200;
		if($this->isempt($pasword)){
			$msg ='新密码不能为空';
			$code=201;
		}
		if($msg == ''){
			$oldpassa	= $this->db->getmou($this->T('admin'),"`pass`","`id`='$id'");
			if($oldpassa != ($oldpass)){
				$msg ='旧密码不正确';
			    $code=201;
			   }
			if($msg==''){
				if($oldpassa == md5($pasword))
					$msg ='新旧密码不能相同';
				    $code=201;
			}
		}
		if($msg == ''){
			if(!$this->db->record($this->T('admin'), "`pass`='".$pasword."'", "`id`='$id'"))$msg	= $this->db->error();
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
	    $arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
//	1-7修改头像
    public   function  editHeadIconAction(){
		// $uid=$this->post('userId');
		// $modifiedUserId=$this->post('modifiedUserId');
		// $headIconName=$this->post('headIconName');

		$data=json_decode($this->post('data'),true);
		$uid=$data['userId'];
		$modifiedUserId=$data['modifiedUserId'];
		$headIconName=$data['headIconName'];

		$arr1=array(
			'face'=>$headIconName,
		);
		$f1=array(
			'id'=>$modifiedUserId,
		);
		$r1=$this->db->record('[Q]admin',$arr1,$f1);
		$arr=array(
			'code'=>200,
			'msg'=>'成功',
			'headiconurl'=>FACE.$headIconName,
		);
		if($r1){
			//第三方修改
			$ids='user'.$uid;
			$rr=$this->userupdate($nick='',FACE.$headIconName,$ids);
			if($rr==1){
				$this->returnjson($arr);
			}else{
				$this->showreturn('','第三方平台未修改成功',201);
			}


			$this->returnjson($arr);

		}else{
			$this->showreturn('','失败',201);
		}
	}



	//1-2.第三方平台发送验证码
	public function responseAction(){
		if(!empty($_POST['phone'])){
			$Mobile=$_POST['phone'];
		}
		$url="http://service.winic.org:8009/sys_port/gateway/index.asp?";
		$data = "id=%s&pwd=%s&to=%s&content=%s&time=";
		$id = 'tianhongo';
		$pwd = 'x473537';
		$to = $Mobile;
		$Content='775852';
		$content = iconv("UTF-8","GB2312",$Content);
		$rdata = sprintf($data, $id, $pwd, $to, $content);
		$ch = curl_init();
		curl_setopt($ch, CFACEOPT_POST,1);
		curl_setopt($ch, CFACEOPT_POSTFIELDS,$rdata);
		curl_setopt($ch, CFACEOPT_FACE,$url);
		curl_setopt($ch,CFACEOPT_RETURNTRANSFER,true);
		$result = curl_exec($ch);
		curl_close($ch);
//		$success = explode('/', $result)[0] === '200';
		if($result){
          echo  1;
		}else{
         echo  0;
		}
	}
	//1-13.修改邮箱接口
       public function editemailAction(){
		   //远程测试接口
//		   $id =$this->post('modifiedUserId');
//		   $email=$this->post('email');
           // print_r($_POST['data']);
             print_r($_POST);
		   $data=json_decode($this->post('data'),true);
		   $id=$data['modifiedUserId'];
		   $email=$data['email'];
		
		   $msg		= '';
		   $code=200;
		   if($this->isempt($email)){
			   $msg ='新改邮箱地址为空';
			   $code=201;
		   }
		   if($msg == ''){
			   if(!$this->db->record($this->T('admin'), "`email`='".$email."'", "`id`='$id'"))
//				   $msg	= $this->db->error();//返回为空未定义
			     $msg='失败';
			     $code=201;
		   }
		   if($msg==''){
			   $msg='成功';
			   $code=200;
		   }
		   $arr=array(
			   'code'=>$code,
			   'msg'=>$msg,
		   );
		   echo json_encode($arr);

	   }
	//1-14. 修改地址接口
        public function editaddressAction(){
//            远程接口测试
//			$id =$this->post('modifiedUserId');
//			$address=$this->post('address');

			$data=json_decode($this->post('data'),true);
			$id=$data['modifiedUserId'];
			$address=$data['address'];
			$msg		= '';
			$code=200;
			if($this->isempt($address)){
				$msg ='新改地址不能为空';
				$code=201;
			}
			if($msg == ''){
				if((!$this->db->record($this->T('admin'), "`address`='".$address."'", "`id`='$id'"))||
					(!$this->db->record($this->T('userinfo'), "`nowdizhi`='".$address."'", "`id`='$id'")))
					$msg='失败';
				    $code=201;
			}
			if($msg==''){
				$msg='成功';
				$code=200;
			}
			$arr=array(
				'code'=>$code,
				'msg'=>$msg,
			);
			echo json_encode($arr);
		}
//     1-23.修改入职时间
	  public function editworkdateAction(){
//		  远程测试接口
//		  $id =$this->post('modifiedUserId');
//	      $workdate=$this->post('entryDate');
//		  $workdates= date("Y-m-d",strtotime($workdate));

		  $data=json_decode($this->post('data'),true);
		  $id=$data['modifiedUserId'];
		  $workdate=$data['entryDate'];
		  $workdates= date("Y-m-d",strtotime($workdate));
		  $msg		= '';
		  $code=200;
		  if($this->isempt($workdate)){
			  $msg ='入职时间为空';
			  $code=201;
		  }
		  if($msg == ''){
			  if(!$this->db->record($this->T('userinfo'), "`workdate`='".$workdates."'", "`id`='$id'"))
				  $msg='失败';
			  $code=201;
		  }
		  if($msg==''){
			  $msg='成功';
			  $code=200;
		  }
		  $arr=array(
			  'code'=>$code,
			  'msg'=>$msg,
		  );
		  echo json_encode($arr);
	  }
	//1-24.修改毕业日期
	public function editgraduateAction(){
//		远程测试使用
//		$id =$this->post('modifiedUserId');
//		$graduatedate=$this->post('graduateDate');
//		$graduatedates= date("Y-m-d",strtotime($graduatedate));

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$graduatedate=$data['graduateDate'];
		$graduatedates= date("Y-m-d",strtotime($graduatedate));
		$msg		= '';
		$code=200;
		if($this->isempt($graduatedate)){
			$msg ='毕业时间为空';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('userinfo'), "`graduatedate`='".$graduatedates."'", "`id`='$id'"))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
    //1-15.修改生日
	 public function editbirthdayAction(){
//		 远程测试
//         $id =$this->post('modifiedUserId');
//		 $birthday=$this->post('birthday');
//		 $birthdays= date("Y-m-d",strtotime($birthday));

		 $data=json_decode($this->post('data'),true);
		 $id=$data['modifiedUserId'];
		 $birthday=$data['birthday'];
		 $birthdays= date("Y-m-d",strtotime($birthday));
		 $msg		= '';
		 $code=200;
		 if($this->isempt($birthday)){
			 $msg ='生日为空';
			 $code=201;
		 }
		 if($msg == ''){
			 if(!$this->db->record($this->T('userinfo'), "`birthday`='".$birthdays."'", "`id`='$id'"))
				 $msg='失败';
			 $code=201;
		 }
		 if($msg==''){
			 $msg='成功';
			 $code=200;
		 }
		 $arr=array(
			 'code'=>$code,
			 'msg'=>$msg,
		 );
		 echo json_encode($arr);
	 }
	//1-12.修改联系方式
	public function editcontactAction(){
		//远程测试
//		$id =$this->post('modifiedUserId');
//		$contact=$this->post('contactList');


		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$contact=json_encode($data['contactList'],true);

        $msg='';
		$code=200;
		if($this->isempt($contact)){
			$msg ='新增联系方式为空';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('admin'), "`contacts`='".$contact."'", "`id`='$id'"))
				$msg='失败';
			    $code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//1-16.修改籍贯
	public function editjiguanAction(){
		//远程测试
//		$id =$this->post('modifiedUserId');
//		$jiguan=$this->post('jiguan');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$jiguan=$data['jiguan'];
		$msg		= '';
		$code=200;
		if($this->isempt($jiguan)){
			$msg ='籍贯为空';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('userinfo'), "`jiguan`='".$jiguan."'", "`id`='$id'"))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//1-17.修改民族
	public function editnationAction(){
		//远程测试
//		$id =$this->post('modifiedUserId');
//		$nation=$this->post('minzu');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$nation=$data['minzu'];
		$msg		= '';
		$code=200;
		if($this->isempt($nation)){
			$msg ='民族为空';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('userinfo'), "`minzu`='".$nation."'", "`id`='$id'"))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//1-18.修改身份证号码
	public function editidcardAction(){
//		远程测试
//		$id =$this->post('modifiedUserId');
//		$idcard=$this->post('identityCard');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$idcard=$data['identityCard'];
		$msg		= '';
		$code=200;
		if($this->isempt($idcard)){
			$msg ='身份证号码为空';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('userinfo'), "`identitycard`='".$idcard."'", "`id`='$id'"))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//1-19.修改婚姻状况
	public function editconditionAction(){
		//远程测试
//		$id =$this->post('modifiedUserId');
//		$condition=$this->post('condition');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$condition=$data['condition'];
		$msg		= '';
		$code=200;
		if($this->isempt($condition)){
			$msg ='未填写';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('userinfo'), "`hunyin`='".$condition."'", "`id`='$id'"))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//1-20.修改是否有子女
	public function editissueAction(){
		//远程测试
//		$id =$this->post('modifiedUserId');
//		$issue=$this->post('condition');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$issue=$data['condition'];
		$msg		= '';
		$code=200;
		if($this->isempt($issue)){
			$msg ='未填写是否有子女';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('userinfo'), "`issue`='".$issue."'", "`id`='$id'"))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//1-21.修改家庭住址
	public function edithomeaddressAction(){
		//远程测试
//		$id =$this->post('modifiedUserId');
//		$homeaddress=$this->post('homeAddress');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$homeaddress=$data['homeAddress'];
		$msg		= '';
		$code=200;
		if($this->isempt($homeaddress)){
			$msg ='家庭住址未填写';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('userinfo'), "`housedizhi`='".$homeaddress."'", "`id`='$id'"))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//1-22.修改紧急联系人信息
	public function editconpersonAction(){
		//远程测试
//		$id =$this->post('modifiedUserId');
//		$contactName=$this->post('contactName');
//		$contactPhone=$this->post('contactPhone');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$contactName=$data['contactName'];
		$contactPhone=$data['contactPhone'];
		$msg		= '';
		$code=200;
		if(($this->isempt($contactName))||($this->isempt($contactPhone))){
			$msg ='紧急联系人未填写完整';
			$code=201;
		}
		if($msg == ''){
			if((!$this->db->record($this->T('userinfo'), "`conperson`='".$contactName."'", "`id`='$id'"))||
				(!$this->db->record($this->T('userinfo'), "`connumber`='".$contactPhone."'", "`id`='$id'")))
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
		//1-9.修改性别
	public  function editSexAction(){
		//远程测试接口
//		   $id =$this->post('modifiedUserId');
//		   $truename=$this->post('sex');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$sex=$data['sex'];
		$msg		= '';
		$code=200;
		if($this->isempt($sex)){
			$msg ='新改性别为空';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('admin'), "`sex`='".$sex."'", "`id`='$id'"))
//				   $msg	= $this->db->error();//返回为空未定义
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);

	}
	//1-11.修改工号
	public function editJobcodeAction(){
		//远程测试接口
//		   $id =$this->post('modifiedUserId');
//		   $jobcode=$this->post('jobcode');

		$data=json_decode($this->post('data'),true);
		$id=$data['modifiedUserId'];
		$jobcode=$data['jobCode'];
		$msg		= '';
		$code=200;
		if($this->isempt($jobcode)){
			$msg ='新改工号为空';
			$code=201;
		}
		if($msg == ''){
			if(!$this->db->record($this->T('admin'), "`jobcode`='".$jobcode."'", "`id`='$id'"))
//				   $msg	= $this->db->error();//返回为空未定义
				$msg='失败';
			$code=201;
		}
		if($msg==''){
			$msg='成功';
			$code=200;
		}
		$arr=array(
			'code'=>$code,
			'msg'=>$msg,
		);
		echo json_encode($arr);
	}
	//   1-8.  修改姓名接口
     public function editNameAction(){
		 //远程测试接口
//		   $id =$this->post('modifiedUserId');
//		   $truename=$this->post('truename');

		 $data=json_decode($this->post('data'),true);
		 $id=$data['modifiedUserId'];
		 $truename=$data['truename'];
		 $msg		= '';
		 $code=200;
		 if($this->isempt($truename)){
			 $msg ='新改姓名为空';
			 $code=201;
		 }
		 if($msg == ''){
			 if(!$this->db->record($this->T('admin'), "`name`='".$truename."'", "`id`='$id'"))
//				   $msg	= $this->db->error();//返回为空未定义
				 $msg='失败';
			 $code=201;
		 }
		 if($msg==''){
			 $msg='成功';
			 $code=200;
		 }

		 // 云旺修改姓名
		 $ids='user'.$id;
			$rr=$this->userupdate($truename,$icon_url='',$ids);
			if($rr==1){
				 $arr=array(
						 'code'=>$code,
						 'msg'=>$msg,
					 );
		 		echo json_encode($arr);
			}else{
				$this->showreturn('','第三方平台未修改成功',201);
			}


			

		

	 }
	 	// 1-10.修改部门职位
	public function editdeptAction(){
		 $data=json_decode($this->post('data'),true);
		 $modid=$data['modifiedUserId'];
		 $deptid=$data['departmentId'];
		 $position=$data['position'];
		 $ismanager=$data['isStoreManager'];
		 $arr0=array(
			 'id'=>$deptid,
		 );
		 $field='`name`';
		 $deptname=$this->db->getone('[Q]dept',$arr0,$field);
		 $arrs=array(
			 'id'=>$modid,
			 'deptid'=>$deptid,
			 'deptname'=>$deptname['name'],
			 'ranking'=>$position,
			 'isstoremanager'=>$ismanager,
		 );

		$result=$this->db->record('[Q]admin',$arrs,"`id`='$modid'");
		if($result){
           $arr=array(
			   'code'=>200,
			   'msg'=>'成功',
		   );
			$this->returnjson($arr);

		}else{
			$this->showreturn('','失败',201);
		}
	}



// 版本更新接口
	public function updateVersionAction(){
			$url='http://120.27.53.194:8082/updatePack/jiekou.docx';
			$arr=array(
					'code'=>200,
					'msg'=>'成功',
					'versioncode'=>1,
					'apkurl'=>$url,
				);
			$this->returnjson($arr);

	}
// 1-54未处理的工作列表
		public   function  untreatedWorkListAction(){

			// $uid=$this->post('userId');
			// $pageNo=$this->post('pageNo');
			// $pageSize=$this->post('pageSize');

			$data=json_decode($this->post('data'),true);
			$uid=$data['userId'];
			$pageNo=$data['pageNo'];
			$pageSize=$data['pageSize'];
		//现将所有的未完成的工作写入到一张表中
//		1.1.通知为浏览的
		$sql1_1='SELECT  b.id as ids,b.pdate,b.title
 					from hrt_inforead a
 				  right  join hrt_infor b  on  a.pid=b.id
 				  where  a.isread is null and rid='.$uid;
		$r_1=$this->db->query($sql1_1);
		foreach($r_1   as $v1) {
			$arr1 = array(
				'type' => 1,
				'uid'=>$uid,
				'wid' => $v1['ids'],
				'dt' => $v1['pdate'],
				'content' => $v1['title'],
			);
			$r1 = $this->db->record('[Q]untreatedwork', $arr1);
		}

//		1.2流程相关的
		$sql_2='SELECT  a.id,a.flowname,a.applydt ,b.name from hrt_flows_bill a
				left join  hrt_admin  b   on  a.uid=b.id
				where a.nowcheckuserid='.$uid.' and a.fstatus=0';
		$r_2=$this->db->query($sql_2);
		foreach($r_2 as $v2){
				$arr2=array(
					'type'=>2,
					'uid'=>$uid,
					'wid'=>$v2['id'],
					'dt'=>strtotime($v2['applydt']),
					'content'=>'待审核-'.$v2['name'].'的'.$v2['flowname'],
				);
			$r2 = $this->db->record('[Q]untreatedwork', $arr2);
		}
//		1-3任务相关
		$sql_3="SELECT id,`explain`,startdt  from hrt_work
				where distid like  '%$uid%' and isend=0 ";
		$r_3=$this->db->query($sql_3);
		foreach($r_3 as  $v3){
			$arr3=array(
				'type'=>3,
				'uid'=>$uid,
				'wid'=>$v3['id'],
				'dt'=>strtotime($v3['startdt']),
				'content'=>$v3['explain'],
			);
			$r3 = $this->db->record('[Q]untreatedwork', $arr3);
		}

//		1-4报销相关
		$sql_4='SELECT  c.name,b.date,b.id  from hrt_consume_process  a
			left join hrt_consume_bill  b  on a.bid=b.id
			left join hrt_admin c on b.uid=c.id
			where a.nowid='.$uid.' and a.fstate=0';
		$r_4=$this->db->query($sql_4);
		foreach($r_4 as $v4){
			$arr4=array(
				'type'=>4,
				'uid'=>$uid,
				'wid'=>$v4['id'],
				'dt'=>strtotime($v4['date']),
				'content'=>'待审批-'.$v4['name'].'的报销单',
			);
			$r4 = $this->db->record('[Q]untreatedwork', $arr4);
		}
//		1-5民意调查
		$sql_5='SELECT   b.date,b.id ,b.title from hrt_surveyrefer a
				right join  hrt_survey b   on a.sid=b.id
				where a.rid='.$uid.' and a.status=0';
		$r_5=$this->db->query($sql_5);
		foreach($r_5 as $v5){
				$arr5=array(
					'type'=>5,
					'uid'=>$uid,
					'wid'=>$v5['id'],
					'dt'=>$v5['date'],
					'content'=>$v5['title'],
				);
			$r5= $this->db->record('[Q]untreatedwork', $arr5);
		}

//	  在表中查询出uid下的所有数据 即所有未完成的工作
//		分页
		$t_sql='SELECT *from hrt_untreatedwork where  uid='.$uid;
		$t_r=$this->db->query($t_sql);
		$total=mysqli_num_rows($t_r);
		$eNum=ceil($total/$pageSize);
		$limit=(($pageNo-1)*$pageSize).",".$pageSize;
		$sql='SELECT *from hrt_untreatedwork  where  uid='.$uid.' order by dt desc  limit '.$limit;
		
		$r=$this->db->query($sql);
		if($r){
			if($pageNo>=$eNum){
				$arr=array(
					'code'=>200,
					'msg'=>'成功',
					'isend'=>true,
				);
			}else{
				$arr=array(
					'code'=>200,
					'msg'=>'成功',
					'isend'=>false,
				);
			}
			foreach($r as $v){
				$arr['list'][]=array(
					'id'=>$v['wid'],
					'type'=>$v['type'],
					'content'=>$v['content'],
					'datetime'=>$v['dt'],
				);
			}
			$aDele=array(
				'uid'=>$uid,
			);
			$dele=$this->db->delete('[Q]untreatedwork',$aDele);
			$this->returnjson($arr);
		}else{
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
				'list'=>null,
			);
			$this->returnjson($arr);
		}
	}

  // 修改星级
	public  function  editMemberStarAction(){
			// $otherid=$this->post('modifiedUserId');
			// $uid=$this->post('userId');
			// $starLevel=$this->post('starLevel');

			$data=json_decode($this->post('data'),true);
			$uid=$data['userId'];
			$otherid=$data['modifiedUserId'];
			$starLevel=$data['starLevel'];

			$arr0=array(
				'star'=>$starLevel,
				);
			$f0=array(
				'id'=>$otherid,
				);

			$r0=$this->db->record('[Q]admin',$arr0,$f0);
			if($r0){
				$arr=array(
					'code'=>200,
					'msg'=>'成功',
					);
				$this->returnjson($arr);
			}else{	
				$this->showreturn('','修改失败',201);
			}




	}





/*---------------------------------------------------------------------------------------------------*/
// 第三方平台方法
    //批量更新用户信息
     function userupdate($nick,$icon_url,$userid){
        $c = new TopClient;
        $req = new OpenimUsersUpdateRequest;
        $userinfos = new Userinfos;
	       if(!empty($nick)){
	            $userinfos->nick=$nick;
	        }elseif (!empty($icon_url)){
	            $userinfos->icon_url=$icon_url;
	        }
	        $userinfos->userid=$userid;
        // $userinfos->password="123456";
        $req->setUserinfos(json_encode($userinfos));
        $list = $this->toArray($c->execute($req));
        //返回添加失败的用户
        if(!empty($list['uid_fail'])){
            return $list['uid_fail'];
        }else {
            return 1;
        }
    }
   function toArray($simplexml_obj, $array_tags=array(), $strip_white=1)
    {
        if( $simplexml_obj )
        {
            if( count($simplexml_obj)==0 )
                return $strip_white?trim((string)$simplexml_obj):(string)$simplexml_obj;
            $attr = array();
            foreach ($simplexml_obj as $k=>$val) {
                if( !empty($array_tags) && in_array($k, $array_tags) ) {
                    $attr[] = self::toArray($val, $array_tags, $strip_white);
                }else{
                    $attr[$k] = self::toArray($val, $array_tags, $strip_white);
                }
            }
            return $attr;
        }
        return false;
    }








// 类结束
}