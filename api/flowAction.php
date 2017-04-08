<?php
//3.流程模块
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
class flowClassAction extends apiAction{
//        3-1获取流程类型数据列表
        public function getFlowClassAction(){
            // $flowid=$this->post('flowCategoryId');

           $data=json_decode($this->post('data'),true);
           $flowid=$data['flowCategoryId'];


            if($flowid==null){
                //表示获取最顶层的数据列表
                $arr0=array(
                    'pid'=>1,
                );
                $field0='`id`,`name`';
                $r0=$this->db->getrows('[Q]flows_test',$arr0,$field0);
                if($r0){
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                    );
                    foreach($r0 as $v0){
                        //判断是否为最后一级
                        $pp='SELECT *from  hrt_flows_test where pid='.$v0['id'].' and isflow !=1';
                        $r_pp=$this->db->query($pp);
                        $num=mysqli_num_rows($r_pp);
                        if($num>0){
                            $arr['flowcategorylist'][]=array(
                                'categoryid'=>$v0['id'],
                                'name'=>$v0['name'],
                                'islastlevel'=>0,
                            );
                        }else{
                            $arr['flowcategorylist'][]=array(
                                'categoryid'=>$v0['id'],
                                'name'=>$v0['name'],
                                'islastlevel'=>1,
                            );
                        }

                    }
                    $this->returnjson($arr);
                }else{
                    $this->showreturn('','失败',201);
                }

            }else{
                //获取指定层的数据列表->递归函数去获得.
                // $ids=$this->getList($flowid);
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                );
                // foreach($ids as $vv){
                    //判断是否为最后一级
                    $pp='SELECT *from  hrt_flows_test where pid='.$flowid;
                    $r_pp=$this->db->query($pp);
                    $num=mysqli_num_rows($r_pp);
                    if($num>0){
                        foreach($r_pp as $vv){
                             $pp1='SELECT *from  hrt_flows_test where pid='.$vv['id'].' and isflow !=1';
                            $r_pp1=$this->db->query($pp1);
                            $num1=mysqli_num_rows($r_pp1);
                            if($num1>0){
                               $status=0;
                            }else{
                               $status=1;
                            }

                            $arr['flowcategorylist'][]=array(
                            'categoryid'=>$vv['id'],
                            'name'=>$vv['name'],
                            'islastlevel'=>$status,
                             );

                        }      
                    }else{
                       $this->showreturn('','失败',201);
                    }
                // }
                $this->returnjson($arr);
            }
        }

//       获取流程分类数据结构的递归函数
      public  function  getList($id){
            $sql='SELECT id ,name from hrt_flows_class where pid='.$id;
            $r=$this->db->query($sql);
            global $ids;

             if($r){
                while($row=mysqli_fetch_array($r)){

                    $ids[]=array(
                        'id'=>$row['id'],
                        'name'=>$row['name'],
                    );
                    $rows=$this->getList($row['id']);
                }

            }
            return $ids;
      }

            //3-2某个分类下的流程列表
    public function getFlowListAction(){
           // $flowid=$this->post('flowCategoryId');

          $data=json_decode($this->post('data'),true);
          $flowid=$data['flowCategoryId'];

            $sql='SELECT id,name,modeltype from hrt_flows_test where pid='.$flowid;
            $r=$this->db->query($sql);
            if($r && mysqli_num_rows($r)>0){

                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                    );
                    foreach($r as $v){
                        $arr['flowlist'][]=array(
                            'flowid'=>$v['id'],
                            'name'=>$v['name'],
                            'type'=>$v['modeltype'],
                        );
                    }
            }else{
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'flowlist'=>null,
                );
            }
            $this->returnjson($arr);
    }

//        3-3加载流程数据初始数据
        public function getFlowInitialDataAction(){
                // $flowid=$this->post('flowId');


               $data=json_decode($this->post('data'),true);
               $flowid=$data['flowId'];

                $sql1='SELECT *from hrt_flows_test where  id='.$flowid;
                $r1=$this->db->query($sql1);
                $row=mysqli_num_rows($r1);

                if($r1 && $row>0){
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                    );
                    foreach($r1 as $v1){
                        $arr['flowmoduletype']=$v1['modeltype'];
                        $arr['isallowskipstep']=$v1['isup'];
                        $arr['content']=$v1['description'];
                        if($v1['modeltype']==0){
                            $arr['customlist']=json_decode($v1['value'],true);
                        }
                    }
                    //流程对应的审核步骤
                    $sql2='SELECT *from hrt_flows_step where fid='.$flowid;
                    $r2=$this->db->query($sql2);
                    foreach($r2 as $v2){
                        if($v2['auditor']==0){
                            $arr['auditsteplist'][]=array(
                                'stepid'=>$v2['id'],
                                'stepname'=>$v2['name'],
                                'audituserid'=>null,
                            );
                        }else{
                            $arr['auditsteplist'][]=array(
                                'stepid'=>$v2['id'],
                                'stepname'=>$v2['name'],
                                'audituserid'=>$v2['auditor'],
                            );
                        }

                    }
                $this->returnjson($arr);
                }else{
                    $this->showreturn('','失败',201);
                }
        }

        //3-4申请流程
        public function  applyFlowAction(){
            // $uid=$this->post('userId');
            // $flowid=$this->post('flowId');
            // $imglist=$this->post('imageList');
            // $sendtostep=$this->post('sendToStep');
            // $sendtouser=$this->post('sendToUser');
            // $fdata=$this->post('data');
            //生成随机流程单号
            // $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
            // $code= $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(),2,5) . sprintf('%02d', rand(0, 99));
            // $applydt=date('Y-m-d H:i:s',time());

            // $opdate=date('Y-m-d H:i:s',time()+1);

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $flowid=$data['flowId'];
           $imglist=json_encode($data['imageList'],true);
           $sendtostep=$data['sendToStep'];
           $sendtouser=$data['sendToUser'];
           $fdata=json_encode($data['data'],JSON_UNESCAPED_UNICODE);
//           //生成随机流程单号
           $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
           $code= $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(),2,5) . sprintf('%02d', rand(0, 99));
            $applydt=date('Y-m-d H:i:s',time());

            $opdate=date('Y-m-d H:i:s',time()+1);

