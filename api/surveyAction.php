<?php
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
//民意调查模块
class surveyClassAction extends  apiAction{
//  10-1有权限发布调查的成员列表
    public function getmemberListAction(){
        $arr0=array(
            'issurvey'=>1,
        );
        $field0='`id`,`face`,`name`';
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

//    10-2添加有权限的成员
    public function addMemberAction(){
        // $isall=$this->post('isAllMember');
        // $userlist=json_decode($this->post('userList'),true);

       $data=json_decode($this->post('data'),true);
       $isall=$data['isAllMember'];
       $userlist=$data['userList'];
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $issurvey=1;
        if($isall==1){

            $result=$this->db->record('[Q]admin',"`issurvey`='".$issurvey."'","`issurvey`=0");
            if($result){
               // 第三方平台推送
                $user_sende='admin';

                $p_sql='SELECT *from hrt_admin';
                $p_r=$this->db->query($p_sql);
                foreach ($p_r as $key => $value) {
                    $to_users[]='user'.$value['id'];
                }

                $summary='您已获得发布民意调查权限';
                $content='您已获得发布民意调查权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=8,1,$summary,$content);



                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
        }else{
            foreach($userlist as $val){
                $arr0=array(
                    'id'=>$val,
                );
                $results=$this->db->record('[Q]admin',"`issurvey`='".$issurvey."'","`id`='".$arr0['id']."'");

                 $to_users[]='user'.$val;
            }

                 // 第三方平台推送
                $user_sende='admin';

                $summary='您已获得发布民意调查权限';
                $content='您已获得发布民意调查权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=8,1,$summary,$content);

            $this->returnjson($arr);
        }
    }
//    10-3.移除有权限的成员
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
        $issurvey=0;
        $result=$this->db->record('[Q]admin',"`issurvey`='".$issurvey."'","`id`='".$removedUserId."'");
        if($result){

               // 第三方平台推送
            $user_sende='admin';
            $to_users='user'.$removedUserId;

            $summary='您的发布民意调查权限已被移除';
            $content='您的发布民意调查权限已被移除';

            $push=$this->custmsgpush($user_sende,$to_users,$type=8,0,$summary,$content);


            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }
    }
 //10-4.发布新的民意调查
    public function publishSurveyAction(){

        //远端测试用
        // $userid=$this->post('userId');
        // $title=$this->post('title');
        // $isall=$this->post('isSendToAllUser');
        // $send=json_decode($this->post('sendToUserList'),true);
        // $enddate=$this->post('endDate');
        // $surveylist=$this->post('surveyList');

           $data=json_decode($this->post('data'),true);
           $userid=$data['userId'];
           $title=$data['title'];
           $isall=$data['isSendToAllUser'];
           $send=$data['sendToUserList'];
           $enddate=$data['endDate'];
           $surveylist=$data['surveyList'];

            $arr=array();
            $arr0=array(
            'title'=>$title,
            'pid'=>$userid,
            'enddate'=>strtotime($enddate),
            'date'=>time(),
        );

        $s_result=$this->db->record('[Q]survey',$arr0);

        //返回新插入问卷调查的id
      
        $num=$this->db->insert_id();

        $s_data=$surveylist;
        foreach($s_data as $v){
            $arr1=array(
                'pid'=>$num,
                'title'=>$v['name'],
                'type'=>$v['type'],
                'choices'=>implode('|||',$v['choices']),
            );

            $d_result=$this->db->record('[Q]surveydata',$arr1);
        }
        if($isall==1){
            //选择全部成员
            $allmember=$this->db->getrows('[Q]admin','','`id`');
            foreach($allmember as $val){
                $arr1=array(
                    'sid'=>$num,
                    'uid'=>$userid,
                    'rid'=>$val['id'],
                );
                $result2=$this->db->record('[Q]surveyrefer',$arr1);
                   //推送
                $ticker=$text=$title;
                $push=$this->android($val['id'],$ticker,'民意调查',$text,9);
            }
            if($result2){

                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'id'=>$num,
                );
                $this->returnjson($arr);
            }
        }else{
            //如果不是全部成员,根据情况做出判断
            foreach($send as $v){
                $arrlist=array(
                    'type'=>$v['type'],
                    'id'=>$v['id'],
                );
                //根据传过来的type判断是否为整个部门
                if($arrlist['type']==1){//表示为单个成员
                    $arr2=array(
                        'sid'=>$num,
                        'uid'=>$userid,
                        'rid'=>$arrlist['id'],
                    );
                    $result00=$this->db->record('[Q]surveyrefer',$arr2);
                }else{
                    //类型为整个部门
                    $arr3=array(
                        'pid'=>$arrlist['id'],

                    );
                    //查询部门下的子部门
                    $row=$this->db->getrows('[Q]dept',$arr3,'`id`');
//                      print_r($row);
                    foreach($row as $vv){
                        $arr4=array(
                            'deptid'=>$vv['id'],
                        );
                        $member=$this->db->getrows('[Q]admin',$arr4,'`id`');
//                          print_r($member);
                        foreach($member as $vvv){
                            $arr4=array(
                                'sid'=>$num,
                                'uid'=>$userid,
                                'rid'=>$vvv['id'],
                            );
                            $r00=$this->db->record('[Q]surveyrefer',$arr4);
                        }
                    }
                    //不是子部门的所有成员
                    $arr5=array(
                        'deptid'=>$arrlist['id'],
                    );
                    $d=$this->db->getrows('[Q]admin',$arr5,'`id`');
                    foreach($d as $v1){
                        $arr6=array(
                            'sid'=>$num,
                            'uid'=>$userid,
                            'rid'=>$v1['id'],
                        );

                        $r11=$this->db->record('[Q]surveyrefer',$arr6);
                    }
                }
            }
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'id'=>$num,
            );
            $this->returnjson($arr);
        }
    }
