<?php 
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
//门店模块
class deptClassAction extends apiAction
{
	public function dataAction()
	{
		$deptarr 	= m('dept')->getdata();
		$userarr 	= m('admin')->getuser(1);
		$grouparr 	= m('reim')->getgroup($this->adminid);
		
		$arr['deptjson']	= json_encode($deptarr);
		$arr['userjson']	= json_encode($userarr);
		$arr['groupjson']	= json_encode($grouparr);
		$this->showreturn($arr);
	}


	//       7-1.允许门店打分的用户列表
	public function getmemberListAction(){
		$arr0=array(
			'isdeptscore'=>1,
		);
		$field0='`id`,`face`,`name`,`ranking`';
		$result=$this->db->getrows('[Q]admin',$arr0,$field0);
		if($result){
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
			foreach($result as $val){
				$arr['userlist'][]=array(
					'id'=>$val['id'],
					'headiconurl'=>FACE.$val['face'],
					'truename'=>$val['name'],
					'positionname'=>$val['ranking'],
				);
			}
			$this->returnjson($arr);

		}else{
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
			$arr['userlist']=null;
			$this->returnjson($arr);
		}

	}
//  7-2添加可以打分的用户
	public function addMemberAction(){
		//远端测试
		// $isall=$this->post('isAllMember');
		// $userlist=json_decode($this->post('userList'),true);

	     $data=json_decode($this->post('data'),true);
	     $isall=$data['isAllMember'];
	     $userlist=$data['userList'];
			$arr=array(
			'code'=>200,
			'msg'=>'成功',
		);
		$isdeptscore=1;
		if($isall==1){

			$result=$this->db->record('[Q]admin',"`isdeptscore`='".$isdeptscore."'","`isdeptscore`=0");
			if($result){
					 // 第三方平台推送
                $user_sende='admin';

                $p_sql='SELECT *from hrt_admin';
                $p_r=$this->db->query($p_sql);
                foreach ($p_r as $key => $value) {
                    $to_users[]='user'.$value['id'];
                }

                $summary='您已获得门店打分权限';
                $content='您已获得门店打分权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=7,1,$summary,$content);



				$this->returnjson($arr);
			}else{
				$this->showreturn('','失败',201);
			}
		}else{
			foreach($userlist as $val){
				$arr0=array(
					'id'=>$val,
				);
				$results=$this->db->record('[Q]admin',"`isdeptscore`='".$isdeptscore."'","`id`='".$arr0['id']."'");

				 $to_users[]='user'.$val;
			}

                 // 第三方平台推送
                $user_sende='admin';

                $summary='您已获得门店打分权限';
                $content='您已获得门店打分权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=7,1,$summary,$content);


			$this->returnjson($arr);
		}

	}

//    7-3.移除可以打分的用户
	public function removeMemberAction(){
		//远端测试
		// $uid=$this->post('userId');
		// $removedUserId=$this->post('removedUserId');

        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $removedUserId=$data['removedUserId'];
		$arr=array(
			'code'=>200,
			'msg'=>'成功',
		);
		$isdeptscore=0;
		$result=$this->db->record('[Q]admin',"`isdeptscore`='".$isdeptscore."'","`id`='".$removedUserId."'");
		if($result){
			  // 第三方平台推送
            $user_sende='admin';
            $to_users='user'.$removedUserId;

            $summary='您的门店打分权限已被移除';
            $content='您的门店打分权限已被移除';

            $push=$this->custmsgpush($user_sende,$to_users,$type=7,0,$summary,$content);

			$this->returnjson($arr);
		}else{
			$this->showreturn('','失败',201);
		}

	}
		//7-4门店列表
	public  function deptStoresListAction()
	{
		$data=json_decode($this->post('data'),true);
		$deptid=$data['departmentId'];
		if ($deptid == null) {
			$arr = array(
				'code' => 200,
				'msg' => '成功',
			);
			$arr1 = array(
				'isstore' => 2,
			);
			$f1 = '`id`';
			$r1 = $this->db->getone('[Q]dept', $arr1, $f1);
			$arr2 = array(
				'pid' => $r1['id'],
			);
			$f2 = '`id`,`name`,`isstore`,`latitude`,`longitude`,`address`';
			$r2 = $this->db->getrows('[Q]dept', $arr2, $f2);
			if ($r2) {
				foreach ($r2 as $v) {
				    if($v['isstore']==1){
				        $arr['departmentlist'][] = array(
				            'id' => $v['id'],
				            'name' => $v['name'],
				            'isstore' => $v['isstore'],
				            'latitude'=>$v['latitude'],
				            'longitude'=>$v['longitude'],
				            'address'=>$v['address'],
				        );
				    }else {
				        $arr['departmentlist'][] = array(
				            'id' => $v['id'],
				            'name' => $v['name'],
				            'isstore' => $v['isstore'],
				        );
				    }
					$arr3 = array(
						'id' => $v['id'],
					);
					$f3 = '`id`,`name`,`face`,`ranking`';
					$r3 = $this->db->getrows('[Q]admin', $arr3, $f3);
					if ($r3) {
						foreach ($r3 as $vv) {
							$arr['userlist'][] = array(
								'id' => $vv['id'],
								'headiconurl' => $vv['face'],
								'truename' => $vv['name'],
								'positionname' => $vv['ranking'],
							);
						}
					} else {
						$arr['userlist'] = null;
					}
				}
			} else {
				$arr['departmentlist'] = null;
				$arr['userlist'] = null;
			}
			$this->returnjson($arr);

		} else {
			//不是顶层结构
			$arr0 = array(
				'pid' => $deptid,
			);
			$f0 = '`id`,`name`,`isstore`,`latitude`,`longitude`,`address`';
			$r0 = $this->db->getrows('[Q]dept', $arr0, $f0);
			if ($r0) {
				$arr=array(
					'code'=>200,
					'msg'=>'成功',
				);
				foreach($r0  as $v0){
				    if($v0['isstore']==1){
				        $arr['departmentlist'][]=array(
				            'id'=>$v0['id'],
				            'name'=>$v0['name'],
				            'isstore' => $v0['isstore'],
				            'latitude'=>$v0['latitude'],
				            'longitude'=>$v0['longitude'],
				            'address'=>$v0['address'],
				        );
				    }else{
				        $arr['departmentlist'][]=array(
				            'id'=>$v0['id'],
				            'name'=>$v0['name'],
				            'isstore' => $v0['isstore'],
				        );
				    }
					$arr00=array(
						'id'=>$v0['id'],
					);
					$f00='`id`,`name`,`face`,`ranking`';
					$r0=$this->db->getrows('[Q]admin',$arr00,$f00);
					if($r0){
						foreach($r0 as $vv){
 							$arr['userlist'][]=array(
								'id'=>$vv['id'],
								'headiconurl'=>$vv['face'],
								'truename'=>$vv['name'],
								'positionname'=>$vv['ranking'],
							);
						}
					}else{
							$arr['userlist']=null;
					}
				}
			} else {
				$arr = array(
					'code' => 200,
					'msg' => '成功',
					'departmentlist' => null,
					'userlist' => null,
				);
			}
		}
		$this->returnjson($arr);
	}

//	7-5门店考核项分类列表
     public  function storesListAction(){
		 //测试使用
    //       $uid=$this->post('userId');
		  // $cid=$this->post('categoryId');


      	 $data=json_decode($this->post('data'),true);
		 $uid=$data['userId'];
		 $cid=$data['categoryId'];

		 if($cid==null){
			   $arr0=array(
				   'pid'=>1,
				   'score'=>0,
			   );
               $field='`id`,`items`,`pid`,`score`,`isend`';
			   $result=$this->db->getrows('[Q]deptscore',$arr0,$field);
			   if($result){
				   $arr=array(
					   'code'=>200,
					   'msg'=>'成功',

				   );
				   foreach ($result  as $v) {

					   if($v['isend']==0){
						   $arr['categorylist'][]=array(
							   'categoryid'=>$v['id'],
							   'name'=>$v['items'],
							   'islastlevel'=>0,
						   );
					   }else{
						   $arr['categorylist'][]=array(
							   'categoryid'=>$v['id'],
							   'name'=>$v['items'],
							   'islastlevel'=>1,
						   );
					   }
 					}

			   }else{
                   $arr=array(
					   'code'=>200,
					   'msg'=>'成功',
					   'categorylist'=>null,
				   );
			   }
			 $this->returnjson($arr);

		 }else{
			$arr0=array(
				'pid'=>$cid,
				'score'=>0,
			);
			 $field='`id`,`items`,`pid`,`score`,`isend`';
			 $result=$this->db->getrows('[Q]deptscore',$arr0,$field);

			 if($result){
				 $arr=array(
					 'code'=>200,
					 'msg'=>'成功',

				 );
				 foreach ($result  as $v) {

					 if($v['isend']==0){
						 $arr['categorylist'][]=array(
							 'categoryid'=>$v['id'],
							 'name'=>$v['items'],
							 'islastlevel'=>0,
						 );
					 }else{
						 $arr['categorylist'][]=array(
							 'categoryid'=>$v['id'],
							 'name'=>$v['items'],
							 'islastlevel'=>1,
						 );
					 }
				 }

			 }else{
				 $arr=array(
					 'code'=>200,
					 'msg'=>'成功',
					 'categorylist'=>null,
				 );
			 }
			 $this->returnjson($arr);

		 }

	 }
// 查找门店考核项分类列表的递归函数
         public  function  get_scoreArray($id){
			 $sql='select  *from hrt_deptscore where pid='.$id;
			 $result=$this->db->query($sql);
			 $ids=array();
			 while($row=mysqli_fetch_assoc($result)){
				 print_r($row);
				  $ids[]=$this->get_scoreArray($row['id']);

			 }
			 return $ids;
		 }

//  7-6获取考核信息数据
       public  function getScoreDataAction(){
				// $cid=$this->post('categoryId');

				$data=json_decode($this->post('data'),true);
		    	$cid=$data['categoryId'];

		   		$arr0=array(
					'pid'=>$cid,
				);
		   		$field='`id`,`items`,`score`';
		   		$result=$this->db->getrows('[Q]deptscore',$arr0,$field);
		    	if($result){
					$arr=array(
						'code'=>200,
						'msg'=>'成功',
					);
                     foreach($result as $v){
     				    $arr['list'][]=array(
							'id'=>$v['id'],
							'content'=>$v['items'],
							'score'=>$v['score'],
						);
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
//	7-7指定考核项下的考核提交
			public function  scoreSubmitAction(){
				//测试用
				// $deptid=$this->post('departmentId');
				// $cid=$this->post('categoryId');
				// $score=$this->post('selectedList');
				// $img=$this->post('imageList');
				// $content=$this->post('content');
				// $uid=$this->post('userId');

				$data=json_decode($this->post('data'),true);
				$uid=$data['userId'];
				$deptid=$data['departmentId'];
				$cid=$data['categoryId'];
				$score=json_encode($data['selectedList'],true);
				$img=json_encode($data['imageList'],true);
				$content=$data['content'];

				//从查询出部门名称
				$sql_1='SELECT name from hrt_dept where id='.$deptid;
				$r1=$this->db->query($sql_1);
				$row1=mysqli_fetch_array($r1);
				$deptname=$row1['name'];

				$sql2='SELECT items from hrt_deptscore where id='.$cid;
				$r2=$this->db->query($sql2);
				$row2=mysqli_fetch_array($r2);
				$ctname=$row2['items'];


				$sql3='SELECT name from hrt_admin where id='.$uid;
				$r3=$this->db->query($sql3);
				$row3=mysqli_fetch_array($r3);
				$username=$row3['name'];



				// 根据选择的选项计算出总得分
				$count=0;

				foreach(json_decode($score,true) as $v){
					$sql4='SELECT score from hrt_deptscore where id='.$v;
					$r4=$this->db->query($sql4);
					$row4=mysqli_fetch_array($r4);
					$count=$count+(int)$row4['score'];
				}
                $arr0=array(
					'deptid'=>$deptid,
					'deptname'=>$deptname,
					'cid'=>$cid,
					'ctname'=>$ctname,
					'select'=>$score,
					'score'=>$count,
					'img'=>$img,
					'content'=>$content,
					'uid'=>$uid,
					'username'=>$username,
					'date'=>time(),
					'dt'=>date('Y-m-d H:i:s',time()),
				);
				$result=$this->db->record('[Q]deptresult',$arr0);
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

// 第三方平台推送消息方法


    public function businessnews($user_sende,$to_users,$type,$id,$summary,$content){
        $list = array(
            'customizeMessageType'=>'Business',
            'type'=>$type,
            'id'=>$id,
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





}