//            1.根据流程id找到流程的模板和审批步骤
//            1-1模版
            $sql1='SELECT *from hrt_flows_test where id='.$flowid;
            $r1=$this->db->query($sql1);
            $row=mysqli_fetch_assoc($r1);
            if($row>0){
                $fname=$row['name'];
                $modeltype=$row['modeltype'];

            }else{
                $this->showreturn('','失败',201);
            }
//           1-2审批步骤
            $sql2='SELECT *from hrt_flows_step where fid='.$flowid.' order by id';
            $r2=$this->db->query($sql2);
            $row2=mysqli_fetch_array($r2);
            $ids=array();
            if($row2>0){
                // 查询出发布人的名称
                $admin_sql='SELECT *from hrt_admin where id='.$uid;
                $a_r=$this->db->query($admin_sql);
                $a_row=mysqli_fetch_array($a_r);
                if($a_row>0){
                    $admin_name=$a_row['name'];
                }


                foreach($r2 as $v2){

                    $step[]=$v2['id'];
                    $ids[]=$v2['auditor'];
                     // // 推送
                   $ticker=$text=$admin_name.'的'.$fname;
                   if($v2['auditor']=0){
                         $push=$this->android($sendtouser,$ticker,'待审核流程',$text,2);
                   }else{
                         $push=$this->android($v2['auditor'],$ticker,'待审核流程',$text,2);
                   }
                  

                }
            }else{
                $this->showreturn('','失败',201);
            }
            $allcheck=implode(',',$step);
            $allcheckid=implode(',',$ids);
            //根据审核步骤找出审核人id
            if($sendtouser==null){
                $sql_c='SELECT  *from hrt_flows_step where fid='.$flowid.' and id='.$sendtostep;
                $r_c=$this->db->query($sql_c);
                $rr=mysqli_fetch_array($r_c);
                $sendtouser=$rr['auditor'];
//                对应步骤下的当前审核人
//                echo $rr['auditor'];
            }
            $arr0=array(
                'code'=>$code,
                'flowid'=>$flowid,
                'flowname'=>$fname,
                'flowmodeltype'=>$modeltype,
                'uid'=>$uid,
                'applydt'=>$applydt,
                'allcheck'=>$allcheck,
                'allcheckid'=>$allcheckid,
                'nowcheckid'=>$sendtostep,
                'nowcheckuserid'=>$sendtouser,
                'imglist'=>$imglist,
                'data'=>$fdata,
                'opdate'=>$opdate,
            );
          
//            print_r($arr0);
            $result=$this->db->record('[Q]flows_bill',$arr0);
            $id=$this->db->insert_id();
         //申请流程完毕后根据提交的审批步骤写入到result表中进行步骤的统计
//            1.首先判断提交的审批步骤是否为第一步,是否有跳过的情况
            $now_chk=','.$sendtostep;
            $chk=strstr($allcheck,$now_chk,true);