//  10-5获取民意调查信息
         public function getSurveyDataAction(){
             //测试
              // $uid=$this->post('userId');
              // $id=$this->post('id');

            $data=json_decode($this->post('data'),true);
            $id=$data['id'];
            $uid=$data['userId'];



             //判断接收人里是否存在
             $sql_1="SELECT a.status,a.uid,b.title from hrt_surveyrefer  a
              left join hrt_survey b  on a.sid=b.id
             where a.sid=$id and a.rid=$uid";
             $r_1=$this->db->query($sql_1);
             $r=mysqli_fetch_array($r_1);
             $num=mysqli_num_rows($r_1);
             $arr=array();
             if($num>0){
                 //有提交权限
                   $arr=array(
                       'code'=>200,
                       'msg'=>'成功',
                       'haspermission'=>1,
                       'status'=>$r['status'],
                       'publishuserid'=>$r['uid'],
                       'title'=>$r['title'],
                   );

             }else{
                // 查询出发布此条信息的发布者
               $c_sql='SELECT pid,title from  hrt_survey where id='.$id;
               $c_r=$this->db->query($c_sql);
               $c_row=mysqli_fetch_array($c_r);
                $arr=array(
                     'code'=>200,
                     'msg'=>'失败',
                     'haspermission'=>0,
                     'status'=>0,
                     'publishuserid'=>$c_row['pid'],
                     'title'=>$c_row['title'],
                );
             }

             //选出客户的答案进行比对
             $answer="select  content  from hrt_surveyrefer where sid=$id and rid=$uid";
             $r_answer=$this->db->query($answer);
             $r_row=mysqli_fetch_array($r_answer);

             $aArr=json_decode($r_row['content'],true);
             foreach($aArr as $va){
                foreach($va['choices'] as $vc){
                    $aaArr[]=$vc['item'];
                }
             }

             $sql='select *from hrt_surveydata  where pid='.$id;
             $result=$this->db->query($sql);
             foreach($result as $v) {
                 $cArr = explode('|||', $v['choices']);
                 $srr=array();

                 foreach($cArr as $vv){

                     if(in_array($vv,$aaArr)){ // 答案进行比对
                         $select=true;
                     }else{
                         $select=false;
                     }
                     $srr[]=array(
                        'item'=>$vv,
                         'isselected'=>$select,
                     );

                 }

                 $arr['list'][] = array(
                     'id' => $v['id'],
                     'name' => $v['title'],
                     'type' => $v['type'],
                     'choices'=>$srr,
                 );

             }

             $this->returnjson($arr);

         }
