<?php
//报销模块
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
class consumeClassAction extends apiAction{
    //  6-1可以报销的用户列表
    public function getmemberListAction(){
        $arr0=array(
            'isconsume'=>1,
        );
        $field0='`id`,`face`,`name`,`approver`,`teller`';
        $result=$this->db->getrows('[Q]admin',$arr0,$field0);
        if($result){
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
            );

            foreach($result as $val){
                  //根据出纳人的id查询出出纳人的用户名称
                $sql='SELECT *from hrt_admin where id='.$val['teller'];
                $result=$this->db->query($sql);
                $row=mysqli_fetch_array($result);

                $range=$val['approver'];
                $rArr=explode(',',$range);
                $sArr=array();
                foreach($rArr as $v){
                   $arrs=array(
                       'id'=>$v,
                   );
                    $fileds='`id`,`name`,`face`';
                    $r=$this->db->getrows('[Q]admin',$arrs,$fileds);

                    foreach($r as $vs){

                       $sArr[]=array(
                            'id'=>$vs['id'],
                            'truename'=>$vs['name'],
                            'headiconurl'=>$vs['face'],
                        );
                    }
                }
                $arr['userlist'][]=array(
                    'id'=>$val['id'],
                    'headiconurl'=>FACE.$val['face'],
                    'truename'=>$val['name'],
                    'cashierid'=>$val['teller'],
                    'cashier'=>$row['name'],
                    'auditstepuserlist'=>$sArr,

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

//    6-2添加报销审批人以及权限人
    public function addMemberAction(){
        // $isall=$this->post('isAllMember');
        // $userlist=json_decode($this->post('userList'),true);
        // $audituserlist=json_decode($this->post('auditUserList'),true);
        // $approver=implode(',',$audituserlist);
        // $cashier=$this->post('cashier');

         $data=json_decode($this->post('data'),true);
         $isall=$data['isAllMember'];
         $userlist=$data['userList'];
         $audituserlist=$data['auditUserList'];
         $approver=implode(',',$audituserlist);
         $cashier=$data['cashier'];
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $isconsume=1;
        if($isall==1){

            $result=$this->db->record('[Q]admin',"`isconsume`='".$isconsume."',`approver`='".$approver."',`teller`='".$cashier."'","`isconsume`=0");
            if($result){
              // 第三方平台推送
                $user_sende='admin';

                $p_sql='SELECT *from hrt_admin';
                $p_r=$this->db->query($p_sql);
                foreach ($p_r as $key => $value) {
                    $to_users[]='user'.$value['id'];
                }

                $summary='您已获得报销权限';
                $content='您已获得报销权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=6,1,$summary,$content);


                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
        }else{
            foreach($userlist as $val){
                $arr0=array(
                    'id'=>$val,
                );
             $result=$this->db->record('[Q]admin',"`isconsume`='".$isconsume."',`approver`='".$approver."',`teller`='".$cashier."'","`id`='".$arr0['id']."'");

                $to_users[]='user'.$val;
            }


                 // 第三方平台推送
                $user_sende='admin';

                $summary='您已获得报销权限';
                $content='您已获得报销权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=6,1,$summary,$content);




            $this->returnjson($arr);
        }
    }
//    6-3.移除可以报销的用户
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
        $default=0;
        $result=$this->db->record('[Q]admin',"`isconsume`='".$default."',`approver`='".$default."',`teller`='".$default."'","`id`='".$removedUserId."'");
        if($result){
            // 第三方平台推送
            $user_sende='admin';
            $to_users='user'.$removedUserId;

            $summary='您的报销权限已被移除';
            $content='您的报销权限已被移除';

            $push=$this->custmsgpush($user_sende,$to_users,$type=6,0,$summary,$content);



            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }
    }
//    6-4查看消费类型列表
       public  function  getTypeListAction(){
            $field='`id`,`name`,`icon`';
           $result=$this->db->getrows('[Q]consumetype','',$field);
           if($result){
               $arr=array(
                   'code'=>200,
                   'msg'=>'成功',
               );
               foreach($result as $v){
                    $arr['list'][]=array(
                        'id'=>$v['id'],
                        'name'=>$v['name'],
                        'icon'=>FACE.$v['icon'],
                    );
               }
               $this->returnjson($arr);

           }else{
               $this->showreturn('','失败',201);
           }
       }
        //6-5添加消费记录
public  function addConsumingRecordsAction(){

        // $uid=$this->post('userId');
        // $money=$this->post('money');
        // $typeid=$this->post('typeId');
        // $datetime=$this->post('datetime');
        // $imglist=$this->post('imageList');
        // $remark=$this->post('remark');
        // $date=time();

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $money=$data['money'];
       $typeid=$data['typeId'];
       $datetime=$data['datetime'];
       $imglist=json_encode($data['imageList'],true);
       $remark=$data['remark'];
       $date=time();
       $arr0=array(
           'uid'=>$uid,
           'money'=>$money,
           'type'=>$typeid,
           'cdate'=>$datetime,
           'img'=>$imglist,
           'remark'=>$remark,
           'status'=>0,
           'date'=>$date,
       );

      // print_r($arr0);
      // die;   	
        $result=$this->db->record('[Q]consuming_records',$arr0);
        if($result){
            $id=$this->db->insert_id();
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'id'=>$id,
            );
            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }
}

//6-6查看消费记录列表
public  function getRecordsListAction(){
    // $uid=$this->post('userId');

   $data=json_decode($this->post('data'),true);
   $uid=$data['userId'];

      $sql='SELECT a.id,b.icon,a.money,a.type,b.name,a.img,a.remark,a.cdate
      from hrt_consuming_records a
      left JOIN  hrt_consumetype b  on a.type=b.id  where  a.status=0 and a.uid='.$uid.' order by a.date  desc';
 
     $result=$this->db->query($sql);
     $row=mysqli_fetch_array($result);
          //1.我还没有审批完成的报销单数量
        $u_sql='SELECT count(1) as count  from hrt_consume_process where nowid='.$uid.'
        and fstate !=2';
        $u_r=$this->db->query($u_sql);
        $u_n=mysqli_fetch_array($u_r);
       // echo $u_n['count'];

        //2.我还没有标记支付的报销单数量
        $p_sql='SELECT  count(1) as count from hrt_consume_process where teller='.$uid.' and fstate=2 and  ispayed=0';
        $p_r=$this->db->query($p_sql);
        $p_n=mysqli_fetch_array($p_r);

        //3.我发布的还没有完成审核的数量(被驳回的也算没有完成)
        $w_sql='SELECT count(1)  as  count from hrt_consume_bill a
                left join  hrt_consume_process  b  on a.id=b.bid
                where a.uid='.$uid.'  and (b.fstate=0  or b.fstate=1)';
        $w_r=$this->db->query($w_sql);
        $w_n=mysqli_fetch_array($w_r);

        

         
     if($result && mysqli_num_rows($result)>0){
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
            //我还没有审批完成的报销单数量
            'unauditcount'=>$u_n['count'],
            //我还没有标记支付的报销单数量
            'unpaycount'=>$p_n['count'],
            //我发布的还没有完成审核的数量(被驳回的也算没有完成)
            'myunfinishedcount'=>$w_n['count'],
        );


        foreach($result as $v){
             // 输出图片
              $iArr=json_decode($v['img'],true);
                $imgArr=array();
                foreach($iArr as $vv){
                    $imgArr[]=FACE.$vv;
                 
                }
                $arr['list'][]=array(
                    'id'=>$v['id'],
                    'icon'=>FACE.$v['icon'],
                    'money'=>$v['money'],
                    'typeid'=>$v['type'],
                    'typename'=>$v['name'],
                    'imagelist'=>$imgArr,
                    'remark'=>$v['remark'],
                    'consumetime'=>strtotime($v['cdate']),
                );
        }

    }else{
          $arr=array(
              'code'=>200,
              'msg'=>'成功',
               //我还没有审批完成的报销单数量
              'unauditcount'=>$u_n['count'],
              //我还没有标记支付的报销单数量
              'unpaycount'=>$p_n['count'],
              //我发布的还没有完成审核的数量(被驳回的也算没有完成)
              'myunfinishedcount'=>$w_n['count'],
                'list'=>null,
          );
    }
    $this->returnjson($arr);

}

//6-7修改消费记录
public function editConsumingRecordsAction(){
    // $id=$this->post('id');
    // $uid=$this->post('userId');
    // $money=$this->post('money');
    // $typeid=$this->post('typeId');
    // $datetime=$this->post('datetime');
    // $img=$this->post('imageList');
    // $remark=$this->post('remark');
    // $date=time();

   $data=json_decode($this->post('data'),true);
   $id=$data['id'];
   $uid=$data['userId'];
   $money=$data['money'];
   $typeid=$data['typeId'];
   $datetime=$data['datetime'];
   $img=json_encode($data['imageList'],true);
   $remark=$data['remark'];
   $date=time();

    $arr0=array(
        'uid'=>$uid,
        'money'=>$money,
        'type'=>$typeid,
        'cdate'=>$datetime,
        'img'=>$img,
        'remark'=>$remark,
        'date'=>$date,
        'status'=>0,

    );
    $r=$this->db->record('[Q]consuming_records',$arr0,"`id`='$id'");
    if($r){
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
            );
        $this->returnjson($arr);
    }else{
        $this->showreturn('','失败',201);
    }




}

//6-8删除消费记录
public function deleteRecordsAction(){

//     $uid = $this->post('userId');
//     $id = $this->post('id');

   $data=json_decode($this->post('data'),true);
   $uid=$data['userId'];
   $id=$data['id'];
    $arr0 = array(
        'id' => $id,
        'uid' => $uid,
    );
    $result = $this->db->delete('[Q]consuming_records', $arr0);
    if ($result) {
        $arr = array(
            'code' => 200,
            'msg' => '成功',
        );
        $this->returnjson($arr);
    } else {
        $this->showreturn('', '失败', 201);
    }
}
//    6-9获取报销审批步骤
    public  function  getApproverListAction(){
            // $uid=$this->post('userId');
            // $otheruserid=$this->post('otherUserId');

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $otheruserid=$data['otherUserId'];

            $arr0=array(
                'id'=>$otheruserid,
            );
            $field='`approver`';
            $result=$this->db->getone('[Q]admin',$arr0,$field);

            if($result['approver']==0){
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'steplist'=>null,
                );
                $this->returnjson($arr);
            }else{
                $approver=explode(',',$result['approver']);

                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                );
                foreach($approver as $v){

                    $arrs=array(
                        'id'=>$v,
                    );
                    $fields='`id`,`name`,`face`';
                    $results=$this->db->getrows('[Q]admin',$arrs,$fields);
                    foreach($results as $vv){
                            $arr['steplist'][]=array(
                                'uerid'=>$vv['id'],
                                'truename'=>$vv['name'],
                                'headiconurl'=>FACE.$vv['face'],
                            );
                    }
                }
                $this->returnjson($arr);
            }
    }