//            echo $chk;
            if($chk==null){
//                没有跳过审核,将审核步骤对应的审核人写入result中
                if($sendtouser==null){
                    $chkid= $rr['auditor'];
                }else{
                    $chkid=$sendtouser;
                }
                $crr=array(
                    'fid'=>$id,
                    'stepid'=>$sendtostep,
                    'checkid'=>$chkid,
                    'status'=>0,
                    'opdate'=>$opdate,
                );
                $cr=$this->db->record('[Q]flows_result',$crr);



            }else{
//                 有跳过审核,将跳过审核的记录在result中
                $cArr=explode(',',$chk);
                foreach($cArr as $cv){
                     //根据审核步骤找到相应的审核人
                    $sql_ck='SELECT  *from hrt_flows_step where fid='.$flowid.' and id='.$cv;
                    $r_ck=$this->db->query($sql_ck);
                    $rck=mysqli_fetch_array($r_ck);
//                    $rck['auditor'];审核人id
                    $crr_1=array(
                        'fid'=>$id,
                        'stepid'=>$cv,
                        'checkid'=>$rck['auditor'],
//                        4表示跳过
                        'status'=>4,
                        'opdate'=>$opdate,
                    );
                    $cr_1=$this->db->record('[Q]flows_result',$crr_1);
                }
                //审核人开始审核的状态加入
                if($sendtouser==null){
                    $chkid= $rr['auditor'];
                }else{
                    $chkid=$sendtouser;
                }
                $crr=array(
                    'fid'=>$id,
                    'stepid'=>$sendtostep,
                    'checkid'=>$chkid,
                    'status'=>0,
                    'opdate'=>date('Y-m-d H:i:s',time()+2),
                );
                $cr=$this->db->record('[Q]flows_result',$crr);

            }

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
//    3-5我申请的流程列表
    public function myApplyFlowListAction(){
             // $uid=$this->post('userId');
             // $pageNo=$this->post('pageNo');
             // $pageSize=$this->post('pageSize');

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $pageNo=$data['pageNo'];
           $pageSize=$data['pageSize'];

//            1.计算分页总条数
             $t_sql='SELECT *from hrt_flows_bill where uid='.$uid;
             $t_r=$this->db->query($t_sql);
             $total=mysqli_num_rows($t_r);
             $eNum=ceil($total/$pageSize);
             $limit=(($pageNo-1)*$pageSize).",".$pageSize;

//            2.当前审核人是我但还没有完成审核的流程数量
            $num_sql='SELECT *from hrt_flows_bill  where  nowcheckuserid='.$uid.' and nowstatus=0  and isdel=0';
            $r_num=$this->db->query($num_sql);
            $num=mysqli_num_rows($r_num);
        //数量
//            echo $num;

             $sql='SELECT a.id,a.flowname,a.fstatus,a.nowcheckuserid ,b.name, a.applydt, a.opdate
                    from hrt_flows_bill   a
                    left join hrt_admin  b  on a.nowcheckuserid=b.id
                    where  a.uid='.$uid.'  and  a.isdel=0  order by a.opdate desc limit '.$limit;
             $r=$this->db->query($sql);
             $row=mysqli_fetch_array($r);


            if($r && $row>0){

                if($pageNo>=$eNum){
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'unauditcount'=>$num,
                    );
                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>false,
                        'unauditcount'=>$num,
                    );
                }
                foreach($r as $v){
                    $arr['flowlist'][]=array(
                        'id'=>$v['id'],
                        'flowname'=>$v['flowname'],
                        'status'=>$v['fstatus'],
                        'audituserid'=>$v['nowcheckuserid'],
                        'audituser'=>$v['name'],
                        'publishtime'=>strtotime($v['applydt']),
                        'updatetime'=>strtotime($v['opdate']),
                    );

                }

             }else{
                if($pageNo>=$eNum){
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'flowlist'=>null,
                        'unauditcount'=>$num,
                    );
                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>false,
                        'flowlist'=>null,
                        'unauditcount'=>$num,
                    );
                }
             }

            $this->returnjson($arr);
    }