//  10-6 民意调查结果提交
       public  function submitSurveyResultAction(){
             // $id=$this->post('id');
             // $uid=$this->post('userId');
             // $value=$this->post('valueList');

           $data=json_decode($this->post('data'),true);
           $id=$data['id'];
           $uid=$data['userId'];
           $value=$data['valueList'];
            $arr0=array(
                'status'=>1,
                'content'=>json_encode($value,JSON_UNESCAPED_UNICODE),
                'date'=>time(),
            );
            $field=array(
                'sid'=>$id,
                'rid'=>$uid,
            );
           $result=$this->db->record('[Q]surveyrefer',$arr0,$field);
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

//  10-7我发布的调查列表
       public function mypublishSurveyListAction(){
           // $uid=$this->post('userId');
           // $pageNo=$this->post('pageNo');
           // $pageSize=$this->post('pageSize');

          $data=json_decode($this->post('data'),true);
          $uid=$data['userId'];
          $pageNo=$data['pageNo'];
          $pageSize=$data['pageSize'];
           $arr=array();
           $t_sql='select *from hrt_survey  where pid='.$uid;
           $t_result=$this->db->query($t_sql);
           $total=mysqli_num_rows($t_result);
           $eNum=ceil($total/$pageSize);
           $limit=(($pageNo-1)*$pageSize).",".$pageSize;

          $sql_1='select a.id,b.face,a.date ,a.title ,a.enddate,b.name
                  from hrt_survey a
                    left join  hrt_admin b  on a.pid=b.id
                    where a.pid='.$uid.' order by a.date desc limit '.$limit;

          $r_1=$this->db->query($sql_1);
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
           foreach($r_1 as $v_1){
               //计算子调查项的数量
               $n_sql='select count(1) as count from hrt_surveydata where pid='.$v_1['id'];

               $n_r=$this->db->query($n_sql);
               foreach($n_r as $vv){
                  $c_num=$vv['count'];
               }
             //看是否有提交权限
               $q_sql='select  count(1) as count from hrt_surveyrefer where sid='.$v_1['id'].' and rid= '.$uid;

               $q_r=$this->db->query($q_sql);
               foreach($q_r as $qv){
                   $h_num=$qv['count'];
               }
            //多少人已经提交
               $s1_sql='select count(1) as count from hrt_surveyrefer where sid='.$v_1['id'].' and `status`=1';
               $s_r=$this->db->query($s1_sql);

               foreach($s_r as $sv){
                   $s_num=$sv['count'];
               }
             //总共那个多少人需要提交
               $t1_sql='select ifnull(sum(1),0)count  from hrt_surveyrefer where sid='.$v_1['id'];
               $t_r=$this->db->query($t1_sql);

               foreach($t_r as $tv){
                   $t_num=$tv['count'];
               }
               //是否提交
               $ss_sql='select ifnull(sum(1),0)count ,ifnull(status,0) as status from hrt_surveyrefer where sid='.$v_1['id'].' and rid='.$uid;
               $ss_r=$this->db->query($ss_sql);

               foreach($ss_r as $ssv){
                   $status=$ssv['status'];
               }


             //生成 list列表
               $arr['list'][]=array(
                   'id'=>$v_1['id'],
                   'title'=>$v_1['title'],
                   'headiconurl'=>FACE.$v_1['face'],
                    'truename'=>$v_1['name'],
                   'publishtime'=>$v_1['date'],
                   'endtime'=>$v_1['enddate'],
                   'childcount'=>$c_num,
                   'haspermission'=>$h_num,
                   'finishednum'=>$s_num,
                   'totalnum'=>$t_num,
                   'status'=>$status,

               );
   }
            $this->returnjson($arr);
}

// 10-8  我收到的调查列表
      public function myreceivedSurveyListAction(){
           // $uid=$this->post('userId');
           // $pageNo=$this->post('pageNo');
           // $pageSize=$this->post('pageSize');


         $data=json_decode($this->post('data'),true);
         $uid=$data['userId'];
         $pageNo=$data['pageNo'];
         $pageSize=$data['pageSize'];
         
          $sql_1='select *from hrt_surveyrefer  where rid='.$uid.' order by id desc ';
            
          $r_1=$this->db->query($sql_1);
          if($r_1){
              $total=mysqli_num_rows($r_1);
              $eNum=ceil($total/$pageSize);
              $limit=(($pageNo-1)*$pageSize).",".$pageSize;

              //计算我收到的调查列表中还没有完成的调查数量
              $unf_sql="SELECT count(1) as count  FROM  `hrt_surveyrefer`  a
                        left join  `hrt_survey`  b  on a.sid=b.id
                        where  rid=$uid  and a.`status` =0";   
              $unf_r=$this->db->query($unf_sql);
              $unf_n=mysqli_fetch_array($unf_r);
              // echo $unf_n['count'];

              $arr=array();
              if($pageNo>=$eNum){
                  $arr=array(
                      'code'=>200,
                      'msg'=>'成功',
                      'isend'=>true,
                      'unfinishedcount'=>$unf_n['count'],
                  );
              }else{
                  $arr=array(
                      'code'=>200,
                      'msg'=>'成功',
                      'isend'=>false,
                      'unfinishedcount'=>$unf_n['count'],
                  );
              }
              foreach($r_1  as $v_1){
                  $sql_2='select a.id,b.face,a.title,a.date,a.enddate,b.name
                from hrt_survey   a
                left join  hrt_admin   b  on  a.pid=b.id
                where a.id='.$v_1['sid'].' order by a.date desc limit '.$limit;
                // echo $sql_2;
                  $r_2=$this->db->query($sql_2);
                  foreach($r_2 as $v_2){

                      //计算子调查项的数量
                      $n_sql='select count(1) as count from hrt_surveydata where pid='.$v_2['id'];

                      $n_r=$this->db->query($n_sql);
                      foreach($n_r as $vv){
                          $c_num=$vv['count'];
                      }
                      //看是否有提交权限
                      $q_sql='select  count(1) as count from hrt_surveyrefer where sid='.$v_2['id'].' and rid= '.$uid;

                      $q_r=$this->db->query($q_sql);
                      foreach($q_r as $qv){
                          $h_num=$qv['count'];
                      }
                      //多少人已经提交
                      $s1_sql='select count(1) as count from hrt_surveyrefer where sid='.$v_2['id'].' and `status`=1';
                      $s_r=$this->db->query($s1_sql);

                      foreach($s_r as $sv){
                          $s_num=$sv['count'];
                      }
                      //总共那个多少人需要提交
                      $t1_sql='select ifnull(sum(1),0)count  from hrt_surveyrefer where sid='.$v_2['id'];
                      $t_r=$this->db->query($t1_sql);

                      foreach($t_r as $tv){
                          $t_num=$tv['count'];
                      }
                      //是否提交
                      $ss_sql='select ifnull(sum(1),0)count ,ifnull(status,0) as status from hrt_surveyrefer where sid='.$v_2['id'].' and rid='.$uid;
                      $ss_r=$this->db->query($ss_sql);

                      foreach($ss_r as $ssv){
                          $status=$ssv['status'];
                      }

                      $arr['list'][]=array(
                          'id'=>$v_2['id'],
                          'headiconurl'=>FACE.$v_2['face'],
                           'truename'=>$v_2['name'],
                          'title'=>$v_2['title'],
                          'publishtime'=>$v_2['date'],
                          'endtime'=>$v_2['enddate'],
                          'childcount'=>$c_num,
                          'haspermission'=>$h_num,
                          'finishednum'=>$s_num,
                          'totalnum'=>$t_num,
                          'status'=>$status,
                      );
                  }
              }
              $this->returnjson($arr);
          }else{

             $this->showreturn('','失败',201);
          }

      }
      //10-9该调查的结果统计
      public function surveyCountDataAction(){
             // $id=$this->post('id');
             // $uid=$this->post('userId');


             $data=json_decode($this->post('data'),true);
             $uid=$data['userId'];
             $id=$data['id'];
          //计算调查已提交人数
          $sub_sql='select count(1) from hrt_surveyrefer where sid='.$id.' and status=1';
          $r_1=$this->db->query($sub_sql);

          $num=mysqli_fetch_array($r_1);
          //未提交人数
          $unsub_sql='select count(1) from hrt_surveyrefer where sid='.$id.' and status=0';
          $unr_1=$this->db->query($unsub_sql);

          $unnum=mysqli_fetch_array($unr_1);
          $arr=array(
             'code'=>200,
             'msg'=>'成功',
             'submitnum'=>$num[0],
             'unsubmitnum'=>$unnum[0],

          );
          //选出选项中人员提交的答案进行比对
          $answer='select  content  from hrt_surveyrefer where sid='.$id;
          $r_answer=$this->db->query($answer);

              foreach($r_answer as $aa){

                   $aArr[]=json_decode($aa['content'],true);

              }

            $fArr=array_filter($aArr);


            foreach($fArr as $va) {

                foreach($va as $vva){

                        foreach($vva['choices'] as $vas){
                            $ss[]=$vas['item'];   //将最后结果的字符串赋值给一个数组中
                        }
                }
            }

          $answer=implode(',',$ss);



          //选出列表里面的题目和选项
          $sql_1='select *from hrt_surveydata where pid='.$id;
          $result=$this->db->query($sql_1);

          foreach($result as $v){
              $cArr = explode('|||', $v['choices']);
              $sArr=array();

              foreach($cArr as $vv){
//                   计算$vv值在$ss中出现了几次,就是选择该选项的人数
                    $count=0;
                     foreach($ss as $cc){
                            if($vv==$cc){
                                $count=$count+1;
                            }
                     }
//                  $count=substr_count($answer,$vv);

                   $sArr[]=array(
                       'item'=>$vv,
                       'selectednum'=>$count,
                   );
              }
//              print_r($sArr);
              $arr['list'][]=array(
                  'id'=>$v['id'],
                  'name'=>$v['title'],
                  'choices'=>$sArr,
              );
          }

         $this->returnjson($arr);

      }


     /*---------------------第三方平台推送消息-----------------------------------*/

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



}