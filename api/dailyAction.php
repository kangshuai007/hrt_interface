<?php
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
//工作汇报模块
class dailyClassAction extends apiAction{
           //5-1允许查看工作汇报的人员列表
          public function memberRuleListAction(){
              // $userid=$this->post('userId');


             $data=json_decode($this->post('data'),true);
             $userid=$data['userId'];

              $arr=array(
                  'code'=>200,
                  ' msg'=>'成功',
              );
              $sql='SELECT b.id,b.name,b.face,a.isall,a.rangelist from hrt_dailyrule a
                    left join hrt_admin b
                    on  a.uid=b.id';

              $result=$this->db->query($sql);
//              $rArr=array();
              if($result){
                  foreach($result as $row){


                   if($row['isall']==1){
                          $arr['userlist'][]=array(
                           'id'=>$row['id'],
                           'headiconurl'=>FACE.$row['face'],
                           'truename'=>$row['name'],
                           'isall'=>$row['isall'],

                       );

                  }else{
                       $rArr=array();
                       $mArr=json_decode($row['rangelist'],true);

                       foreach($mArr as $m){

                           $arr1=array(
                               'type'=>$m['type'],
                               'id'=>$m['id'],

                           );

                           if($arr1['type']==1){
                               $sql_1='select name,face from hrt_admin where id='.$arr1['id'];
                               $member=$this->db->query($sql_1);
                               foreach($member as $mm){
                                   $rArr[]=array(

                                       'type'=>$arr1['type'],
                                       'name'=>$mm['name'],
                                       'headicon'=>FACE.$mm['face'],
                                       'id'=>$arr1['id'],

                                   );

                               }
                           }else{
                               $sql_2='select name from hrt_dept where id='.$arr1['id'];
                               $dept=$this->db->query($sql_2);
                               foreach($dept as  $dd){
                                   $rArr[]=array(

                                       'type'=>$arr1['type'],
                                       'name'=>$dd['name'],
                                       'id'=>$arr1['id'],

                                   );
                               }

                           }
                       }
                       $arr['userlist'][]=array(
                           'id'=>$row['id'],
                           'headiconurl'=>FACE.$row['face'],
                           'truename'=>$row['name'],
                           'isall'=>$row['isall'],
                           'rangelist'=>$rArr,

                       );
                   }
                  }

                  $this->returnjson($arr);
              }else{
                  $this->showreturn('','失败',201);
              }

          }



//       5-2 添加一个有查看工作汇报权限的人
        public function addMemberRuleAction(){
            //远端测试使用
               // $userid=$this->post('userId');
               // $isall=$this->post('isAllMember');
               // $userlist=json_decode($this->post('userList'),true);

           $data=json_decode($this->post('data'),true);
           $userid=$data['userId'];
           $isall=$data['isAllMember'];
           $userlist=$data['userList'];
             if($isall==1){
                  $all_admin=$this->db->getrows('[Q]admin','',"`id`");
                  foreach($all_admin as $v){
                      $arr0=array(
                          'uid'=>$v['id'],
                          'isall'=>1,
                          'date'=>time(),
                          'rangelist'=>0,
                      );
                      $result=$this->db->record('[Q]dailyrule',$arr0);
                  }
                 if($result){

                       // 第三方平台推送
                $user_sende='admin';

                $p_sql='SELECT *from hrt_admin';
                $p_r=$this->db->query($p_sql);
                foreach ($p_r as $key => $value) {
                    $to_users[]='user'.$value['id'];
                }

                $summary='您已获得查看工作汇报权限';
                $content='您已获得查看工作汇报权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=5,1,$summary,$content);

                     $arr=array(
                         'code'=>200,
                         'msg'=>'成功',
                     );
                     $this->returnjson($arr);
                 }else{
                     $this->showreturn('','失败',201);
                 }

             }else{

                 //不是全部  isall==0
                 foreach($userlist as $vv){
                      $arr1=array(
                          'uid'=>$vv,
                          'isall'=>1,
                          'date'=>time(),
                          'rangelist'=>0,
                      );


                     $sql='SELECT *from hrt_dailyrule where uid='.$vv;

                     $if_no=$this->db->query($sql);
                     $num=mysqli_num_rows($if_no);

                     if($num>0){
                         $this->showreturn('','失败',201);
                     }else{
                         $results=$this->db->record('[Q]dailyrule',$arr1);
                     }
                     $to_users[]='user'.$vv;

                 }
                 if($results){


                 // 第三方平台推送
                $user_sende='admin';

                $summary='您已获得查看工作汇报权限';
                $content='您已获得查看工作汇报权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=5,1,$summary,$content);

                     $arr=array(
                         'code'=>200,
                         'msg'=>'成功',
                     );
                     $this->returnjson($arr);
                 }else{

                     $this->showreturn('','失败',201);
                 }

             }


        }
//    5-3移除一个有查看工作汇报权限的人
         public function deleteMemberRuleAction(){
             //远端测试
             // $ruleid=$this->post('hasPermissionUserId');

            $data=json_decode($this->post('data'),true);
            $ruleid=$data['hasPermissionUserId'];

             $sql='delete from hrt_dailyrule where uid='.$ruleid;
             $result=$this->db->query($sql);
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


//     5-4修改一个用户的查看权限范围
           public function  setMemberRuleAction(){
               // $ruleid=$this->post('hasPermissionUserId');
               // $isall=$this->post('isAll');
               // $rangelist=$this->post('rangeList');

              $data=json_decode($this->post('data'),true);
              $ruleid=$data['hasPermissionUserId'];
              $isall=$data['isAll'];
              $rangelist=$data['rangeList'];

              $arr0=array(
                 'isall'=>$isall,
                 'rangelist'=>json_encode($rangelist,true),
              );
               $result=$this->db->record('[Q]dailyrule',$arr0,"`uid`='$ruleid'");
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

//      5-6  新建模板
        public function createDailyModelAction(){
             // $uid=$this->post('userId');
             // $type=$this->post('type');
             // $isall=$this->post('isAllDepartment');
             // $deptlist=$this->post('departmentList');
             // $field=$this->post('fieldList');

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $type=$data['type'];
           $isall=$data['isAllDepartment'];
           $deptlist=json_encode($data['departmentList'],JSON_UNESCAPED_UNICODE);
           $field=json_encode($data['fieldList'],JSON_UNESCAPED_UNICODE);

           $arr0=array(
               'uid'=>$uid,
               'type'=>$type,
               'isall'=>$isall,
               'deptlist'=>$deptlist,
               'field'=>$field,
               'date'=>time(),
           );
            $result=$this->db->record('[Q]dailymodel',$arr0);
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

//    5-7我创建的模版列表
      public function myCreateModelListAction(){
          // $uid=$this->post('userId');
          // $type=$this->post('type');

         $data=json_decode($this->post('data'),true);
         $uid=$data['userId'];
         $type=$data['type'];


        $sql="SELECT * from hrt_dailymodel  where   type=$type";

        $result=$this->db->query($sql);
         $num=mysqli_num_rows($result); //判断我是否创建了模版
         
          if($result && $num>0){
                $arr=array(
                  'code'=>200,
                  'msg'=>'成功',
                  

              );
              foreach($result as $v){

                  if($v['isall']==1){
                      //是否为全部部门使用
                      $fArr=array();
                      $fieldlist=json_decode($v['field'],true);
                      $arr['list'][]=array(
                          'id'=>$v['id'],
                          'isall'=>$v['isall'],
                          'fieldlist'=>$fieldlist,
                      );

                  }else{
                      //不是为全部部门
                      $dptlist=json_decode($v['deptlist'],true);

                      $dptArr=array();
                      foreach($dptlist as $vv){
                          $arr1=array(
                              'id'=>$vv,
                          );
                          $dptsql='select  name from hrt_dept where id='.$vv;

                          $dptname=$this->db->query($dptsql);
                          foreach($dptname as $vvv){
                              $dptArr[]=array(
                                  'id'=>$vv,
                                  'name'=>$vvv['name'],

                              );
                          }
                      }
                      $fieldlist=json_decode($v['field'],true);
                      $arr['list'][]=array(
                          'id'=>$v['id'],
                          'isall'=>$v['isall'],
                          'departmentlist'=>$dptArr,
                          'fieldlist'=>$fieldlist,
                      );
                  }
              }
              if($v['isall']==1){
                  $dt_sql="select *from hrt_dept where pid =1";
                  $dt_r=$this->db->query($dt_sql);
                  foreach($dt_r as $vv){
                      $arr['departmentlist'][]=array(
                          'id'=>$vv['id'],
                      );
                  }
              }else{

                   foreach($result as $v){
//                       print_r($v);
                       $dptlist=json_decode($v['deptlist'],true);


                       foreach($dptlist as $vs){
//                          判断是否为第一层
                           $sql00='SELECT pid from hrt_dept where id='.$vs;
                           $r00=$this->db->query($sql00);
                           $row=mysqli_fetch_array($r00);
                          if($row['pid']==1){
                              $dpidArr[]=array(
                                  'id'=>$vs,
                              );
                          }else{
                              $this->tmparr = array();
                              $dpidArr[]=$this->test2($vs);

//                             print_r($this->test2(9));
                          }
                       }
                   }
                  foreach($dpidArr as $k=>$nn){
                      $arr['departmentlist'][]=$nn;

                  }
              }

              $this->returnjson($arr);
          }else{
              $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'departmentlist'=>null,
                'list'=>null,
                );
              $this->returnjson($arr);
          }

      }
      //    5-8  修改模版
     public function  editDailyModelAction(){
            // $id=$this->post('id');
            // $uid=$this->post('userId');
            // $isall=$this->post('isAllDepartment');
            // $deptlist=$this->post('departmentList');
            // $fieldlist=$this->post('fieldList');


          $data=json_decode($this->post('data'),true);
          $id=$data['id'];
          $uid=$data['userId'];
          $isall=$data['isAllDepartment'];
          $deptlist=json_encode($data['departmentList'],JSON_UNESCAPED_UNICODE);
          $field=json_encode($data['fieldList'],JSON_UNESCAPED_UNICODE);
        
         
           $arr0=array(
               'isall'=>$isall,
               'deptlist'=>$deptlist,
               'field'=>$field,
           );
           $result=$this->db->record('[Q]dailymodel',$arr0,"`id`='$id'");
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



//    5-9删除工作汇报模版
      public  function deleteDailyAction(){

           // $id=$this->post('id');

         $data=json_decode($this->post('data'),true);
         $id=$data['id'];

          $arr0=array(
              'id'=>$id,
          );
          $result=$this->db->delete('[Q]dailymodel',$arr0);
          if($result){
              $arr=array(
                  'code'=>200,
                  ' msg'=>'成功',
              );
              $this->returnjson($arr);
          }else{
              $arr=array(
                  'code'=>201,
                  'msg'=>'失败',
              );
              $this->showreturn('','失败',201);
          }
    }

//  10初始化写汇报需要准备的数据
       public  function  prepareDataAction(){
         // 远端测试
             // $uid=$this->post('userId');
             // $type=$this->post('type');
       	
         $data=json_decode($this->post('data'),true);
         $uid=$data['userId'];
         $type=$data['type'];

           //查询出来的部门id
            $uid_sql='select deptid from  hrt_admin where id='.$uid;
            $id_result=$this->db->query($uid_sql);
            $row_id=mysqli_fetch_array($id_result);
            $dt_id=$row_id['deptid'];
         
          //查询出模版的使用范围
            $sql='select  id,deptlist from hrt_dailymodel where type='.$type;
            $result=$this->db->query($sql);
            
          
            if($result){	

               $arr=array();
               $deptArr=array();
               foreach($result as  $k=>$v){
          
                   
                   $deptArr[]=array(
                       'id'=>$v['id'],
                       'deptlist'=>$v['deptlist'],
                   );
                   $deptid=json_decode($deptArr[$k]['deptlist'],true);

                   $arr00=array(
                       'id'=>$v['id'],
                       'did'=>$deptid,
                   );
            
               }
                if(in_array($dt_id,$arr00['did'])){
                   	
                       //找到对应的模板的id
                       $m_sql='select field from hrt_dailymodel where id='.$arr00['id'];
                       $m_result=$this->db->query($m_sql);
                       $fieldlist=mysqli_fetch_array($m_result);

                       $arr=array(
                           'code'=>200,
                           ' msg'=>'成功',
                           'fieldlist'=>json_decode($fieldlist['field'],true),
                       );
                       $this->returnjson($arr);
                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'fieldlist'=>null,
                       );
                   }
                   $this->returnjson($arr);

           }else{
                 $arr=array(
                     'code'=>200,
                     'msg'=>'成功',
                     'fieldlist'=>null,
                 );
               $this->returnjson($arr);
           }
       }
//    5-11  写(日/周/月)报
       public  function  writeDailyAction(){
           //测试
               // $uid=$this->post('userId');
               // $type=$this->post('type');
               // $datetime=$this->post('datetime');
               // $content=$this->post('content');
               // $fieldvalue=$this->post('fieldValueList');
               // $imagelist=$this->post('imageList');

          $data=json_decode($this->post('data'),true);
          $uid=$data['userId'];
          $type=$data['type'];
          $datetime=$data['datetime'];
          $content=$data['content'];
          $fieldvalue=json_encode($data['fieldValueList'],JSON_UNESCAPED_UNICODE);
          $imagelist=json_encode($data['imageList'],true);
               
               

             if($content==null  && $fieldvalue !=null){
                $arr0=array(
                    'uid'=>$uid,
                    'type'=>$type,
                    'dt'=>$datetime,
                    'content'=>null,
                    'dcontent'=>$fieldvalue,
                    'iscustom'=>1,
                    'imglist'=>$imagelist,
                    'date'=>time(),
                );
           }else{
               $arr0=array(
                   'uid'=>$uid,
                   'type'=>$type,
                   'dt'=>$datetime,
                   'content'=>$content,
                   'dcontent'=>null,
                   'iscustom'=>0,
                   'imglist'=>$imagelist,
                   'date'=>time(),
               );
           }
         
           $rusult=$this->db->record('[Q]daily',$arr0);
           if($rusult){
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                );
               $this->returnjson($arr);
           }else{
               $this->showreturn('','失败',201);
           }

       }

//       5-12我写的工作汇报列表
       public  function  myDailyListAction(){
             // $uid=$this->post('userId');
             // $pageNo=$this->post('pageNo');
             // $pageSize=$this->post('pageSize');


            $data=json_decode($this->post('data'),true);
            $uid=$data['userId'];
            $pageNo=$data['pageNo'];
            $pageSize=$data['pageSize'];

            $sql='select *from hrt_daily where uid='.$uid;
            $r=$this->db->query($sql);
            $total=mysqli_num_rows($r);
            $eNum=ceil($total/$pageSize);
           //判断是否最后一页
            $arr=array();

           $limit=(($pageNo-1)*$pageSize).",".$pageSize;

           $d_sql="select a.id,b.id as uid,b.face,b.name,a.type,a.date,
                   a.dt,a.iscustom,a.content ,a.dcontent
                    from  hrt_daily    a
                   LEFT JOIN hrt_admin  b  on a.uid=b.id
                   where a.uid=$uid  order by a.date desc limit $limit ";

           $d_result=$this->db->query($d_sql);
           if($d_result){
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
               foreach($d_result as $v){

                   $arr['list'][]=array(

                       'id'=>$v['id'],
                       'userid'=>$v['uid'],
                       'headiconurl'=>FACE.$v['face'],
                       'truename'=>$v['name'],
                       'type'=>$v['type'],
                       'publishtime'=>$v['date'],
                       'reporttime'=>strtotime($v['dt']),
                       'iscustom'=>$v['iscustom'],
                       'content'=>$v['content'],
                       'valuelist'=>json_decode($v['dcontent'],true),
                   );
               }
               $this->returnjson($arr);
           }else{
               $this->showreturn('','失败',201);
           }
       }
//        5-13工作汇报统计列表
              public function  dailyCountListAction()
        {
            // $uid = $this->post('userId');
            // $type = $this->post('type');

            // $pageNo = $this->post('pageNo');
            // $pageSize = $this->post('pageSize');


            $data=json_decode($this->post('data'),true);
            $uid=$data['userId'];
            $type=$data['type'];
            $pageNo=$data['pageNo'];
            $pageSize=$data['pageSize'];

            $sql = "select *from  hrt_dailyrule   where  uid=$uid";
            $result = $this->db->query($sql);
            $r_row = mysqli_fetch_array($result);
            if ($r_row['isall'] == 1) {
                //能查看全部
                $sql_0 = 'select *from hrt_daily where type=' . $type;
                $r = $this->db->query($sql_0);
                $total = mysqli_num_rows($r);
                $eNum = ceil($total / $pageSize);
                $limit = (($pageNo - 1) * $pageSize) . "," . $pageSize;
                if ($pageNo >= $eNum) {
                    $arr = array(
                        'code' => 200,
                        'msg' => '成功',
                        'isend' => true,
                    );
                } else {
                    $arr = array(
                        'code' => 200,
                        'msg' => '成功',
                        'isend' => false,
                    );
                }
                $n_sql = "select dt,count(distinct uid) as count from hrt_daily where type=$type group by dt order by dt desc";

                $n_r = $this->db->query($n_sql);
                foreach ($n_r as $v) {
                    $arr['list'][] = array(
                        'startdate' => strtotime($v['dt']),
                        'num' => $v['count'],
                    );
                }
                $this->returnjson($arr);
            } else {
                //查看指定范围
                $range=json_decode($r_row['rangelist'],true);
                $ids=array();
                foreach($range as  $key=>$value) {
                    if($value['type']==1){
                        $ids[] = $value['id'];
                    }else{
                        $d_sql='select id from hrt_admin where deptid='.$value['id'];
                        $d_r=$this->db->query($d_sql);
                        foreach($d_r as $vv){
                            $ids[]=$vv['id'];
                        }
                    }
                }
                $ids_new=array_unique($ids); //去重
                $idsN=implode(',',$ids_new);  //能查看人员的id  即uid
                $t_sql="select dt,count(distinct uid) as count from hrt_daily where uid in ($idsN) and type=$type group by  dt order by dt desc" ;
                $t_result=$this->db->query($t_sql);
                $total = mysqli_num_rows($t_result);
                $eNum = ceil($total / $pageSize);
                $limit = (($pageNo - 1) * $pageSize) . "," . $pageSize;
                if ($pageNo >= $eNum) {
                    $arr = array(
                        'code' => 200,
                        'msg' => '成功',
                        'isend' => true,
                    );
                } else {
                    $arr = array(
                        'code' => 200,
                        'msg' => '成功',
                        'isend' => false,
                    );
                }
                if(mysqli_num_rows($t_result)>0){

                    foreach ($t_result as $vv) {
                        $arr['list'][] = array(
                            'startdate' => strtotime($vv['dt']),
                            'num' => $vv['count'],
                        );
                    }

                }else{
                    $arr['list']=null;
                }

                $this->returnjson($arr);
            }

}
//   5-14   日报月统计数据汇报人员列表
          public  function dailyMonthCountAction(){
              //测试
                // $uid=$this->post('userId');
                // $year=$this->post('year');
                // $months=$this->post('month');
                // $pageNo=$this->post('pageNo');
                // $pageSize=$this->post('pageSize');
                  
                // $month=sprintf("%02d",$months);
             
             $data=json_decode($this->post('data'),true);
             $uid=$data['userId'];
             $year=$data['year'];
             $months=$data['month'];
             $pageNo=$data['pageNo'];
             $pageSize=$data['pageSize'];
             $month=sprintf("%02d",$months);


              $r_sql="select *from  hrt_dailyrule   where  uid=$uid ";
              $r_result=$this->db->query($r_sql);
              $r_row=mysqli_fetch_array($r_result);
              if($r_row['isall']==1){
                  $dt=$year."-".$month;

                  $sql="select *from hrt_daily where   dt like  '$dt%'  and type=1";
                  $sql_mem="select *from hrt_daily  where   dt like  '$dt%' and type=1 group by uid  ";
                  $r=$this->db->query($sql);
                  $r_m=$this->db->query($sql_mem);
                  $m_total=mysqli_num_rows($r_m);//汇报的总人数
                  $total=mysqli_num_rows($r);  //日报的数量

                  $eNum=ceil($total/$pageSize);
                  $limit=(($pageNo-1)*$pageSize).",".$pageSize;
                  //判断是否最后一页
                  $arr=array();
                  if($pageNo>=$eNum){
                      $arr=array(
                          'code'=>200,
                          'msg'=>'成功',
                          'totalusernum'=>$m_total,
                          'reportnum'=>$total,
                          'isend'=>true,
                      );
                  }else{
                      $arr=array(
                          'code'=>200,
                          'msg'=>'成功',
                          'totalusernum'=>$m_total,
                          'reportnum'=>$total,
                          'isend'=>false,
                      );
                  }
             $sqls="select b.id,b.face,b.name,a.dt from hrt_daily a
                     left join hrt_admin b on  a.uid=b.id
                     where dt like '$dt%'  and  a.type=1  group by b.id
                     ";
             $results=$this->db->query($sqls);
                  $arrs=array();
             foreach($results as $k=>$vs ){
                 $ssArr=array();
                 $id_sql="select uid , dt from hrt_daily where dt like
                         '$dt%' and type=1 and uid=".$vs['id']."  group by dt order by dt asc";
                 $id_r=$this->db->query($id_sql);
                 foreach($id_r  as $rv){
                     //正则表达式去掉数字开头的0,substr截取年月日的日.
                       $day=preg_replace("/^0*/","",substr($rv['dt'],-2));
                       // $day=substr($rv['dt'],-2);
                       $ssArr[]=$day;
                 }
                   $ssArr=array_unique($ssArr); //数组去重
                   $arr['list'][]=array(
                           'id'=>$vs['id'],
                           'headiconurl'=>FACE.$vs['face'],
                           'truename'=>$vs['name'],
                           'days'=>$ssArr,
                   );

             }


              }else{
                 //我的查看权限不是全部
                  $range=json_decode($r_row['rangelist'],true);

                  $ids=array();
                  foreach($range as  $key=>$value) {

                      if($value['type']==1){
                          $ids[] = $value['id'];
                      }else{
                          $d_sql='select id from hrt_admin where deptid='.$value['id'];
                          $d_r=$this->db->query($d_sql);
                          foreach($d_r as $vv){
                              $ids[]=$vv['id'];
                          }
                      }
                  }

                  $ids_new=array_unique($ids); //去重
                  $idsN=implode(',',$ids_new);  //能查看人员的id  即uid
                
                  $dt=$year."-".$month;

                  $sql="select *from hrt_daily where   dt like  '$dt%'  and type=1  and uid in ($idsN)";
                  $sql_mem="select *from hrt_daily  where   dt like  '$dt%' and type=1  and uid in ($idsN) group by uid  ";
                  $r=$this->db->query($sql);
                  $r_m=$this->db->query($sql_mem);
                  $m_total=mysqli_num_rows($r_m);//汇报的总人数
                  $total=mysqli_num_rows($r);  //日报的数量

                  $eNum=ceil($total/$pageSize);
                  $limit=(($pageNo-1)*$pageSize).",".$pageSize;
                  //判断是否最后一页
                  $arr=array();
                  if($pageNo>=$eNum){
                      $arr=array(
                          'code'=>200,
                          'msg'=>'成功',
                          'totalusernum'=>$m_total,
                          'reportnum'=>$total,
                          'isend'=>true,
                      );
                  }else{
                      $arr=array(
                          'code'=>200,
                          'msg'=>'成功',
                          'totalusernum'=>$m_total,
                          'reportnum'=>$total,
                          'isend'=>false,
                      );
                  }

                  $sqls="select b.id,b.face,b.name,a.dt from hrt_daily a
                     left join hrt_admin b on  a.uid=b.id
                     where dt like '$dt%'  and  a.type=1 and a.uid  in  ($idsN)
                      group by b.id";

                   $results=$this->db->query($sqls);

                  if(mysqli_num_rows($results)>0){

                      $arrs=array();
                       
                      foreach($results as $k=>$vs ){
                          $ssArr=array();
                          $id_sql="select uid , dt from hrt_daily where dt like
                         '$dt%' and type=1 and uid=".$vs['id']."  group by dt order by dt asc";
                          $id_r=$this->db->query($id_sql);
                          foreach($id_r  as $rv){
                            // print_r($rv);
                              //正则表达式去掉数字开头的0,substr截取年月日的日.
                              $day=preg_replace("/^0*/","",substr($rv['dt'],-2));
                              // echo $day;
                              // $day=substr($rv['dt'],-2);
                              $ssArr[]=$day;
                            
                             
                          }
                      
                       
                          $arr['list'][]=array(
                              'id'=>$vs['id'],
                              'headiconurl'=>FACE.$vs['face'],
                              'truename'=>$vs['name'],
                              'days'=>$ssArr,
                          );
                      }
                  }else{

                      $arr['list']=null;
                  }
              }
              $this->returnjson($arr);
}
















//       5-15 我可以查看的工作汇报
        public function mySeeDailyListAction(){
              //测试
             // $uid=$this->post('userId');
             // $year=$this->post('year');
             // $month=$this->post('month');
             // $day=$this->post('day');
             // $type=$this->post('type');
             // $memberId=$this->post('memberId');
             // $pageNo=$this->post('pageNo');
             // $pageSize=$this->post('pageSize');

            $data=json_decode($this->post('data'),true);
            $uid=$data['userId'];
            $year=$data['year'];
            $month=$data['month'];
            $day=$data['day'];
            $type=$data['type'];
            $memberId=$data['memberId'];
            $pageNo=$data['pageNo'];
            $pageSize=$data['pageSize'];

             $r_sql="select *from  hrt_dailyrule   where  uid=$uid";
             $r_result=$this->db->query($r_sql);
             $r_row=mysqli_fetch_array($r_result);
             if($r_row['isall']==1){
                 //1.表示查看全部
                $dt=$year.'-'.$month.'-'.$day;
                 if($memberId !=''){
                     $sql_0="select *from hrt_daily where dt='$dt' and type=$type and uid=$memberId";
                     $r=$this->db->query($sql_0);
                     $total=mysqli_num_rows($r);
                     $eNum=ceil($total/$pageSize);
                     $limit=(($pageNo-1)*$pageSize).",".$pageSize;

                     $sql_1="select a.id,b.id as uid,b.face,b.name,a.type,a.date,a.dt,
                             a.iscustom,a.content,a.dcontent  from hrt_daily a
                             left join hrt_admin b on a.uid=b.id
                             where dt='$dt' and a.type=$type and uid=$memberId 
                             order by a.date desc limit $limit";
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
                        $arr['list'][]=array(
                            'id'=>$v_1['id'],
                            'userid'=>$v_1['uid'],
                            'headiconurl'=>FACE.$v_1['face'],
                            'truename'=>$v_1['name'],
                            'type'=>$v_1['type'],
                            'publishtime'=>$v_1['date'],
                            'reporttime'=>strtotime($v_1['dt']),
                            'iscustom'=>$v_1['iscustom'],
                            'content'=>$v_1['content'],
                            'valuelist'=>json_decode($v_1['dcontent'],true),
                        );
                     }
                    $this->returnjson($arr);

                 }else{
                     $sql_0="select *from hrt_daily where dt='$dt' and type=$type ";
                     $r=$this->db->query($sql_0);
                     $total=mysqli_num_rows($r);
                     $eNum=ceil($total/$pageSize);

                     $limit=(($pageNo-1)*$pageSize).",".$pageSize;
                     $sql_1="select a.id,b.id as uid,b.face,b.name,a.type,a.date,a.dt,
                             a.iscustom,a.content,a.dcontent  from hrt_daily a
                             left join hrt_admin b on a.uid=b.id
                             where dt='$dt' and a.type=$type  order by a.date desc limit $limit";
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
                         $arr['list'][]=array(
                             'id'=>$v_1['id'],
                             'userid'=>$v_1['uid'],
                             'headiconurl'=>FACE.$v_1['face'],
                             'truename'=>$v_1['name'],
                             'type'=>$v_1['type'],
                             'publishtime'=>$v_1['date'],
                             'reporttime'=>strtotime($v_1['dt']),
                             'iscustom'=>$v_1['iscustom'],
                             'content'=>$v_1['content'],
                             'valuelist'=>json_decode($v_1['dcontent'],true),
                         );
                     }
                     $this->returnjson($arr);
                 }
             }else{
                 //2.表示查看范围
              $range=json_decode($r_row['rangelist'],true);
                 $ids=array();
               foreach($range as  $key=>$value) {
                    if($value['type']==1){
                        $ids[] = $value['id'];
                    }else{
                        $d_sql='select id from hrt_admin where deptid='.$value['id'];
                        $d_r=$this->db->query($d_sql);
                        foreach($d_r as $vv){
                            $ids[]=$vv['id'];
                        }
                    }
               }
                 $ids_new=array_unique($ids); //去重
                 $idsN=implode(',',$ids_new);  //能查看人员的id  即uid
               
                 $dt=$year.'-'.$month.'-'.$day;
                 
                 $sql_total="select *from hrt_daily where dt='$dt'
                  and type=$type and  uid in ($idsN) ";
               
                 $total_r=$this->db->query($sql_total);
                 $total=mysqli_num_rows($total_r);
                 $eNum=ceil($total/$pageSize);
                 $limit=(($pageNo-1)*$pageSize).",".$pageSize;
                 if($memberId ==null){
             $sql_1="select a.id,b.id as uid,b.face,b.name,a.type,a.date,a.dt,
             a.iscustom,a.content,a.dcontent  from hrt_daily a
             left join hrt_admin b on a.uid=b.id
             where dt='$dt' and a.type=$type and uid in ($idsN)  order by a.date desc limit $limit";
         
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
                         $arr['list'][]=array(
                             'id'=>$v_1['id'],
                             'userid'=>$v_1['uid'],
                             'headiconurl'=>FACE.$v_1['face'],
                             'truename'=>$v_1['name'],
                             'type'=>$v_1['type'],
                             'publishtime'=>$v_1['date'],
                             'reporttime'=>strtotime($v_1['dt']),
                             'iscustom'=>$v_1['iscustom'],
                             'content'=>$v_1['content'],
                             'valuelist'=>json_decode($v_1['dcontent'],true),
                         );
                     }
                     $this->returnjson($arr);


                 }else{
                    //指定查看某个人的
                     $sql_1="select a.id,b.id as uid,b.face,b.name,a.type,a.date,a.dt,
             a.iscustom,a.content,a.dcontent  from hrt_daily a
             left join hrt_admin b on a.uid=b.id
             where dt='$dt' and a.type=$type and uid=$memberId order by a.date desc limit $limit";

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
                         $arr['list'][]=array(
                             'id'=>$v_1['id'],
                             'userid'=>$v_1['uid'],
                             'headiconurl'=>FACE.$v_1['face'],
                             'truename'=>$v_1['name'],
                             'type'=>$v_1['type'],
                             'publishtime'=>$v_1['date'],
                             'reporttime'=>strtotime($v_1['dt']),
                             'iscustom'=>$v_1['iscustom'],
                             'content'=>$v_1['content'],
                             'valuelist'=>json_decode($v_1['dcontent'],true),
                         );
                     }
                     $this->returnjson($arr);
                 }


             }
              // $this->returnjson($arr);

         }

    // 5-16工作汇报下的评论列表
          public function dailyCommentListAction(){
              //远端测试
                // $id=$this->post('id');
                // $pageNo=$this->post('pageNo');
                // $pageSize=$this->post('pageSize');

               $data=json_decode($this->post('data'),true);
               $id=$data['id'];
               $pageNo=$data['pageNo'];
               $pageSize=$data['pageSize'];

              $sql='select *from hrt_dailycomment where did='.$id;
              $r=$this->db->query($sql);
              $total=mysqli_num_rows($r);
              $eNum=ceil($total/$pageSize);

              //判断是否最后一页
              $arr=array();

              $limit=(($pageNo-1)*$pageSize).",".$pageSize;
              $sql_1="select a.id,b.id as uid ,b.face ,b.name,a.content,a.cdate
                    from hrt_dailycomment  a
                    left join hrt_admin b  on  a.uid=b.id
                    where did=$id limit $limit";

              $result=$this->db->query($sql_1);

              if($result){
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
                  foreach($result  as  $v){

                         $arr['list'][]=array(
                             'id'=>$v['id'],
                             'evaluateuserid'=>$v['uid'],
                             'evaluateheadicon'=>FACE.$v['face'],
                             'evaluateuser'=>$v['name'],
                             'content'=>$v['content'],
                             'updatetime'=>$v['cdate'],
                         );
                    }
                  $this->returnjson($arr);
              }else{
                  $this->showreturn('','失败',201);
              }

          }