//    6-10.新建报销单
public function createConsumeBillsAction(){
        // $uid=$this->post('userId');
        // $title=$this->post('title');
        // $list=json_decode($this->post('consumeList'),true);

        // $lists=implode(',',$list);
        // $subsidy=$this->post('subsidy');
        // $remark=$this->post('remark');
        // $date=date('Y-m-d',time());
        // $opdate=date('Y-m-d H:i:s',time());
    //生成报销单号随机码
    // $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
    // $code= $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(),2,5) . sprintf('%02d', rand(0, 99));

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $title=$data['title'];
       $list=$data['consumeList'];
       $lists=implode(',',$list);
       $subsidy=$data['subsidy'];
       $remark=$data['remark'];
       $date=date('Y-m-d H:i:s',time());
       $opdate=date('Y-m-d H:i:s',time());
       $yCode = array('A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J');
       $code= $yCode[intval(date('Y')) - 2011] . strtoupper(dechex(date('m'))) . date('d') . substr(time(), -5) . substr(microtime(),2,5) . sprintf('%02d', rand(0, 99));

        $arr0=array(
            'uid'=>$uid,
            'title'=>$title,
            'list'=>$lists,
            'subsidy'=>$subsidy,
            'remark'=>$remark,
            'code'=>$code,
            'date'=>$date,
        );
        $result=$this->db->record('[Q]consume_bill',$arr0);
        if($result){
        	 $id=$this->db->insert_id();
            //先将消费记录里面选中的标记
                foreach($list as $v1){
                    $arrs=array(
                        'status'=>1,

                    );
                    $rs=$this->db->record('[Q]consuming_records',$arrs,"`id`='$v1'");
                }
               
                $arr1=array(
                    'id'=>$uid,
                );
                $field1='`approver`,`teller`,`name`';
                $result1=$this->db->getone('[Q]admin',$arr1);
                $sArr=explode(',',$result1['approver']);
                $nowid=$sArr[0];
                $arr2=array(
                    'bid'=>$id,
                    'list'=>$result1['approver'],
                    'nowid'=>$nowid,
                    'nowstatus'=>0,
                    'nlist'=>$result1['approver'],
                    'fstate'=>0,
                    'teller'=>$result1['teller'],
                    'opdate'=>$opdate,
                );
                $result2=$this->db->record('[Q]consume_process',$arr2);
                //在result 中创建第一个状态第一个审核的人开始审核
                $arr3=array(
                	'cid'=>$id,
                	'uid'=>$nowid,
                	'status'=>6,
                	'remark'=>'开始审核',
                	'opdate'=> $opdate,
                	);
                $result3=$this->db->record('[Q]consume_result',$arr3);


              if($result3){
                //推送
                foreach ($sArr as $p) {
                      $ticker=$text=$result1['name'].'的'.$title;
                      $push=$this->android($p,$ticker,'待审核报销单',$text,5);
                }
              
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'id'=>$id,
                    );
                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
        }else{
            $this->showreturn('','失败',201);
        }

}
//6-11我提交的报销单列表
public  function myConsumeReportAction(){
        // $uid=$this->post('userId');
        // $pageNo=$this->post('pageNo');
        // $pageSize=$this->post('pageSize');


       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $pageNo=$data['pageNo'];
       $pageSize=$data['pageSize'];
        $t_sql='select *from hrt_consume_bill where uid='.$uid;
        $t_r=$this->db->query($t_sql);
        $total=mysqli_num_rows($t_r);
        $eNum=ceil($total/$pageSize);
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;


        $sql_1='SELECT a.id,a.title,a.uid,b.name,b.face, c.fstate,
                c.ispayed, c.nowid,c.opdate, a.date,a.list,a.subsidy 
                  from hrt_consume_bill a
                left join hrt_admin b on a.uid=b.id
                left join hrt_consume_process  c  on  a.id=c.bid  where a.uid='.$uid.'  order by  a.date  desc  limit '.$limit;
               
        $r_1=$this->db->query($sql_1);
        if($r_1){
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
            foreach($r_1 as $v){
                $arr1=array(
                    'id'=>$v['nowid'],
                );
                $f_1='`name`';
                $n_1=$this->db->getone('[Q]admin',$arr1,$f_1);
                $listArr=explode(',',$v['list']);
                $sum=0;
                foreach($listArr as $l){
                    $sum_sql='select money  from hrt_consuming_records
                      where id='.$l;
                    $r_s=$this->db->query($sum_sql);
                    foreach($r_s as $vvs){
                        $sum +=$vvs['money'];
                    }
                }
                $sum=$sum+$v['subsidy'];
                $arr['list'][]=array(
                    'id'=>$v['id'],
                    'title'=>$v['title'],
                    'applyuserid'=>$v['uid'],
                    'applyuser'=>$v['name'],
                    'applyheadicon'=>FACE.$v['face'],
                    'money'=>$sum,
                    'status'=>$v['fstate'],
                    'ispayed'=>$v['ispayed'],
                    'audituserid'=>$v['nowid'],
                    'audituser'=>$n_1['name'],
                    'publishtime'=>strtotime($v['date']),
                    'updatetime'=>strtotime($v['opdate']),
                );
            }
            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }

}
//6-12需要我审批的报销单列表
public  function myApprovedListAction(){
    // $uid=$this->post('userId');
    // $pageNo=$this->post('pageNo');
    // $pageSize=$this->post('pageSize');

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $pageNo=$data['pageNo'];
       $pageSize=$data['pageSize'];
        $t_sql='SELECT *from hrt_consume_process where a.nowstatus=0 and nowid='.$uid;
        $t_r=$this->db->query($t_sql);
        $total=mysqli_num_rows($t_r);
        $eNum=ceil($total/$pageSize);
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;

    $arr0=array(
        'id'=>$uid,
    );
    $f0='`name`';
    $r0=$this->db->getone('[Q]admin',$arr0,$f0);

    $sql_1='SELECT   b.id,b.title, b.uid,c.name ,c.face,
            a.fstate,a.ispayed,a.nowid,a.opdate,b.date  ,b.list,b.subsidy
            from hrt_consume_process  a
            left join  hrt_consume_bill b on a.bid=b.id
            left join hrt_admin  c   on c.id=b.uid
            where a.nowstatus=0 and  nowid='.$uid.' order by a.opdate desc ';
    $r_1=$this->db->query($sql_1);
    if($r_1 && mysqli_num_rows($r_1)>0){
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
        foreach($r_1 as $v1){
            $listArr=explode(',',$v1['list']);
            $sum=0;
            foreach($listArr as $l){
                $sum_sql='select money  from hrt_consuming_records
                      where id='.$l;
                $r_s=$this->db->query($sum_sql);
                foreach($r_s as $vvs){
                    $sum +=$vvs['money'];
                }
            }
            $sum=$sum+$v1['subsidy'];
            $arr['list'][]=array(
                'id'=>$v1['id'],
                'title'=>$v1['title'],
                'applyuserid'=>$v1['uid'],
                'applyuser'=>$v1['name'],
                'applyheadicon'=>FACE.$v1['face'],
                'money'=>$sum,
                'status'=>$v1['fstate'],
                'ispayed'=>$v1['ispayed'],
                'audituserid'=>$v1['nowid'],
                'audituser'=>$r0['name'],
                'publishtime'=>strtotime($v1['date']),
                'updatetime'=>strtotime($v1['opdate']),
            );
        }

    }else{
        if($pageNo>=$eNum){
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'isend'=>true,
                'list'=>null,
            );
        }else{
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'isend'=>false,
                'list'=>null,
            );
        }
    }
    $this->returnjson($arr);

}
//6-13我已经审批的报销单列表
public function  myApprovedBillListAction(){
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
        // $statusFilter=$this->post('statusFilter');
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
       $statusFilter=$data['statusFilter'];
       $memberId=$data['memberId'];


        //计算全部符合条件的总数,实现分页
        $t_sql='select *from hrt_consume_result where uid='.$uid;
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

            //表示查看全部时间内符合条件的信息
            if($statusFilter==null){
                //查看全部状态

                if($memberId==null){
                    //没有指定人
                   $sql1='SELECT b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where (a.status =2 or a.status=1 ) and  b.id is not null  and  a.uid='.$uid.' order by a.opdate desc limit '.$limit ;
                      
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                            $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }else{

                    //指定人
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where (a.status =2 or a.status=1 ) and  b.id is not null and  a.uid='.$uid.' and b.uid='.$memberId .' order by a.opdate desc  limit '.$limit ;
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                            $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);
                }


            }else if($statusFilter==1){
                //正在处理的
             
                if($memberId==null){
                    //没有指定人
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=0  order by a.opdate desc  limit '.$limit ;

                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }else{
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and  a.uid='.$uid.' and c.fstate=0 and b.uid='.$memberId.' order by a.opdate  desc  limit '.$limit ;
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }

            }else if($statusFilter==2){
            	// echo 111;
                //审批完成未支付的
                if($memberId==null){
                    //没有指定人
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=2 and c.ispayed=0  order by a.opdate desc limit '.$limit ;

                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }else{
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=2 and c.ispayed=0  and b.uid
                        ='.$memberId.' order by a.opdate  desc  limit '.$limit ;
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }

            }else if($statusFilter==3){
                //审批完成已支付
                if($memberId==null){
                    //没有指定人
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=2 and c.ispayed=1  order by a.opdate desc  limit '.$limit ;
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }else{
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=2 and ispayed=1
                         and b.uid='.$memberId.' order by a.opdate desc limit '.$limit ;
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }

            }else if($statusFilter==4){
                //已驳回
                if($memberId==null){
                    //没有指定人
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=1 order by a.opdate desc limit '.$limit ;
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }else{
                    $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list.b.subsidy 
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=1 and b.uid='.$memberId.'  order by  a.opdate desc limit '.$limit ;
                    $r1=$this->db->query($sql1);
                    if($r1 && mysqli_num_rows($r1)>0){
                        foreach($r1 as $v1){
                            $listArr=explode(',',$v1['list']);
                            $sum=0;
                            foreach($listArr as $l){
                                $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                                $r_s=$this->db->query($sum_sql);
                                foreach($r_s as $vvs){
                                    $sum +=$vvs['money'];
                                }
                            }
                             $sum=$sum+$v1['subsidy'];
                            //审核人的姓名
                            $arrs=array(
                                'id'=>$v1['nowid'],
                            );
                            $fields='`name`';
                            $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                            $arr['list'][]=array(
                                'id'=>$v1['id'],
                                'title'=>$v1['title'],
                                'applyuserid'=>$v1['uid'],
                                'applyuser'=>$v1['name'],
                                'applyheadicon'=>FACE.$v1['face'],
                                'money'=>$sum,
                                'status'=>$v1['fstate'],
                                'ispayed'=>$v1['ispayed'],
                                'audituserid'=>$v1['nowid'],
                                'audituser'=>$rs['name'],
                                'publishtime'=>strtotime($v1['date']),
                                'updatetime'=>strtotime($v1['opdate']),
                            );

                        }

                    }else{
                        $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                            'isend'=>true,
                            'list'=>null,
                        );
                    }
                    $this->returnjson($arr);

                }

            }

       }else{

            //表示查看范围内的信息
           if($statusFilter==null){
               //查看全部状态
               if($memberId==null){
                   //没有指定人
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy 
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and b.date like '."'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }else{

                   //指定人
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy 
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.'  and b.uid='.$memberId .'and
                         b.date like'."'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);
               }


           }else if($statusFilter==1){
               //正在处理的
               if($memberId==null){
                   //没有指定人
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=0 and b.date like '."'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }else{
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list.b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=0 and b.uid='.$memberId.' and b.date like '."'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }

           }else if($statusFilter==2){
               //审批完成未支付的
               if($memberId==null){
                   //没有指定人
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy 
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and  a.uid='.$uid.' and c.fstate=2 and c.ispayed=0  and b.date like'. "'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }else{
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy 
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and  a.uid='.$uid.' and c.fstate=2 and c.ispayed=0  and b.uid
                        ='.$memberId.' and b.date like '."'$date%'".' order by a.opdate desc   limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }

           }else if($statusFilter==3){
               //审批完成已支付
               if($memberId==null){
                   //没有指定人
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy 
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=2 and c.ispayed=1  and b.date
                        like  '."'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }else{
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy 
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=2 and ispayed=1
                         and b.uid='.$memberId.' and b.date like '."'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }

           }else if($statusFilter==4){
               //已驳回
               if($memberId==null){
                   //没有指定人
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and  a.uid='.$uid.' and c.fstate=1  and b.date like
                         '."'$date%'".' order by a.opdate desc limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }else{
                   $sql1='select b.id,b.title,b.uid,d.name,d.face,
                        c.fstate,c.ispayed,c.nowid,b.date,c.opdate,b.list,b.subsidy
                        from hrt_consume_result  a
                        left JOIN hrt_consume_bill b  on a.cid=b.id
                        left join hrt_consume_process c on b.id=c.bid
                        left JOIN hrt_admin d on  b.uid=d.id
                        where  (a.status =2 or a.status=1 ) and  b.id is not null and   a.uid='.$uid.' and c.fstate=1 and b.uid='.$memberId.' and b.date like '."'$date%'".' order by a.opdate desc  limit '.$limit ;
                   $r1=$this->db->query($sql1);
                   if($r1 && mysqli_num_rows($r1)>0){
                       foreach($r1 as $v1){
                           $listArr=explode(',',$v1['list']);
                           $sum=0;
                           foreach($listArr as $l){
                               $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                               $r_s=$this->db->query($sum_sql);
                               foreach($r_s as $vvs){
                                   $sum +=$vvs['money'];
                               }
                           }
                            $sum=$sum+$v1['subsidy'];
                           //审核人的姓名
                           $arrs=array(
                               'id'=>$v1['nowid'],
                           );
                           $fields='`name`';
                           $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                           $arr['list'][]=array(
                               'id'=>$v1['id'],
                               'title'=>$v1['title'],
                               'applyuserid'=>$v1['uid'],
                               'applyuser'=>$v1['name'],
                               'applyheadicon'=>FACE.$v1['face'],
                               'money'=>$sum,
                               'status'=>$v1['fstate'],
                               'ispayed'=>$v1['ispayed'],
                               'audituserid'=>$v1['nowid'],
                               'audituser'=>$rs['name'],
                               'publishtime'=>strtotime($v1['date']),
                               'updatetime'=>strtotime($v1['opdate']),
                           );

                       }

                   }else{
                       $arr=array(
                           'code'=>200,
                           'msg'=>'成功',
                           'isend'=>true,
                           'list'=>null,
                       );
                   }
                   $this->returnjson($arr);

               }

           }
       }


}