//3-6流程信息详情
    public  function   flowDetailAction(){
        // $id=$this->post('id');
        // $uid=$this->post('userId');


       $data=json_decode($this->post('data'),true);
       $id=$data['id'];
       $uid=$data['userId'];
        $sql='SELECT    b.face,b.id,b.`name`,a.applydt,a.fstatus,a.allcheckid,
                a.nowcheckuserid,c.id as flowid  ,a.code,a.flowname,c.description,
                c.modeltype,a.data,a.imglist,a.flowid as fid
                from  hrt_flows_bill  a
                left join  hrt_admin  b    on  a.uid=b.id
                left join hrt_flows_test  c  on   a.flowid=c.id
                 where  a.id='.$id;
        $r=$this->db->query($sql);
        $row=mysqli_fetch_assoc($r);
        if($row>0){
            //输出图片
            $iArr=json_decode($row['imglist'],true);

             foreach($iArr as $img){
                $imgArr[]=FACE.$img;    

             }
            $sArr=array(
                'flowid'=>$row['flowid'],
                'flowcode'=>$row['code'],
                'flowname'=>$row['flowname'],
                'flowcontent'=>$row['description'],
                'flowmoduletype'=>$row['modeltype'],
                'data'=>json_decode($row['data'],true),
                'imagelist'=>$imgArr,

            );
        		// 信息详情里面下一步审核人的信息
        	$now_id=$row['nowcheckuserid'].',';
        	// echo $now_id;
        	$nextcheckid=strstr($row['allcheckid'],$now_id);
        	// echo  $nextcheckid;
        	$nextArr=explode(',',$nextcheckid);
        	$next_id=$nextArr[1];
        	
        	if($next_id==null){
        		 $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'applyheadicon'=>FACE.$row['face'],
                'applyuserid'=>$row['id'],
                'applyuser'=>$row['name'],
                'applytime'=>strtotime($row['applydt']),
                'status'=>$row['fstatus'],
                'audituserid'=>$row['nowcheckuserid'],
                'flowdata'=>$sArr,
                'nextauditinfo'=>null,
           		 );
        	}else{
        		//根据审核人id 得出审核步骤和名称
        		$arr_next=array(
        			'auditor'=>$next_id,
        			'fid'=>$row['fid'],
        			);
        	
        		$next_r=$this->db->getone('[Q]flows_step',$arr_next);
        		// print_r($next_r);
        		if($next_r['auditor']==0){
        			$nArr=array(
        				'stepid'=>$next_r['id'],		
        				'stepname'=>$next_r['name'],		
        				'audituserid'=>null,		
        				);
        			 $arr=array(
			                'code'=>200,
			                'msg'=>'成功',
			                'applyheadicon'=>FACE.$row['face'],
			                'applyuserid'=>$row['id'],
			                'applyuser'=>$row['name'],
			                'applytime'=>strtotime($row['applydt']),
			                'status'=>$row['fstatus'],
			                'audituserid'=>$row['nowcheckuserid'],
			                'flowdata'=>$sArr,
			                'nextauditinfo'=>$nArr,
           			 );
        		}else{
        			$nArr=array(
        				'stepid'=>$next_r['id'],		
        				'stepname'=>$next_r['name'],		
        				'audituserid'=>$next_r['auditor'],		
        				);
        			 $arr=array(
			                'code'=>200,
			                'msg'=>'成功',
			                'applyheadicon'=>FACE.$row['face'],
			                'applyuserid'=>$row['id'],
			                'applyuser'=>$row['name'],
			                'applytime'=>strtotime($row['applydt']),
			                'status'=>$row['fstatus'],
			                'audituserid'=>$row['nowcheckuserid'],
			                'flowdata'=>$sArr,
			                'nextauditinfo'=>$nArr,
           			 );
        		}

        	}

            //记录审核的步骤....
            $step_sql='SELECT d.name as stepname ,a.checkid,b.name,b.face,a.`status`,
                        a.remark,a.opdate
                        from hrt_flows_result  a
                        left join hrt_admin  b  on a.checkid=b.id
                        left join hrt_flows_bill  c  on a.fid=c.id
                        left  join hrt_flows_step d on c.flowid=d.fid
                        where  a.stepid=d.id  and  a.fid='.$id.' order by a.opdate desc';
            $s_r=$this->db->query($step_sql);

	         foreach($s_r as $vv) {
                if ($vv['status'] == 5) {
                    $arr['auditsteplist'][] = array(
                        'stepstatus' => 4,

                    );
                }else if($vv['status']==4){
                    $arr['auditsteplist'][] = array(
                        'stepstatus' => 1,
                        'operatestepname'=>$vv['stepname'],

                    );
                }else if($vv['status']==3){
                    $arr['auditsteplist'][] = array(
                        'stepstatus' => 3,
                        // 'operatestepname'=>$vv['stepname'],
                        'operateuserid'=>$vv['checkid'],
                        'operateuser'=>$vv['name'],
                        'operateuserheadicon'=>FACE.$vv['face'],
                        'updatetime'=>strtotime($vv['opdate']),

                    );
                }else if($vv['status']==0){
                    $arr['auditsteplist'][] = array(
                        'stepstatus' => 2,
                        'operatestepname'=>$vv['stepname'],
                        'operateuserid'=>$vv['checkid'],
                        'operateuser'=>$vv['name'],
                        'operateuserheadicon'=>FACE.$vv['face'],
                        'updatetime'=>strtotime($vv['opdate']),

                    );
                }else {
                    $arr['auditsteplist'][] = array(
                        'stepstatus' => 2,
                        'operatestepname'=>$vv['stepname'],
                        'operateuserid'=>$vv['checkid'],
                        'operateuser'=>$vv['name'],
                        'status'=>$vv['status'],
                        'cause'=>$vv['remark'],
                        'operateuserheadicon'=>FACE.$vv['face'],
                        'updatetime'=>strtotime($vv['opdate']),
                    );
                }

            }
             $arr['auditsteplist'][]=array(
            		'stepstatus'=>0,
            	);

            $this->returnjson($arr);

        }else{
            $this->showreturn('','失败',201);
        }


    }