//  5-17 发布评论
       public  function   publishDailyCommentAction(){
             // $uid=$this->post('userId');
             // $did=$this->post('id');
             // $content=$this->post('content');


          $data=json_decode($this->post('data'),true);
          $uid=$data['userId'];
          $did=$data['id'];
          $content=$data['content'];

             $arr0=array(
                 'did'=>$did,
                 'uid'=>$uid,
                 'content'=>$content,
                 'cdate'=>time(),

             );
             $result=$this->db->record('[Q]dailycomment',$arr0);
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

//5-18工作汇报详情
      public function  dailyDetailAction(){
            // $id=$this->post('id');

           $data=json_decode($this->post('data'),true);
           $id=$data['id'];

            $sql='SELECT  a.id,b.id as uid,b.face,b.name,a.type,a.date,
                  a.dt,a.imglist,a.content,a.dcontent
                  from  hrt_daily a
                  left join hrt_admin b on a.uid=b.id  where a.id='.$id;
            $r=$this->db->query($sql);
            $row=mysqli_fetch_array($r);
            $num=mysqli_num_rows($r);
            if($num>0){
                $iArr=json_decode($row['imglist'],true);

                   foreach($iArr as $img){
                    $imgArr[]=FACE.$img;  

                 }
                 $arr=array(
                     'code'=>200,
                     'msg'=>'成功',
                     'id'=>$row['id'],
                     'headiconurl'=>FACE.$row['face'],
                     'truename'=>$row['name'],
                     'type'=>$row['type'],
                     'publishtime'=>$row['date'],
                     'reporttime'=>strtotime($row['dt']),
                     'imagelist'=>$imgArr,
                     'content'=>$row['content'],
                     'valuelist'=>json_decode($row['dcontent'],true),
                 );
                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
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
    private $tmparr = array();
    public  function   temp1($pid,$arr){
        $sql='select id,pid from hrt_dept where id='.$pid;
        $result=$this->db->query($sql);
        $row=mysqli_fetch_array($result);

        if($row['pid'] !=1){
            $this->temp1($row['pid'],$arr);
        }
       $this->tmparr[] = (int)$row['id'];
    }


    function temp2($count,$arr) {

        $arr['id'] = $this->tmparr[$count];
        if (isset($this->tmparr[$count+1])) {
            $count++;
            $arr['departmentlist'][] = $this->temp2($count,$arr);
        }

        return $arr;
    }


    function test2($ids) {
        $arr = array();
        $id=$ids;
        $sql='select pid from hrt_dept where id='.$id;
        $result=$this->db->query($sql);
        $row=mysqli_fetch_array($result);
        if ($result) {
            $this->temp1($row['pid'],$arr);
        }
        $this->tmparr[] = $id;

        $arr = array();
        $count = 0;
        $arr = $this->temp2($count,$arr);

       return $arr;
    }



}