//6-14我需要支付的报销单列表
public function myRequiredPayedListAction(){
  //远端测试
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
    // $payFilter=$this->post('payFilter');
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
       $payFilter=$data['payFilter'];
       $memberId=$data['memberId'];

    //计算全部符合条件的总数,实现分页
    $t_sql='select *from hrt_consume_process  where fstate=2  and  ispayed=0  and  uid='.$uid;
    $t_r=$this->db->query($t_sql);
   @$total=mysqli_num_rows($t_r);
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
        //表示查看全部时间内符合条件的信息
        if($payFilter==null){
            //查看全部状态
            if($memberId==null){
                //没有指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and  a.teller='.$uid .' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }else{

                //指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2  and  a.teller='.$uid .' and b.uid='.$memberId.'  order by  a.opdate desc  limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);
            }


        }else if($payFilter=1){
            //正在处理的
            if($memberId==null){
                //没有指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=0 and a.teller='.$uid.'  order by a.opdate desc  limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }else{
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=0  and a.teller='.$uid .' and b.uid='.$memberId.' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }

        }else{
            //审批完成已支付的
            if($memberId==null){
                //没有指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=1 and a.teller='.$uid.' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }else{
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=1 and a.teller='.$uid .' and b.uid='.$memberId.' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }
            $this->returnjson($arr);
        }

    }else{
        if($payFilter==null){
            //查看全部状态
            if($memberId==null){
                //没有指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.teller='.$uid .' and b.date like '."'$date%'".' order by a.opdate desc  limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }else{

                //指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.teller='.$uid .' and b.uid='.$memberId.' and b.date like '."'$date%'".' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);
            }


        }else if($payFilter=1){
            //正在处理的
            if($memberId==null){
                //没有指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=0 and a.teller='.$uid.' and b.date like '."'$date%'".' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }else{
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=0  and a.teller='.$uid .' and b.uid='.$memberId.' and b.date like '."'$date%'".' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }

        }else{
            //审批完成已支付的
            if($memberId==null){
                //没有指定人
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=1 and a.teller='.$uid.' and b.date  like '."'$date%'".' order by a.opdate desc limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }else{
                $sql1='select b.id,b.title,b.uid,c.`name`,c.face,
                        a.fstate,a.ispayed,a.nowid,b.date,a.opdate,b.list
                        from hrt_consume_process  a
                        left join hrt_consume_bill b  on  a.bid=b.id
                        left join hrt_admin c  on b.uid=c.id
                        where a.fstate=2 and a.ispayed=1 and a.teller='.$uid .' and b.uid='.$memberId.' and b.date like '."'$date%'".' limit '.$limit;
                $r1=$this->db->query($sql1);
                if($r1 && mysqli_num_rows($r1)>0){
                    foreach($r1 as $v1){
                        $listArr=explode(',',$v1['list']);
                        $sum=0;
                        foreach($listArr as $l){
                            $sum_sql='select money  from hrt_consuming_records
                                 where id='.$l;
                            $r_s=$this->db->query($sum_sql);
                            foreach($r_s as $vvs){
                                $sum +=$vvs['money'];
                            }
                        }
                        //审核人的姓名
                        $arrs=array(
                            'id'=>$v1['nowid'],
                        );
                        $fields='`name`';
                        $rs=$this->db->getone('[Q]admin',$arrs,$fields);
                        $arr['list'][]=array(
                            'id'=>$v1['id'],
                            'title'=>$v1['title'],
                            'applyuserid'=>$v1['uid'],
                            'applyuser'=>$v1['name'],
                            'applyheadicon'=>FACE.$v1['face'],
                            'money'=>$sum,
                            'status'=>$v1['fstate'],
                            'ispayed'=>$v1['ispayed'],
                            'audituserid'=>$v1['nowid'],
                            'audituser'=>$rs['name'],
                            'publishtime'=>strtotime($v1['date']),
                            'updatetime'=>strtotime($v1['opdate']),
                        );

                    }

                }else{
                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                        'isend'=>true,
                        'list'=>null,
                    );
                }
                $this->returnjson($arr);

            }
            $this->returnjson($arr);
        }

    }




}
//6-15报销单详情
public  function BillDetailsAction(){
        // $id=$this->post('id');

     $data=json_decode($this->post('data'),true);
     $id=$data['id'];


        $sql='SELECT c.`name`,c.face,a.uid,a.date,a.title,a.subsidy,
                a.remark,a.code,b.fstate,b.nowid,b.ispayed,a.list,b.teller
                from hrt_consume_bill   a
                left JOIN hrt_consume_process b on a.id=b.bid
                left join hrt_admin c on c.id=a.uid
                where a.id='.$id;
        $r=$this->db->query($sql);
        if($r && mysqli_num_rows($r)>0){

            foreach($r as $v){
                $listArr=explode(',',$v['list']);
                $sum=0;
                foreach($listArr as $l){
                    $sum_sql='select a.id,b.icon,a.money,a.type,b.name,a.img,
                      a.remark,a.date
                      from hrt_consuming_records a
                      left join  hrt_consumetype  b  on a.type=b.id
                      where a.id='.$l;

                    $r_s=$this->db->query($sum_sql);
                    $row=mysqli_fetch_array($r_s);

                 // 输出图片
                     $iArr=json_decode($row['img'],true);
                     $imgArr=array();
                     foreach($iArr as $img){
                      $imgArr[]=FACE.$img;  

                     }

                    foreach($r_s as $vvs){

                        $sum +=$vvs['money'];//报销单总的金额
                        $sum=$sum+$v['subsidy'];
                        $rArr[]=array(
                            'id'=>$vvs['id'],
                            'icon'=>FACE.$vvs['icon'],
                            'money'=>$vvs['money'],
                            'typeid'=>$vvs['type'],
                            'typename'=>$vvs['name'],
                            'imagelist'=>$imgArr,
                            'remark'=>$vvs['remark'],
                            'consumetime'=>$vvs['date'],
                        );
                    }
                }
                //报销的审批过程步骤记录
                $step_sql='SELECT a.uid,a.`status`,a.remark,a.opdate,b.name,b.face
                      from hrt_consume_result   a
                      left JOIN  hrt_admin  b   on a.uid=b.id
                      where    a.cid='.$id.' order by a.opdate desc';
                $s_r=$this->db->query($step_sql);
                foreach($s_r as $s_v){

                    if($s_v['status']==5){
                        $stepArr[]=array(
                            'stepstatus'=>4,
                            'content'=>'完成',
                            'ispayed'=>0,
                        );


                    }else if($s_v['status']==4){
                        $stepArr[]=array(
                            'stepstatus'=>4,
                            'content'=>'完成',
                            'ispayed'=>1,
                        );
                    }else if($s_v['status']==3){
                        $stepArr[]=array(
                            'stepstatus'=>3,
                            'operateuserid'=>$s_v['uid'],
                            'operateuser'=>$s_v['name'],
                            'operateuserheadicon'=>FACE.$s_v['face'],
                            'updatetime'=>strtotime($s_v['opdate']),
                        );
                    }else if($s_v['status']==6){
                      //开始审核
                    	 $stepArr[]=array(
                            'stepstatus'=>2,
                            'operateuserid'=>$s_v['uid'],
                            'operateuser'=>$s_v['name'],
                            'status'=>0,
                            'operateuserheadicon'=>FACE.$s_v['face'],
                            'updatetime'=>strtotime($s_v['opdate']),
                        );
                    }else{
                        $stepArr[]=array(
                            'stepstatus'=>2,
                            'operateuserid'=>$s_v['uid'],
                            'operateuser'=>$s_v['name'],
                            'status'=>$s_v['status'],
                            'cause'=>$s_v['remark'],
                            'operateuserheadicon'=>FACE.$s_v['face'],
                            'updatetime'=>strtotime($s_v['opdate']),
                        );
                    }
                }

                $stepArr[]=array(
                    'stepstatus'=>0,
                );
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'headiconurl'=>FACE.$v['face'],
                    'truename'=>$v['name'],
                    'publishuserid'=>$v['uid'],
                    'publishtime'=>strtotime($v['date']),
                    'title'=>$v['title'],
                    'cashierid'=>$v['teller'],
                    'money'=>$sum,
                    'subsidy'=>$v['subsidy'],
                    'remark'=>$v['remark'],
                    'ordercode'=>$v['code'],
                    'status'=>$v['fstate'],
                    'audituserid'=>$v['nowid'],
                    'ispayed'=>$v['ispayed'],
                    'consumelist'=>$rArr,
                    'auditsteplist'=>$stepArr,
                );
            }

        }else{
            $this->showreturn('','失败',201);
        }

        $this->returnjson($arr);



}