//3-7同意申请的流程
            public  function agreeFlowAction(){
                // $uid=$this->post('userId');
                // $id=$this->post('id');
                // $remark=$this->post('remark');
                // $opdate=date('Y-m-d H:i:s',time());

               $data=json_decode($this->post('data'),true);
               $id=$data['id'];
               $nextaudituserid=$data['nextaudituserid'];
               $uid=$data['userId'];
               $remark=$data['remark'];
               $opdate=date('Y-m-d H:i:s',time());
                //1.将result表中的状态改为同意

               $sql1="UPDATE hrt_flows_result set status=2,remark='$remark',opdate='$opdate' where fid=$id and checkid=$uid and status=0";
					// echo $sql1;
				$r1=$this->db->query($sql1);
				// die;
                // $arr1=array(
                //     'status'=>2,
                //     'remark'=>$remark,
                //     'opdate'=>$opdate,
                // );
                // $f1=array(
                //     'fid'=>$id,
                //     'checkid'=>$uid,
                // );
           
                // $r1=$this->db->record('[Q]flows_result',$arr1,$f1);
                if($r1){
//                    2.将flow_bill表中的状态改变,进入下一个审核人

                     $arr2=array(
                         'id'=>$id,
                     );
                    $r2=$this->db->getone('[Q]flows_bill',$arr2);
                    $flowid=$r2['flowid'];
                    $allid=$r2['allcheckid'];
                    $allcheck=$r2['allcheck'];// 所有步骤id
                    $n_uid=$uid.',';
                    $new_id=strstr($allid,$n_uid);
                    if($new_id==null){
                      //当前审批人为最后一个审批人
                        $now_chk=$uid;
                        $now_status=2;
                        $fstatus=2;
                    }else{
                        $nArr=explode(',',$new_id);
                        if($nArr[1]==0){
                        	 $now_chk=$nextaudituserid;	
                        }else{
                        	 $now_chk=$nArr[1];
                        }
                        $now_status=0;
                        $fstatus=0;

                    }
             //根据uid得到当前步骤id 然后计算出下一步步骤id
                $urr=array(
                    'auditor'=>$uid,
                    'fid'=>$flowid,
                );
                    $u_r=$this->db->getone('[Q]flows_step',$urr);
                    $step_id=$u_r['id'];//当前步骤id
                    $n_step=$step_id.',';

                    $new=strstr($allcheck,$n_step);
                    if($new==null){
                        //最后一步
                        $now_checkid=$step_id;
                    }else{
                        $sArr=explode(',',$new);
                        $now_checkid=$sArr[1];
                    }

                 $arr2=array(
                    'nowcheckid'=>$now_checkid,
                     'nowcheckuserid'=>$now_chk,
                     'nowstatus'=>$now_status,
                     'fstatus'=>$fstatus,
                     'opdate'=>$opdate,
                 );

                $f2=array(
                    'id'=>$id,
                );
                $r2=$this->db->record('[Q]flows_bill',$arr2,$f2);
                if($r2){
//                    3-1.将当前的操作事件写入result表中
                    // $arr3=array(
                    //     'status'=>2,
                    //     'opdate'=>$opdate,
                    // );
                    //  $f3=array(
                    //      'fid'=>$id,
                    //      'checkid'=>$uid,
                    //  );
                    // $r3=$this->db->record('[Q]flows_result',$arr3,$f3);
//                    3-2.将下一个步骤人进入开始审核状态
                    if($new_id==null){
//                        为最后一个审批人
                        $arr4=array(
                            'fid'=>$id,
                            'stepid'=>$step_id,
                            'checkid'=>$uid,
                            'status'=>5,
                            'opdate'=>date('Y-m-d H:i:s',time()+1),
                        );
                        $r4=$this->db->record('[Q]flows_result',$arr4);
                    }else{
                        // $nArr=explode(',',$new_id);
                        // $now_chk=$nArr[1];
                          $arr4=array(
                            'fid'=>$id,
                            'stepid'=>$now_checkid,
                            'checkid'=>$now_chk,
                            'status'=>0,
                            'opdate'=>date('Y-m-d H:i:s',time()+1),
                            );
                            $r4=$this->db->record('[Q]flows_result',$arr4);
                    }
                    //最终状态进行第三方推送
                    if($fstatus==2){

                          $user_sende='admin';

                          $p_sql='SELECT *from hrt_flows_bill where id='.$id;
                          $p_r=$this->db->query($p_sql);
                          $row=mysqli_fetch_array($p_r);
                          $to_users='user'.$row['uid'];

                          $summary='您的流程'.($row['flowname']).'已经被通过';
                          $content='您的流程'.($row['flowname']).'已经被通过';  

                          $push=$this->businessnews($user_sende,$to_users,$type=2,$id,$summary,$content);
                    }

                    $arr=array(
                    	'code'=>200,
                    	'msg'=>'成功',	
                    	);
                    $this->returnjson($arr);
                }else{
                    $this->showreturn('','失败',201);
                }

        }else{
            $this->showreturn('','不成功',201);
        }

    }
