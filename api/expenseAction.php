<?php
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
//九.   费用模块
class  expenseClassAction extends  apiAction{
    //  9-1.获取可以查看门店费用的成员列表
    public function getmemberListAction(){
        $arr0=array(
            'isexpense'=>1,
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
//                    'positionname'=>$val['ranking'],
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
//  9-2添加有权限的成员
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
        $isexpense=1;
        if($isall==1){

            $result=$this->db->record('[Q]admin',"`isexpense`='".$isexpense."'","`isexpense`=0");
            if($result){
        // 第三方平台推送
                $user_sende='admin';

                $p_sql='SELECT *from hrt_admin';
                $p_r=$this->db->query($p_sql);
                foreach ($p_r as $key => $value) {
                    $to_users[]='user'.$value['id'];
                }

                $summary='您已获得查看门店费用权限';
                $content='您已获得查看门店费用权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=9,1,$summary,$content);

                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
        }else{
            foreach($userlist as $val){
                $arr0=array(
                    'id'=>$val,
                );
                $results=$this->db->record('[Q]admin',"`isexpense`='".$isexpense."'","`id`='".$arr0['id']."'");

                 $to_users[]='user'.$val;
            }

                 // 第三方平台推送
                $user_sende='admin';

                $summary='您已获得查看门店费用权限';
                $content='您已获得查看门店费用权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=9,1,$summary,$content);




            $this->returnjson($arr);
        }

    }
//    9-3.移除有权限的成员
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
        $isexpense=0;
        $result=$this->db->record('[Q]admin',"`isexpense`='".$isexpense."'","`id`='$removedUserId'");
        if($result){
                  // 第三方平台推送
            $user_sende='admin';
            $to_users='user'.$removedUserId;

            $summary='您的查看门店费用权限已被移除';
            $content='您的查看门店费用权限已被移除';

            $push=$this->custmsgpush($user_sende,$to_users,$type=9,0,$summary,$content);


            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }

    }
//    9-4获取每个月的费用列表
  public function getExpenseListAction()
  {
      // $uid = $this->post('userId');
      // $departmentid=$this->post('departmentId');

      $data=json_decode($this->post('data'),true);
      $uid=$data['userId'];
      $departmentid=$data['departmentId'];
      //获取当前时间戳,根据时间戳写出年份和月份
      $now = time();
      $year = date('Y', $now);
      $m = date('m', $now);
      $month = str_replace("0", "", $m);


        //查询出模版对应的费用字段
      // $sql0 = 'select *from hrt_expensemodel';
      // $result = $this->db->query($sql0);
      // $row=mysqli_fetch_array($result);
      // $fArr=json_decode($row['field'],true);
      // foreach($fArr as $ff){
      //     $mm[]=$ff['field'];
      // }

      //      //得到month后循环1-month月份去查找数据
      for ($i = 1; $i <= $month; $i++) {
          $sql_1 = 'select *from hrt_expense where year=' .$year. ' and month=' . $i.' and mid='.$departmentid;
          $r_1 = $this->db->query($sql_1);
          $row_1 = mysqli_fetch_array($r_1);
          $dArr[] = $row_1;

      }
  
      foreach ($dArr as $k => $v) {
      
          $a_sql = 'select isstoremanager from hrt_admin where id='.$uid;
          $a_result = $this->db->query($a_sql);
          $ismanager=mysqli_fetch_array($a_result);
          if ($v=='') {
              //通过查询数据库比对没有录入的返回数据
              $ssArr[] = array(
                  'month' => $k + 1,
                  'totalmoney'=>0,
                  'hasaddpermission' => $ismanager[0],
                  'customlist' => null,
              );
          } else {
              //有录入信息的
            // 计算总的费用
              $exdata=json_decode($v['data'],true);
              $sum=0;
               foreach($exdata as $e){
                  $sum+=$e['value'];
               }
             
            
            $eArr=json_decode($v['data'],true);
            $eeArr=array();
            foreach($eArr as $ee){
                    $eeArr[]=array(
                        'field'=>$ee['field'],
                        'value'=>$ee['value'],
                    );
            }


              $ssArr[] = array(
                  'month' => $k + 1,
                  'totalmoney'=>$sum,
                  'hasaddpermission' => $ismanager[0],
                  'customlist' =>$eeArr,
              );

          }
      }
 
      //往前推一年
      $oldyear=$year-1;
      $oldmonth=12;

//      往前推一年
      for ($j = 1; $j<= $oldmonth; $j++) {
          $sql_2 = 'select *from hrt_expense where year=' . $oldyear . ' and month=' . $j.' and mid='.$departmentid;

          $r_2 = $this->db->query($sql_2);
          $row_2 = mysqli_fetch_array($r_2);
          $dArrs[] = $row_2;
      }
      $arr = array(
          'code' => 200,
          'msg' => '成功',
      );
      foreach($dArrs as $kk=>$vv){
          $a_sqls = 'select isstoremanager from hrt_admin where id=' . $uid;
          $a_results = $this->db->query($a_sqls);
          $ismanagers = mysqli_fetch_array($a_results);
          //通过查询数据库比对没有录入的返回数据
          if ($vv == '') {

              $mArr[] = array(
                  'month' => $kk + 1,
                  'totalmoney'=>0,
                  'hasaddpermission' => $ismanagers[0],
                  'customlist' => null,
              );
          } else {
              //有录入信息的
              $exdatas=json_decode($vv['data'],true);
              $sums=0;
              foreach($exdatas as $e){
                  $sums+=$e['value'];
              }
                 //查询出模版对应的费用字段
             
              $eArrs=json_decode($vv['data'],true);
              $eeArrs=array();
              foreach($eArrs as $ees){
                  $eeArrs[]=array(
                      'field'=>$ees['field'],
                      'value'=>$ees['value'],
                  );
              }

              $mArr[] = array(
                  'month' => $kk + 1,
                  'totalmoney'=>$sums,
                  'hasaddpermission' => $ismanagers[0],
                  'customlist' =>$eeArrs,
              );
          }
      }

      $arr['list'][] = array(
          'year'=>$year,
          'months' => $ssArr,
      );
       $arr['list'][] = array(
          'year' => $oldyear,
          'months' => $mArr,
      );
       $this->returnjson($arr);
  }






//9-5修改费用字段
   public function editExpenseModelAction(){
       // $fieldlist=$this->post('fieldList');


      $data=json_decode($this->post('data'),true);
      $fieldlist=$data['fieldList'];

       $arr0=array(

           'field'=>json_encode($fieldlist,JSON_UNESCAPED_UNICODE),
       );
       $result=$this->db->record('[Q]expensemodel',$arr0,"`id`=1");
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
//   9-6 获取费用字段列表
    public function getExpenseModelAction()
    {
        $sql = 'select *from hrt_expensemodel ';
        $result = $this->db->query($sql);

        if ($result) {
            foreach ($result as $v) {
                $field = json_decode($v['field'], true);
                $fArr = $field;
            }
            $arr = array(
                'code' => 200,
                'msg' => '成功',
                'list' => $fArr,
            );
            $this->returnjson($arr);
        } else {
            $this->showreturn('', '失败', 201);
        }
    }

//9-7录入门店费用的各项费用数据
        public   function  inputExpenseAction(){
            //测试
            // $uid=$this->post('userId');
            // $year=$this->post('year');
            // $month=$this->post('month');
            // $field=$this->post('fieldList');




          $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $year=$data['year'];
           $month=$data['month'];
           $field=json_encode($data['fieldList'],JSON_UNESCAPED_UNICODE);
      

//            根据uid查询出所属门店的id
            $sql='SELECT *from hrt_admin where id='.$uid;
            $r=$this->db->query($sql);
            $row=mysqli_fetch_array($r); 

            $arr0=array(
                'uid'=>$uid,
                'mid'=>$row['deptid'],
                'year'=>$year,
                'month'=>$month,
                'data'=>$field,
                'date'=>time(),
            );
            $result=$this->db->record('[Q]expense',$arr0);
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
//     9-8初始化费用数据
       public function  initialExpenseAction(){
              // $uid=$this->post('userId');

             $data=json_decode($this->post('data'),true);
             $uid=$data['userId'];


           $sql='select a.isstoremanager,a.deptid,b.name,a.isexpense,b.isstore
              from hrt_admin a
               left join  hrt_dept b  on a.deptid=b.id where a.id='.$uid;
           $result=$this->db->query($sql);
           $row=mysqli_fetch_array($result);
           if($result){
                if($row['isstore']==1){
                           $arr=array(
                               'code'=>200,
                               'msg'=>'成功',
                               'isstoremanager'=>$row['isstoremanager'],
                               'departmentid'=>$row['deptid'],
                               'departmentname'=>$row['name'],
                               'haspermission'=>$row['isexpense'],
                          );
                }else{
                           $arr=array(
                               'code'=>200,
                               'msg'=>'成功',
                               'isstoremanager'=>$row['isstoremanager'],
                               'departmentid'=>null,
                               'departmentname'=>null,
                               'haspermission'=>$row['isexpense'],
                          );
                }
                
             $this->returnjson($arr);
           }else{
             $this->showreturn('','失败',201);
           }
       }

 /*---------------------第三方平台推送消息-----------------------------------*/

 
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