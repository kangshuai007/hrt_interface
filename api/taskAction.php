<?php
//任务模块接口
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
class  taskClassAction extends apiAction{
//      4-1新建任务接口
       public function createTaskAction(){
          //远端测试使用
           // $userid=$this->post('userId');
           // $content=$this->post('content');
           // $imagelist=$this->post('imageList');
           // $userlist=json_decode($this->post('userList'),true);
           // $mainuserid=$this->post('mainUserId');
           // $enddate=$this->post('endDate');

          $data=json_decode($this->post('data'),true);
          $userid=$data['userId'];
          $content=$data['content'];
          $imagelist=json_encode($data['imageList'],true);
          $userlist=$data['userList'];
          $mainuserid=$data['mainUserId'];
          $enddate=$data['endDate'];
           $idArr=array();
           foreach($userlist as $v){
                 $idArr[]=$v;
                // 推送
                 $ticker=$text=$content;
                 $push=$this->android($v,$ticker,'任务',$text,3);
                
           }
           $ids=implode(',',$idArr);
           // 根据userid查询出用户名
           $sql='SELECT name from hrt_admin where id='.$userid;
           $r=$this->db->query($sql);
           $row=mysqli_fetch_array($r);
           // echo $row['name'];

            $arr0=array(
               'explain'=>$content,   //内容
                'optid'=>$userid,
                'optname'=>$row['name'],
                'startdt'=>date('Y-m-d H:i:s',time()),
                'enddt'=>$enddate,
                'imglist'=>$imagelist,
                 'header'=>$mainuserid,
                 'distid'=>$ids,
            );
           $result=$this->db->record('[Q]work',$arr0);
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
//      4-2设置完成规则 默认为1
        public function setTaskRuleAction(){
            //远端测试
            // $type=$this->post('type');

           $data=json_decode($this->post('data'),true);
           $type=$data['type'];
            $arr=array(
                'taskrule'=>$type,
            );
            $result=$this->db->record('[Q]workrule',$arr,"`id`=1");
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
//   4-3 获取当前的完成规则
          public function  getTaskRuleAction(){
//              $type=$this->post('type');

//              $data=json_decode($this->post('data'),true);
//              $type=$data['type'];
//              $arr=array(
//                  'taskrule'=>$type,
//              );
              $result=$this->db->getone('[Q]workrule',"`id`=1");

              if($result){
                  $arr=array(
                      'code'=>200,
                      'msg'=>'成功',
                        'type'=>$result['taskrule'],
                  );
                  $this->returnjson($arr);
              }else{
                  $this->showreturn('','失败',201);
              }

          }

//    4-4我发布的任务列表
      public  function  myTaskListAction()
      {
          // $uid = $this->post('userId');
          // $pageNo = $this->post('pageNo');
          // $pageSize = $this->post('pageSize');
          // $timeFilter = $this->post('timeFilter');
          // $memberId = $this->post('memberId');

         $data=json_decode($this->post('data'),true);
          $uid=$data['userId'];
         $pageNo=$data['pageNo'];
         $pageSize=$data['pageSize'];
         $timeFilter=$data['timeFilter'];
         $memberId=$data['memberId'];
          $limit = (($pageNo - 1) * $pageSize) . "," . $pageSize;
//          $sql="select *from hrt_work where optid=$uid";
//          $row=$this->db->query($sql);
//          $total=mysqli_num_rows($row);
          if ($memberId == null) {
              //显示我发布的任务列表全部列表
              if ($timeFilter == 0) {
                  //最新发布时间倒序
                  $arr = array(
                      'code' => 200,
                      'msg' => '成功',
                  );
                  $sql1 = "SELECT  a.id,b.face,a.`explain`as content ,count(c.pid) as childcount
                     ,a.isend as status ,a.enddt from hrt_work a
                     left join hrt_admin b on a.optid=b.id
                     left join hrt_workchild c on a.id=c.pid
                     where a.optid=$uid group by a.id order by a.startdt desc
                     limit $limit ";
                  $result1 = $this->db->query($sql1);
                  foreach ($result1 as $v1) {
                    if($v1['enddt']==null){
                         $arr['list'][] = array(
                              'id' => $v1['id'],
                              'status' => $v1['status'],
                              'headicon' => FACE.$v1['face'],
                              'content' => $v1['content'],
                              'childcount' => $v1['childcount'],
                              'enddate' => $v1['enddt'],
                          );
                    }else{
                          $arr['list'][] = array(
                              'id' => $v1['id'],
                              'status' => $v1['status'],
                              'headicon' => FACE.$v1['face'],
                              'content' => $v1['content'],
                              'childcount' => $v1['childcount'],
                              'enddate' => strtotime($v1['enddt']),
                          );
                    }
                  }
              } else if ($timeFilter == 1) {
                  //更新时间排序
                  $arr = array(
                      'code' => 200,
                      'msg' => '成功',
                  );
                  $sql2 = "SELECT a.id,b.face,a.`explain`as content ,count(c.pid) as childcount
                     ,a.isend as status ,a.enddt from hrt_work a
                     left join hrt_admin b on a.optid=b.id
                     left join hrt_workchild c on a.id=c.pid
                     where a.optid=$uid group by a.id order by a.optdt desc
                     limit $limit";
                  $result2 = $this->db->query($sql2);
                  foreach ($result2 as $v2) {
                      if($v2['enddt']==null){
                          $arr['list'][] = array(
                            'id' => $v2['id'],
                            'status' => $v2['status'],
                            'headicon' =>FACE.$v2['face'],
                            'content' => $v2['content'],
                            'childcount' => $v2['childcount'],
                            'enddate' => $v2['enddt'],
                             );
                      }else{
                           $arr['list'][] = array(
                          'id' => $v2['id'],
                          'status' => $v2['status'],
                          'headicon' =>FACE.$v2['face'],
                          'content' => $v2['content'],
                          'childcount' => $v2['childcount'],
                          'enddate' => strtotime($v2['enddt']),
                           );
                      }
                     
                  }

              } else {
                  //截止日期排序
                  $arr = array(
                      'code' => 200,
                      'msg' => '成功',
                  );
                  $sql3 = "SELECT a.id,b.face,a.`explain`as content ,count(c.pid) as childcount
                     ,a.isend as status ,a.enddt from hrt_work a
                     left join hrt_admin b on a.optid=b.id
                     left join hrt_workchild c on a.id=c.pid
                     where a.optid=$uid group by a.id order by a.enddt desc
                     limit $limit";
                  $result3 = $this->db->query($sql3);
                  foreach ($result3 as $v3) {
                     if($v3['enddt']==null){
                         $arr['list'][] = array(
                          'id' => $v3['id'],
                          'status' => $v3['status'],
                          'headicon' => FACE.$v3['face'],
                          'content' => $v3['content'],
                          'childcount' => $v3['childcount'],
                          'enddate' => $v3['enddt'],
                        );
                     }else{
                           $arr['list'][] = array(
                          'id' => $v3['id'],
                          'status' => $v3['status'],
                          'headicon' => FACE.$v3['face'],
                          'content' => $v3['content'],
                          'childcount' => $v3['childcount'],
                          'enddate' => strtotime($v3['enddt']),
                        );
                     }
                     
                  }
              }

              $this->returnjson($arr);
          } else {
              //显示我发布给的指定的成员的列表
              if ($timeFilter == 0) {
                  //最新发布时间倒序
                  $arr = array(
                      'code' => 200,
                      'msg' => '成功',
                  );
                  $sql1 = "SELECT a.id,b.face,a.`explain`as content ,count(c.pid) as childcount
                     ,a.isend as status ,a.enddt from hrt_work a
                     left join hrt_admin b on a.optid=b.id
                     left join hrt_workchild c on a.id=c.pid
                     where a.optid=$uid  and a.distid like '%$memberId%'
                     group by a.id order by a.startdt desc
                     limit $limit";
                  $result1 = $this->db->query($sql1);
                  foreach ($result1 as $v1) {
                      if($v1['enddt']==null){
                        $arr['list'][] = array(
                          'id' => $v1['id'],
                          'status' => $v1['status'],
                          'headicon' => FACE.$v1['face'],
                          'content' => $v1['content'],
                          'childcount' => $v1['childcount'],
                          'enddate' =>$v1['enddt'],
                         );
                      }else{
                         $arr['list'][] = array(
                          'id' => $v1['id'],
                          'status' => $v1['status'],
                          'headicon' => FACE.$v1['face'],
                          'content' => $v1['content'],
                          'childcount' => $v1['childcount'],
                          'enddate' => strtotime($v1['enddt']),
                          );
                      }
                    
                  }
              } else if ($timeFilter == 1) {
                  //更新时间排序
                  $arr = array(
                      'code' => 200,
                      'msg' => '成功',
                  );
                  $sql2 = "SELECT a.id,b.face,a.`explain`as content ,count(c.pid) as childcount
                     ,a.isend as status ,a.enddt from hrt_work a
                     left join hrt_admin b on a.optid=b.id
                     left join hrt_workchild c on a.id=c.pid
                     where a.optid=$uid and a.distid like '%$memberId%'
                     group by a.id order by a.optdt desc
                     limit $limit";
                  $result2 = $this->db->query($sql2);
                  foreach ($result2 as $v2) {
                       if($v2['enddt']==null){
                           $arr['list'][] = array(
                              'id' => $v2['id'],
                              'status' => $v2['status'],
                              'headicon' => FACE.$v2['face'],
                              'content' => $v2['content'],
                              'childcount' => $v2['childcount'],
                              'enddate' => $v2['enddt'],
                           );
                       }else{
                          $arr['list'][] = array(
                              'id' => $v2['id'],
                              'status' => $v2['status'],
                              'headicon' => FACE.$v2['face'],
                              'content' => $v2['content'],
                              'childcount' => $v2['childcount'],
                              'enddate' => strtotime($v2['enddt']),
                          );
                       }
                     
                  }

              } else {
                  //截止日期排序
                  $arr = array(
                      'code' => 200,
                      'msg' => '成功',
                  );
                  $sql3 = "SELECT a.id,b.face,a.`explain`as content ,count(c.pid) as childcount
                     ,a.isend as status ,a.enddt from hrt_work a
                     left join hrt_admin b on a.optid=b.id
                     left join hrt_workchild c on a.id=c.pid
                     where a.optid=$uid and a.distid like '%$memberId%'
                     group by a.id order by a.enddt desc
                     limit $limit";
                  $result3 = $this->db->query($sql3);
                  foreach ($result3 as $v3) {
                     if($v3['enddt']==null){
                        $arr['list'][] = array(
                            'id' => $v3['id'],
                            'status' => $v3['status'],
                            'headicon' => FACE.$v3['face'],
                            'content' => $v3['content'],
                            'childcount' => $v3['childcount'],
                            'enddate' => $v3['enddt'],
                          );
                     }else{
                            $arr['list'][] = array(
                              'id' => $v3['id'],
                              'status' => $v3['status'],
                              'headicon' => FACE.$v3['face'],
                              'content' => $v3['content'],
                              'childcount' => $v3['childcount'],
                              'enddate' => strtotime($v3['enddt']),
                          );
                     }
                    
                  }
              }

              $this->returnjson($arr);

          }
      }
//  4-5我收到的任务列表
        public function myreceivedTaskListAction(){
             //远端测试
             // $uid=$this->post('userId');
             // $pageNo=$this->post('pageNo');
             // $pageSize=$this->post('pageSize');

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $pageNo=$data['pageNo'];
           $pageSize=$data['pageSize'];


            $t_sql="SELECT *from hrt_work  where distid like '%$uid%' ";
            $t_result=$this->db->query($t_sql);
            $total=mysqli_num_rows($t_result);
            $eNum=ceil($total/$pageSize);
            $limit=(($pageNo-1)*$pageSize).','.$pageSize;


            $sql= "SELECT a.id,b.face,a.`explain`as content ,count(c.pid) as childcount
                     ,a.isend as status ,a.enddt from hrt_work a
                     left join hrt_admin b on a.optid=b.id
                     left join hrt_workchild c on a.id=c.pid
                     where a.distid like '%$uid%'
                     group by a.id order by a.startdt desc  limit $limit";
            $result=$this->db->query($sql);
            if($result){
                //计算出我收到的任务中未完成的数量
            $uf_sql="SELECT count(1) as count from hrt_work where distid like '%$uid%' and isend=0 ";
                $uf_r=$this->db->query($uf_sql);
                $uf_num=mysqli_fetch_array($uf_r);

                if($pageNo>=$eNum){
                     $arr=array(
                         'code'=>200,
                         'msg'=>'成功',
                         'isend'=>true,
                         'unfinishedcount'=>$uf_num['count'],
                     );
                 }else{
                     $arr=array(
                         'code'=>200,
                         'msg'=>'成功',
                         'isend'=>false,
                          'unfinishedcount'=>$uf_num['count'],
                     );
                 }
                foreach ($result as $v) {
                    if($v['enddt']==null){
                        $arr['list'][] = array(
                        'id' => $v['id'],
                        'status' => $v['status'],
                        'headicon' => FACE.$v['face'],
                        'content' => $v['content'],
                        'childcount' => $v['childcount'],
                        'enddate' => $v['enddt'],
                    ); 
                    }else{
                         $arr['list'][] = array(
                        'id' => $v['id'],
                        'status' => $v['status'],
                        'headicon' => FACE.$v['face'],
                        'content' => $v['content'],
                        'childcount' => $v['childcount'],
                        'enddate' => strtotime($v['enddt']),
                    );
                  }
                 
                }
                $this->returnjson($arr);
            }else{
               $this->showreturn('','失败',201);
            }
          }
//4-6任务详情
       public function   taskDetailAction(){
           //远端测试
           // $id=$this->post('id');


          $data=json_decode($this->post('data'),true);
          $id=$data['id'];
       $sql='SELECT a.optid as taskuserid,a.isend as status ,
            b.name as taskuser,b.face as taskheadiconurl,a.header as mainuserid ,
            c.name as mainusername ,a.explain as content,a.startdt as publishtime,
            a.imglist as imglist,a.enddt
            from  hrt_work a
            left join   hrt_admin  b  on  a.optid=b.id
            left JOIN hrt_admin c on a.header=c.id where a.id='.$id;

       $result=$this->db->query($sql);

       $row=mysqli_fetch_array($result);

       $rule_sql="select *from  hrt_workrule where id=1";
       $r_reslut=$this->db->query($rule_sql);
       $r_row=mysqli_fetch_array($r_reslut);
       // 输出图片 
             $iArr=json_decode($row['imglist'],true);

             foreach($iArr as $img){
              $imgArr[]=FACE.$img;  

             }

           if($result){
         foreach($result as $v){

            $arr=array(
                 'code'=>200,
                 'msg'=>'成功',
                 'ruletype'=>$r_row['taskrule'],
                  'taskuserid'=>$v['taskuserid'],
                  'status'=>$v['status'],
                  'taskuser'=>$v['taskuser'],
                  'taskheadiconurl'=>FACE.$v['taskheadiconurl'],
                  'mainuserid'=>$v['mainuserid'],
                  'mainusername'=>$v['mainusername'],
                  'content'=>$v['content'],
                  'publishtime'=>strtotime($v['publishtime']),
                  // 'endtime'=>strtotime($v['enddt']),
                  'imagelist'=>$imgArr,

            );
             if($v['enddt']==null){
                $arr['endtime']=$v['enddt'];  
              }else{
                 $arr['endtime']=strtotime($v['enddt']);  
              }
         }
       $sqls='select a.id,a.pid,a.state as status ,a.content ,
            a.mainid as mainuserid ,b.name ,a.enddt as enddate
            from hrt_workchild as a
            left join hrt_admin as b on a.mainid=b.id
            where pid='.$id;
           $results=$this->db->query($sqls);
           foreach($results as $vv){
                  if($vv['enddate']==null){
                        $arr['childtasklist'][]=array(
                           'id'=>$vv['id'],
                           'status'=>$vv['status'],
                           'content'=>$vv['content'],
                           'mainuserid'=>$vv['mainuserid'],
                           'mainuser'=>$vv['name'],
                           'enddate'=>$vv['enddate'],

                     );
                  }else{
                        $arr['childtasklist'][]=array(
                           'id'=>$vv['id'],
                           'status'=>$vv['status'],
                           'content'=>$vv['content'],
                           'mainuserid'=>$vv['mainuserid'],
                           'mainuser'=>$vv['name'],
                           'enddate'=>$vv['enddate'],

                     );
                  }
             
           }
           $sqlss='select  distid from hrt_work where id='.$id;
           $resultss=$this->db->query($sqlss);
           foreach ($resultss as $vvs){
                $receid=$vvs['distid'];
           }
           $idArr=array();
           $idArr=explode(',',$receid);

           foreach($idArr as $value){
                 $arrss=array(
                     'id'=>$value,
                 );

               $field0='`id`,`name`,`face`';
               $userlist=$this->db->getrows('[Q]admin',$arrss,$field0);
               foreach($userlist as $values){
                   $arr['userlist'][]=array(
                       'id'=>$values['id'],
                       'truename'=>$values['name'],
                       'headiconurl'=>FACE.$values['face'],
                   );
               }

           }
                 $this->returnjson($arr);
           }else{
                $this->showreturn('','失败',201);
           }

       }

//   4-7添加子任务接口
          public function createChildTaskAction(){
              //远端测试使用
              // $uid=$this->post('userId');
              // $id=$this->post('id');
              // $content=$this->post('content');
              // $mainuserid=$this->post('mainUserId');
              // $enddate=$this->post('endDate');

             $data=json_decode($this->post('data'),true);
             $uid=$data['userId'];
             $id=$data['id'];
             $content=$data['content'];
             $mainuserid=$data['mainUserId'];
             $enddate=$data['endDate'];
            $arr0=array(
                'pid'=>$id,
                'content'=>$content,
                'mainid'=>$mainuserid,
                'startdt'=>time(),
                'enddt'=>strtotime($enddate),

            );
              $result=$this->db->record('[Q]workchild',$arr0);
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
//     4-8完成任务
           public function completeTaskAction(){
               // $id=$this->post('id');

              $data=json_decode($this->post('data'),true);
              $uid=$data['userId'];
              $id=$data['id'];
              $arr0=array(
                  'isend'=>1,
              );
              $result=$this->db->record('[Q]work',$arr0,"`id`='$id'");
               if($result){
                    // 第三方推送
                // 查询出uid名字
                $sql0='SELECT *from hrt_admin where id='.$uid;
                $r0=$this->db->query($sql0);
                $row0=mysqli_fetch_array($r0);
                $name=$row0['name'];

                     // 查询出接收者
                  $sql='SELECT *from hrt_work where id='.$id;
                  $r=$this->db->query($sql);
                  $row=mysqli_fetch_array($r);
                  $distid=$row['distid'];

                  $to_u=explode(',',$distid);
                  foreach($to_u as $tv){
                     $to_users[]='user'.$tv;
                  }


                  $user_sende='admin';
                  $summary=$name.'标记任务为完成';
                  $content=$name.'标记任务为完成';
                  $rr=$this->businessnews($user_sende,$to_users,$type=3,$id,$summary,$content);


             $arr=array(
                       'code'=>200,
                       'msg'=>'成功'
                   );
                   $this->returnjson($arr);
               }else{
                   $this->showreturn('','失败',201);
               }
           }
// 4-9标记完成子任务
    public function completeChildTaskAction(){
                // $id=$this->post('id');

                      $data=json_decode($this->post('data'),true);
                      $id=$data['id'];
                $arr0=array(
                    'state'=>1,
                );
                $result=$this->db->record('[Q]workchild',$arr0,"`id`='$id'");
                if($result){
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功'
                    );
                    $this->returnjson($arr);
                }else{
                    $this->showreturn('','失败',201);
                }
            }
//4-10标记取消完成子任务
    public function cancelChildTaskAction(){
        // $id=$this->post('id');

                      $data=json_decode($this->post('data'),true);
                      $id=$data['id'];
        $arr0=array(
            'state'=>0,
        );
        $result=$this->db->record('[Q]workchild',$arr0,"`id`='$id'");
        if($result){
            $arr=array(
                'code'=>200,
                'msg'=>'成功'
            );
            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }
    }

//4-11发布该任务下的评论
     public function publishTaskCommentAction(){
           // $id=$this->post('id');
           // $uid=$this->post('userId');
           // $content=$this->post('content');

        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $id=$data['id'];
        $content=$data['content'];
         $arr0=array(
            'pid'=>$id,
            'uid'=>$uid,
            'content'=>$content,
             'cdate'=>time(),

         );
         $result=$this->db->record('[Q]workcomment',$arr0);
         if($result){
             $arr=array(
                 'code'=>200,
                 'msg'=>'成功'
             );
             $this->returnjson($arr);
         }else{
             $this->showreturn('','失败',201);
         }
   }

// 4-12 任务下评论列表
      public function  taskCommentListAction(){
         //远端测试
            // $id=$this->post('id');
            // $pageNo=$this->post('pageNo');
            // $pageSize=$this->post('pageSize');


         $data=json_decode($this->post('data'),true);
         $id=$data['id'];
           $pageNo=$data['pageNo'];
           $pageSize=$data['pageSize'];

          $limit=(($pageNo-1)*$pageSize).",".$pageSize;
          $arr=array();

          $sql="select id,uid,content,cdate from hrt_workcomment where pid=$id  order by cdate desc limit $limit ";
          $result=$this->db->query($sql);
          $num=mysqli_num_rows($result);

          if($num=$pageSize){
             $arr=array(
                 'code'=>200,
                 'msg'=>'成功',
                 'isend'=>false,
             );
          }else{
             $arr=array(
                 'code'=>200,
                 'msg'=>'成功',
                 'isend'=>true,
             );
          }
          if($result){

              foreach($result as $v){
                  $arr1=array(
                      'id'=>$v['uid'],
                  );
                  $fields='`name`,`face`';
                  $results=$this->db->getrows('[Q]admin',$arr1,$fields);

                  foreach($results  as $vv){

                      $arr['list'][]=array(
                          'id'=>$v['id'],
                          'evaluateuserid'=>$v['uid'],
                          'evaluateheadicon'=>FACE.$vv['face'],
                          'evaluateuser'=>$vv['name'],
                          'content'=>$v['content'],
                          'updatetime'=>$v['cdate'],
                      );

                  }
              }
              $this->returnjson($arr);
          }else{
              $this->showreturn('','失败',201);
          }
      }
// 4-14标记取消完成主任务
    public function cancelTaskAction(){
        // $id=$this->post('id');

              $data=json_decode($this->post('data'),true);
              $id=$data['id'];
              $uid=$data['userId'];
        $arr0=array(
            'isend'=>0,
        );
        $result=$this->db->record('[Q]work',$arr0,"`id`='$id'");
        if($result){
               // 第三方推送
                // 查询出uid名字
                $sql0='SELECT *from hrt_admin where id='.$uid;
                $r0=$this->db->query($sql0);
                $row0=mysqli_fetch_array($r0);
                $name=$row0['name'];

             $sql='SELECT *from hrt_work where id='.$id;
                  $r=$this->db->query($sql);
                  $row=mysqli_fetch_array($r);
                  $distid=$row['distid'];

                  $to_u=explode(',',$distid);
                  foreach($to_u as $tv){
                     $to_users[]='user'.$tv;
                  }


                  $user_sende='admin';
                  $summary=$name.'标记任务为未完成';
                  $content=$name.'标记任务为未完成';
                  $rr=$this->businessnews($user_sende,$to_users,$type=3,$id,$summary,$content);
            $arr=array(
                'code'=>200,
                'msg'=>'成功'
            );
            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }
    }
//第三方平台方法
    //发送业务自定义消息
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
    













}