//3-8驳回申请的流程
    public function unpassFlowAction(){
        // $uid=$this->post('userId');
        // $id=$this->post('id');
        // $remark=$this->post('remark');
        // $opdate=date('Y-m-d H:i:s',time());

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $id=$data['id'];
       $remark=$data['remark'];
       $opdate=date('Y-m-d H:i:s',time());

//        1.在flows_result表中记录驳回的历史记录
        $arr1=array(
            'status'=>1,
            'remark'=>$remark,
            'opdate'=>$opdate,
        );
        $f1=array(
        	 'fid'=>$id,
          	 'checkid'=>$uid,
        	);
        $r1=$this->db->record('[Q]flows_result',$arr1,$f1);
        if($r1){
//        2.在flows_bill表中改变当前审核的状态和最终状态
            $arr2=array(
                'fstatus'=>1,
                'nowstatus'=>1,
                'opdate'=>$opdate,

            );
            $f2=array(
                'id'=>$id,
                'nowcheckuserid'=>$uid,
            );
            $r2=$this->db->record('[Q]flows_bill',$arr2,$f2);
            if($r2){
                //第三方推送驳回的消息


              $user_sende='admin';

              $p_sql='SELECT *from hrt_flows_bill where id='.$id;
              $p_r=$this->db->query($p_sql);
              $row=mysqli_fetch_array($p_r);
              $to_users='user'.$row['uid'];

              $summary='您的流程'.($row['flowname']).'已经被驳回';
              $content='您的流程'.($row['flowname']).'已经被驳回';  

              $push=$this->businessnews($user_sende,$to_users,$type=2,$id,$summary,$content);



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
//3-9删除申请的流程
     public function deleteFlowAction(){
          // $id=$this->post('id');

         $data=json_decode($this->post('data'),true);
         $id=$data['id'];

          $arr=array(
              'isdel'=>1,
          );
         $field=array(
             'id'=>$id,
         );
         $result=$this->db->record('[Q]flows_bill',$arr,$field);
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

//3-10需要我审批的流程列表
        public  function  myrequiredFlowListAction(){
            // $uid=$this->post('userId');
            // $pageNo=$this->post('pageNo');
            // $pageSize=$this->post('pageSize');


           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $pageNo=$data['pageNo'];
           $pageSize=$data['pageSize'];

            //总的需要我审批的流程

            $t_sql='SELECT * from hrt_flows_bill
                    where isdel=0  and fstatus=0  and nowcheckuserid='.$uid;
            $t_r=$this->db->query($t_sql);
            $total=mysqli_num_rows($t_r);
            $eNum=ceil($total/$pageSize);
            $limit=(($pageNo-1)*$pageSize).",".$pageSize;

            $sql='SELECT a.id,a.flowname,b.name as username ,b.face,a.fstatus,a.opdate,c.name,a.nowcheckuserid,a.applydt
                    from hrt_flows_bill a
                    left join  hrt_admin  b  on  a.uid=b.id
                    left join hrt_admin c  on a.nowcheckuserid=c.id
                    where a.isdel=0  and a.fstatus=0  and a.nowcheckuserid='.$uid.' order by a.opdate desc  limit '.$limit ;
            $r=$this->db->query($sql);
            $row=mysqli_num_rows($r);
            if($r && $row>0){
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
                    $arr['flowlist'][]=array(
                        'id'=>$v['id'],
                        'flowname'=>$v['flowname'],
                        'publishuser'=>$v['username'],
                        'publishheadicon'=>FACE.$v['face'],
                        'status'=>$v['fstatus'],
                        'audituserid'=>$v['nowcheckuserid'],
                        'audituser'=>$v['name'],
                        'updatetime'=>strtotime($v['opdate']),
                        'publishtime'=>strtotime($v['applydt']),
                    );
                }

            }else{
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'isend'=>true,
                    'flowlist'=>null,
                );
            }
            $this->returnjson($arr);
        }
//3-11已经审批的流程列表

    public  function  myapprovedFlowListAction(){
        // $uid=$this->post('userId');
        // $pageNo=$this->post('pageNo');
        // $pageSize=$this->post('pageSize');
        // $dateFilter=json_decode($this->post('dateFilter'),true);

        // $year=$dateFilter['year'];
        // @$month=sprintf("%02d",$dateFilter['month']);
        // if($month==00){
        //     $date=$year;
        // }else{
        //     $date=$year.'-'.$month;
        // }
        // $typeId=$this->post('typeId');
        // $memberId=$this->post('memberId');

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $pageNo=$data['pageNo'];
       $pageSize=$data['pageSize'];
       $dateFilter=$data['dateFilter'];
       $year=$dateFilter['year'];
       @$month=sprintf("%02d",$dateFilter['month']);
           if($month==00){
               $date=$year;
           }else{
               $date=$year.'-'.$month;
           }
       $typeId=$this->post('typeId');
       $memberId=$data['memberId'];

        //计算全部符合条件的总数,实现分页
        $t_sql='SELECT *from hrt_flows_result where checkid='.$uid.' and status !=0 and status !=4 and status !=5';
        $t_r=$this->db->query($t_sql);
        $total=mysqli_num_rows($t_r);
        $eNum=ceil($total/$pageSize);
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;
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

        if($dateFilter==null || $year==null){
        // //表示查看全部时间内符合条件的信息
            if($typeId==null){
                //全部类型
                if($memberId==null){
                    $sql='SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                        left join hrt_admin d on b.uid=d.id
                          where a.checkid='.$uid.'  and a.status !=0 and a.status!=4 and a.status !=5
                          and b.isdel=0
                          order by b.opdate desc  limit '.$limit ;
                    $r=$this->db->query($sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                                'publishtime'=>strtotime($v['applydt']),	
                            );
                        }
                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }


                }else{
                    $sql='SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                         left join hrt_admin d on b.uid=d.id
                          where a.checkid='.$uid.'  and a.status !=0 and a.status!=4 and a.status !=5   and b.isdel=0  and   
                          b.uid='.$memberId.'  order by b.opdate desc  limit '.$limit ;
                    $r=$this->db->query($sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                                'publishtime'=>strtotime($v['applydt']),  

                            );
                        }
                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }


                }

            }else{
                    //指定流程类型
//                1.根据类型id选出都有那些流程
                if($memberId==null){
                    $check_sql='SELECT *from hrt_flows_test where  pid='.$typeId;
                    $c_r=$this->db->query($check_sql);
                    foreach($c_r as $c_v){
                        $flowsid[]=$c_v['id'];
                    }
                    $str_id=implode(',',$flowsid);
                    $f_sql="SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                         left join hrt_admin d on b.uid=d.id
                          where a.checkid=$uid  and a.status !=0 and a.status!=4 and a.status !=5  and b.isdel=0 
                       and b.flowid in ($str_id)  order by b.opdate desc  limit $limit " ;
                    $r=$this->db->query($f_sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                               'publishtime'=>strtotime($v['applydt']), 	
                            );
                        }
                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }

                }else{
                    $check_sql='SELECT *from hrt_flows_tets where pid='.$typeId;
                    $c_r=$this->db->query($check_sql);
                    foreach($c_r as $c_v){
                        $flowsid[]=$c_v['id'];
                    }
                    $str_id=implode(',',$flowsid);
                    $f_sql="SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                         left join hrt_admin d on b.uid=d.id
                          where a.checkid=$uid  and a.status !=0 and a.status!=4 and a.status !=5  and b.isdel=0
                       and b.flowid in ($str_id)  and  b.uid=$memberId order by b.opdate desc  limit $limit " ;
                    $r=$this->db->query($f_sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                                'publishtime'=>strtotime($v['applydt']), 	

                            );
                        }
                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }

                }

            }
        }else{
            //有时间条件查询

            if($typeId==null){
                //全部类型
                if($memberId==null){
                    $sql='SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                         left join hrt_admin d on b.uid=d.id
                          where a.checkid='.$uid.'  and a.status !=0 and a.status!=4 and a.status !=5
                          and b.isdel=0  and  b.applydt  like '."'$date%'".'
                           order by b.opdate desc  limit '.$limit ;
                    $r=$this->db->query($sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                               'publishtime'=>strtotime($v['applydt']), 	

                            );
                        }
                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }


                }else{
                    $sql='SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                         left join hrt_admin d on b.uid=d.id
                          where a.checkid='.$uid.'  and a.status !=0 and a.status!=4 and a.status !=5
                           and b.isdel=0 
                          and  b.applydt  like '."'$date%'".'  and
                          b.uid='.$memberId.'  order by b.opdate desc  limit '.$limit ;
                    $r=$this->db->query($sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                               'publishtime'=>strtotime($v['applydt']), 	
                            );
                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }

                }

            }else{
                //指定流程类型
//                1.根据类型id选出都有那些流程
                if($memberId==null){
                    $check_sql='SELECT *from hrt_flows_test where  pid='.$typeId;
                    $c_r=$this->db->query($check_sql);
                    foreach($c_r as $c_v){
                        $flowsid[]=$c_v['id'];
                    }
                    $str_id=implode(',',$flowsid);
                    $f_sql="SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                         left join hrt_admin d on b.uid=d.id
                          where a.checkid=$uid  and a.status !=0 and a.status!=4 and a.status !=5  and b.isdel=0 
                          and  b.applydt  like '$date%'  and b.flowid in  ($str_id) order by b.opdate desc  limit $limit" ;
//                    echo $f_sql;
                    $r=$this->db->query($f_sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                                 'publishtime'=>strtotime($v['applydt']),   	
                            );
                        }
                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }


                }else{
                    $check_sql='SELECT *from hrt_flows_test where  pid='.$typeId;
                    $c_r=$this->db->query($check_sql);
                    foreach($c_r as $c_v){
                        $flowsid[]=$c_v['id'];
                    }
                    $str_id=implode(',',$flowsid);
                    $f_sql="SELECT  b.id,b.flowname,b.fstatus,c.name,b.opdate,d.`name` as uid ,d.face,c.id as aid,b.applydt
                        from hrt_flows_result  a
                        left join  hrt_flows_bill b on a.fid=b.id
                        left join  hrt_admin  c  on  b.nowcheckuserid=c.id
                         left join hrt_admin d on b.uid=d.id
                          where a.checkid=$uid  and a.status !=0 and a.status!=4 and a.status !=5  and b.isdel=0 
                          and  b.applydt  like  '$date%'
                       and b.flowid in ($str_id)  and  b.uid=$memberId  order by b.opdate desc  limit $limit ";

                   $r=$this->db->query($f_sql);
                    if($r && mysqli_num_rows($r) >0){
                        foreach($r as $v){
                            $arr['flowlist'][]=array(
                                'id'=>$v['id'],
                                'flowname'=>$v['flowname'],
                                'publishuser'=>$v['uid'],
                                'publishheadicon'=>$v['face'],
                                'status'=>$v['fstatus'],
                                'audituser'=>$v['name'],
                                'audituserid'=>$v['aid'],
                                'updatetime'=>strtotime($v['opdate']),
                                 'publishtime'=>strtotime($v['applydt']),   	

                            );
                        }
                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'flowlist'=>null,
                        );
                    }

                }

            }


        }
         $this->returnjson($arr);
    }