//6-16同意报销单
 public  function passAuditAction(){
     // $uid=$this->post('userId');
     // $id=$this->post('id');
     // $remark=$this->post('remark');
     // $opdate=date('Y-m-d H:i:s',time());


        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $id=$data['id'];
        $remark=$data['remark'];
        $opdate=date('Y-m-d H:i:s',time());
        $n_opdate=date('Y-m-d H:i:s',time()+1);


        $arr0=array(
            'status'=>2,
            'remark'=>$remark,
            'opdate'=>$opdate,
        );
        $field=array(
        	'cid'=>$id,
        	'uid'=>$uid,
          'status'=>6,
        	);
        $r0=$this->db->record('[Q]consume_result',$arr0,$field);
        if($r0){
                $arr1=array(
                    'bid'=>$id,
                    'nowid'=>$uid,

                );
                $field1='`nlist`,`teller`';
                $r1=$this->db->getone('[Q]consume_process',$arr1);
                $list=$r1['nlist'];
                $listArr=explode(',',$list);
                $newArr=array_shift($listArr);//第一个
                @$ns=$listArr[0];
                $n=implode(',',$listArr);
                if($n!=''){
                    $arr2=array(
                    	'nowstatus'=>0,
                        'nowid'=>$ns,
                        'nlist'=>$n,
                        'opdate'=>$opdate,
                    );
                      //在result中创建下一个审核人开始的状态
                    $arr_next=array(
                        'cid'=>$id,
                        'uid'=>$ns,
                        'status'=>6,
                        'opdate'=>$n_opdate,
                    );
                    $r_next=$this->db->record('[Q]consume_result',$arr_next);
                    
                }else{
                    $ls=$r1['nlist'];
                    $listArrs=explode(',',$ls);
                    $last=array_pop($listArrs);
                    $arr2=array(
                        'nowid'=>$last,
                        'nlist'=>0,
                        'nowstatus'=>2,
                        'fstate'=>2,
                        'opdate'=>$opdate,
                    );
                    //全部审核完成的情况下将完成未支付的状态记录于result表中
                    $arr00=array(
                        'status'=>2,
                    );
                    $f00=array(
                        'cid'=>$id,
                        'uid'=>$last,
                    );

                    $r00=$this->db->record('[Q]consume_result',$arr00,$f00);

                   
                     $arr0_0=array(
                        'status'=>5,
                        'cid'=>$id,
                        'uid'=>$last,
                        'opdate'=>$n_opdate,
                    );
                    $r0_0=$this->db->record('[Q]consume_result',$arr0_0);

                    // 全部审核完成推送报销单完成状态通知
                    $user_sende='admin';
                    // 查询出谁的报销单
                    $p_sql='SELECT *from hrt_consume_bill where id='.$id;
                    $p_r=$this->db->query($p_sql);
                    $row=mysqli_fetch_array($p_r);
                    $to_users='user'.$row['uid'];
                    $summary='您的报销单已经审批通过';
                    $content='您的报销单已经审批通过';

                    $push=$this->businessnews($user_sende,$to_users,$type=5,$id,$summary,$content);
                    
                    // 友盟推送待支付报销单
                    // 查询发布人的名称
                    $p_sql='SELECT a.name as name,b.title  from hrt_admin a  left join hrt_consume_bill b  on a.id=b.uid 
                    where b.id='.$id;
                    $p_r=$this->db->query($p_sql);
                    $pp_r=mysqli_fetch_array($p_r);
                    $ticker=$text=$pp_r['name'].'的'.$pp_r['title'];
                    $y_push=$this->android($r1['teller'],$ticker,'待支付报销单',$text,5);


                }
            $r2=$this->db->record('[Q]consume_process',$arr2,"`bid`='".$id."'");
            if($r2){
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

//6-17驳回报销单
public  function  unpassAuditAction(){
        // $uid=$this->post('userId');
        // $id=$this->post('id');
        // $remark=$this->post('remark');
        // $opdate=date('Y-m-d H:i:s',time());

    $data=json_decode($this->post('data'),true);
    $uid=$data['userId'];
    $id=$data['id'];
    $remark=$data['remark'];
    $opdate=date('Y-m-d H:i:s',time());

        $arr0=array(
            'status'=>1,
            'remark'=>$remark,
            'opdate'=>$opdate,
        );
        $field0=array(
        	'cid'=>$id,
        	'uid'=>$uid,
        	);
        $r0=$this->db->record('[Q]consume_result',$arr0,$field0);
        if($r0){
            $arr1=array(
                'fstate'=>1,
                'opdate'=>$opdate,
                'nowstatus'=>1,
            );
            $field1=array(
                'bid'=>$id,
                'nowid'=>$uid,

            );
            $r1=$this->db->record('[Q]consume_process',$arr1,$field1);
            if($r1){
                 // 全部审核完成推送报销单完成状态通知
                    $user_sende='admin';
                    // 查询出谁的报销单
                    $p_sql='SELECT *from hrt_consume_bill where id='.$id;
                    $p_r=$this->db->query($p_sql);
                    $row=mysqli_fetch_array($p_r);
                    $to_users='user'.$row['uid'];
                    $summary='您的报销单已经被驳回';
                    $content='您的报销单已经被驳回';

                    $push=$this->businessnews($user_sende,$to_users,$type=5,$id,$summary,$content);
               


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
//    6-18撤销(删除)报销单
public function deleteAuditAction(){
     // $id=$this->post('id');


   $data=json_decode($this->post('data'),true);
   $id=$data['id'];

    $arr1=array(
        'id'=>$id,
    );
    $r1=$this->db->delete('[Q]consume_bill',$arr1);
    if($r1){
        $arr2=array(
           'bid'=>$id,
        );
        $r2=$this->db->delete('[Q]consume_process',$arr2);
        if($r2){
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
//6-19修改报销单
public  function editAuditAction(){
       // $id=$this->post('id');
       //  $uid=$this->post('userId');
       // $title=$this->post('title');
       // $list=json_decode($this->post('consumeList'),true);
       // $lists=implode(',',$list);
       // $subsidy=$this->post('subsidy');
       // $remark=$this->post('remark');
       //  $date=date('Y-m-d',time());
       //  $dates=date('Y-m-d H:i:s',time());

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $id=$data['id'];
       $title=$data['title'];
       $list=$data['consumeList'];
       $lists=implode(',',$list);
       $subsidy=$data['subsidy'];
       $remark=$data['remark'];
       $date=date('Y-m-d H:i:s',time());
       $dates=date('Y-m-d H:i:s',time()+1);

//      1.先获取原来的报销单的信息将消费记录全部标记为0
         $arr1=array(
             'id'=>$id,
         );
        $f1='`list`';
        $r1=$this->db->getone('[Q]consume_bill',$arr1,$f1);
        $rArr=explode(',',$r1['list']);
        foreach($rArr as $v1){

            $arr1_1=array(
                'status'=>0,
            );
            $r1_1=$this->db->record('[Q]consuming_records',$arr1_1,"`id`='$v1'");

        }

       //1-1.把新的消费记录的状态改为1
        foreach($list as $v1_2){

            $arr1_2=array(
                'status'=>1,

            );
            $r1_2=$this->db->record('[Q]consuming_records',$arr1_2,"`id`='$v1_2'");

        }

//        2.更新报销单据表数据
          $arr2=array(
              'title'=>$title,
              'list'=>$lists,
              'subsidy'=>$subsidy,
              'remark'=>$remark,
              'date'=>$date,
          );
        $r2=$this->db->record('[Q]consume_bill',$arr2,"`id`='$id'");
        if($r2){
//            3.将审核进程表中数据初始化


            $arr3=array(
                'id'=>$uid,
                );
            $field3='`approver`,`teller`';
            $r3=$this->db->getone('[Q]admin',$arr3);
            $sArr=explode(',',$r3['approver']);
            $nowid=$sArr[0];
            $arr3_1=array(
                'list'=>$r3['approver'],
                'nowid'=>$nowid,
                'nowstatus'=>0,
                'nlist'=>$r3['approver'],
                'fstate'=>0,
                'teller'=>$r3['teller'],
                'ispayed'=>0,
                'opdate'=>$dates,
            );
            $r3_1=$this->db->record('[Q]consume_process',$arr3_1,"`bid`='$id'");
            if($r3_1){
//                4.在审核人统计hrt_consume_result中写入修改记录
                $arr4=array(
                    'cid'=>$id,
                    'uid'=>$uid,
                    'status'=>3,
                    'opdate'=>$dates,
                );
                $r4=$this->db->record('[Q]consume_result',$arr4);
                if($r4){
                    // 重新把第一个人开始审核的状态写入result表中
                    $arr5=array(
                      'cid'=>$id,
                      'uid'=>$nowid,
                      'status'=>6,
                      'opdate'=>date('Y-m-d H:i:s',time()+2),
                      );
                    $r5=$this->db->record('[Q]consume_result',$arr5);
                    if($r5){
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
                $this->showreturn('','失败',201);
            }
        }else{
            $this->showreturn('','失败',201);
        }


}
//    6-20标记报销单为已支付状态
public  function markpayedAuditAction(){
        // $id=$this->post('id');
        // $uid=$this->post('userId');

       $data=json_decode($this->post('data'),true);
       $id=$data['id'];
       $uid=$data['userId'];


//        1.先将进程表中的状态改为已支付
        $arr1=array(
            'ispayed'=>1,
        );
        $r1=$this->db->record('[Q]consume_process',$arr1,"`bid`='$id'");
        if($r1){
//                2.在统计表中输入此条记录.
                $arr2=array(
                    'uid'=>$uid,
                    'status'=>4,
                    'opdate'=>date('Y-m-d H:i:s',time()),
                );
                $f2=array(
                    'cid'=>$id,
                    'status'=>5,
                );
                $r2=$this->db->record('[Q]consume_result',$arr2,$f2);
                if($r2){

                    // 第三方推送消息
                    $user_sende='admin';
                    $p_sql='SELECT *from hrt_consume_bill where id='.$id;
                    $p_r=$this->db->query($p_sql);
                    $row=mysqli_fetch_array($p_r);
                    $to_users='user'.$row['uid'];
                    $summary='你的报销单已支付';
                    $content='你的报销单已支付';
                    $push=$this->businessnews($user_sende,$to_users,$type=5,$id,$summary,$content);


                    $arr=array(
                        'code'=>200,
                        'msg'=>'成功',
                    );
                    $this->returnjson($arr);
                }else{
                    $this->showreturn('','成功',201);
                }

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