// 3-12修改申请的流程单
    public function editFlowAction(){
            // $uid=$this->post('userId');
            // $applyflowid=$this->post('id');
            // $imglist=$this->post('imageList');
            // $sendtostep=$this->post('sendToStep');
            // $sendtouser=$this->post('sendToUser');
            // $fdata=$this->post('data');
            // $opdate=date('Y-m-d H:i:s',time());

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $applyflowid=$data['applyFlowId'];
           $imglist=json_encode($data['imageList'],true);
           $sendtostep=$data['sendToStep'];
           $sendtouser=$data['sendToUser'];
           $fdata=json_encode($data['data'],JSON_UNESCAPED_UNICODE);
           $opdate=date('Y-m-d H:i:s',time());

            $arr0=array(
                'imglist'=>$imglist,
                'nowcheckid'=>$sendtostep,
                'nowcheckuserid'=>$sendtouser,
                'nowstatus'=>0,
                'fstatus'=>0,
                'data'=>$fdata,
                'opdate'=>$opdate,
            );
            $f0=array(
                'id'=>$applyflowid,
            );
         
            $r0=$this->db->record('[Q]flows_bill',$arr0,$f0);
            // $row0=mysqli_fetch_array($r0);
            if($r0){
//                0.现将修改的状态写入到result表中
                $arr00=array(
                    'fid'=>$applyflowid,
                    // 默认stepid=1
                    'stepid'=>$sendtostep,
                    'checkid'=>$uid,
                    'status'=>3,
                    'opdate'=>$opdate,
                );
                $r00=$this->db->record('[Q]flows_result',$arr00);

//                1.先判断提交的步骤是否有跳过的步骤
                $sql1='SELECT *from hrt_flows_bill where id='.$applyflowid.' and isdel=0';
                $r1=$this->db->query($sql1);
                $row1=mysqli_fetch_array($r1);
//                echo $row1['allcheck']; 所有的审核步骤

                $allstepid=$row1['allcheck'];
                $now=','.$sendtostep;
                $nextstep=strstr($allstepid,$now,true);

//
        if($nextstep==null){
            //没有跳过的步骤
//         2.根据审核步骤找出对应的审核人
                if($sendtouser==null){
                    $sql_c='SELECT  *from hrt_flows_step where fid='.$row1['flowid'].' and id='.$sendtostep;
                    $r_c=$this->db->query($sql_c);
                    $rr=mysqli_fetch_array($r_c);
                    $sendtouser=$rr['auditor'];

                }else{
                    $sendtouser=$sendtouser;
                }
            $crr=array(
                'fid'=>$applyflowid,
                'stepid'=>$sendtostep,
                'checkid'=>$sendtouser,
                'status'=>0,
                'opdate'=>date('Y-m-d H:i:s',time()+1),
            );
            $cr=$this->db->record('[Q]flows_result',$crr);
             $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                );
                $this->returnjson($arr);
        }else{

//            有跳过审核,将跳过审核的记录在result中
                $cArr=explode(',',$nextstep);
                foreach($cArr as $cv){
                    //根据审核步骤找到相应的审核人
                    $sql_ck='SELECT  *from hrt_flows_step where fid='.$row1['flowid'].' and id='.$cv;
                    $r_ck=$this->db->query($sql_ck);
                    $rck=mysqli_fetch_array($r_ck);
//                    $rck['auditor'];审核人id
                    $crr_1=array(
                        'fid'=>$applyflowid,
                        'stepid'=>$cv,
                        'checkid'=>$rck['auditor'],
//                        4表示跳过
                        'status'=>4,
                        'opdate'=>date('Y-m-d H:i:s',time()-1),
                    );
                    $cr_1=$this->db->record('[Q]flows_result',$crr_1);
                }
                //审核人开始审核的状态加入
            if($sendtouser==null){
                $sql_c='SELECT  *from hrt_flows_step where fid='.$row1['flowid'].' and id='.$sendtostep;
                $r_c=$this->db->query($sql_c);
                $rr=mysqli_fetch_array($r_c);
                $sendtouser=$rr['auditor'];

                }else{
                    $sendtouser=$sendtouser;
                }
                $crr=array(
                    'fid'=>$applyflowid,
                    'stepid'=>$sendtostep,
                    'checkid'=>$sendtouser,
                    'status'=>0,
                    'opdate'=>date('Y-m-d H:i:s',time()+1),
                );
                $cr=$this->db->record('[Q]flows_result',$crr);
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

// 第三方平台推送消息
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

   /*
     * 友盟+消息推送
     */
    
    /*
     * android商户推送自定义播
     */
    public function android($uid,$ticker,$title,$text,$type) {
        require_once(''.ROOT_PATH.'/webmain/task/umeng/notification/Umeng.php');
        $config = array(
            'aliasType' => 'HRT',
            'alias'=> $uid,//用户ID
            'ticker' => $ticker,//通知栏提示文字
            'title' => $title,//标题
            'text' => $text,//内容
            'type'=>$type,
        );
        $umeng = new Umeng($config);
        $umeng->sendAndroidCustomizedcast();
    }
    /*
     * IOS商户推送自定义播
     */
    public function ios($uid,$alert,$type) {
        require_once(''.ROOT_PATH.'/webmain/task/umeng/notification/Umeng.php');
        $config = array(
            'aliasType' => 'HRT',
            'alias'=> $uid,
            'alert'=>$alert,
            'type'=>$type,
        );
        $umeng = new Umeng($config);
        $umeng->sendIOSCustomizedcast();
    
    }
    

//后台添加流程自定义模板的表单提交方法
    public function  formSubmitdataAction(){
            $id=$_POST['hid'];
            $nArr=array();
            for ($x=0; $x<count($_POST["name"]); $x++){
                array_push($nArr,
                    array("name"=>$_POST["name"][$x],
                        "type"=>$_POST["type"][$x],
                        "isrequired"=>$_POST["isrequired"][$x],
                    )
                );
            }

            foreach($nArr as $v){
                $arrs[]=$v;
            }
           $jsondata=json_encode($arrs,JSON_UNESCAPED_UNICODE);
        
            $arr0=array(

                'value'=>$jsondata,
            );
            $f0=array(
                'id'=>$id,
            );
            $r0=$this->db->record('[Q]flows_test',$arr0,$f0);

            if($r0){
                echo ' <script>alert("提交成功");history.go(-1);</script>';
            }else{
                echo ' <script>alert("提交失败");history.go(-1);</script>';
            }
    }

//后台添加流程步骤的方法
   public  function addstepAction(){
         $name=$_POST['name'];
         $fid=$_POST['fid'];
         $stepuser=$_POST['superman'];
         if(empty($_POST['auditor'])){
             $auditor=0;
         }else{
             $auditor=$_POST['auditor'];
         }
        $arr0=array(
            'name'=>$name,
            'fid'=>$fid,
            'auditor'=>$auditor,
            'stepuser'=>$stepuser,
        );
        $r0=$this->db->record('[Q]flows_step',$arr0);
       if($r0){
           echo ' <script>alert("提交成功");history.go(-1);</script>';
       }else{
           echo ' <script>alert("提交失败");history.go(-1);</script>';
       }
   }






}