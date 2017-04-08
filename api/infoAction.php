<?php
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
//通知模块接口
class infoClassAction extends apiAction{
//       2-1.获取有允许发通知权限的成员列表
        public function getmemberListAction(){
    
             $arr0=array(
                  'isinfor'=>1,
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
//  2-2添加有权限的成员
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
                  $isinfor=1;
                  if($isall==1){

                      $result=$this->db->record('[Q]admin',"`isinfor`='".$isinfor."'","`isinfor`=0");
                          if($result){
                            // 第三方平台推送
                            $user_sende='admin';

                            $p_sql='SELECT *from hrt_admin';
                            $p_r=$this->db->query($p_sql);
                            foreach ($p_r as $key => $value) {
                                $to_users[]='user'.$value['id'];
                            }

                            $summary='您已获得发送通知的权限';
                            $content='您已获得发送通知的权限';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=4,1,$summary,$content);


                              $this->returnjson($arr);
                          }else{
                             $this->showreturn('','失败',201);
                          }
                  }else{
                      foreach($userlist as $val){
                              $arr0=array(
                                  'id'=>$val,
                              );
                        $results=$this->db->record('[Q]admin',"`isinfor`='".$isinfor."'","`id`='".$arr0['id']."'");

                            $to_users[]='user'.$val;
                      }
                        // 第三方平台推送
                             $user_sende='admin';

                            $summary='您已获得发送通知的权限';
                            $content='您已获得发送通知的权限';

                            $push=$this->custmsgpush($user_sende,$to_users,$type=4,1,$summary,$content);

                          $this->returnjson($arr);
                  }

       }

//    2-3.移除有权限的成员
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
             $isinfor=0;
             $result=$this->db->record('[Q]admin',"`isinfor`='".$isinfor."'","`id`='".$removedUserId."'");
             if($result){
                //第三方推送
                $user_sende='admin';
                $to_users='user'.$removedUserId;

                $summary='您的发送通知权限已被移除';
                $content='您的发送通知权限已被移除';

                $push=$this->custmsgpush($user_sende,$to_users,$type=4,0,$summary,$content);


                 $this->returnjson($arr);
             }else{
                $this->showreturn('','失败',201);
             }

     }
    //2-4.发布通知接口
     public function publishInfoAction(){
            //远端测试用
         // $userid=$this->post('userId');
         // $title=$this->post('title');
         // $content=$this->post('content');
         // $imageList=$this->post('imageList');
         // $location=$this->post('locationInfo');
         // $feedbackInfo=$this->post('feedbackInfo');
         // $isall=$this->post('isSendToAllUser');
         // $send=json_decode($this->post('sendToUserList'),true);

           $data=json_decode($this->post('data'),true);
           $userid=$data['userId'];
           $title=$data['title'];
           $content=$data['content'];
           $imageList=json_encode($data['imageList'],true);
           $location=json_encode($data['locationInfo'],JSON_UNESCAPED_UNICODE);
           $feedbackInfo=$data['feedbackInfo']['question'];
           $enddt=$data['feedbackInfo']['endDate'];
           $isall=$data['isSendToAllUser'];
           $send=$data['sendToUserList'];
           // 根据uid查询出名字
           $sql='SELECT name from hrt_admin where id='.$userid;
           $r=$this->db->query($sql);
           $row=mysqli_fetch_array($r);
           // echo $row['name'];

        if($send==null){
                $arr0=array(
                     'title'=>$title,
                     'typename'=>'通知公告',
                     'content'=>$content,
                     'imglist'=>$imageList,
                     'location'=>$location,
                     'feedback'=>$feedbackInfo,
                     'optid'=>$userid,
                     'optname'=>$row['name'],
                     'indate'=>date('Y-m-d H:i:s',time()),
                     'pdate'=>time(),
                     'enddt'=>$enddt,
              );
            
        }else{
              $arr0=array(
                   'title'=>$title,
                   'typename'=>'通知公告',
                   'content'=>$content,
                   'imglist'=>$imageList,
                   'location'=>$location,
                   'feedback'=>$feedbackInfo,
                   'optid'=>$userid,
                    'optname'=>$row['name'],
                     'indate'=>date('Y-m-d H:i:s',time()),
                   'pdate'=>time(),
                   'enddt'=>$enddt,
                   'userlist'=>json_encode($send,true),
             );
        
        }
         
         $p_info=$this->db->record('[Q]infor',$arr0);

         //返回通知新插入通知的id
        
         $num=$this->db->insert_id();
     		
        
         if($isall==1){
               //选择全部成员
               $allmember=$this->db->getrows('[Q]admin','','`id`');
               foreach($allmember as $val){
                   $arr1=array(
                       'pid'=>$num,
                       'uid'=>$userid,
                       'rid'=>$val['id'],
                   );
     
                   $result2=$this->db->record('[Q]inforead',$arr1);
                  // // 推送
                   $ticker=$text=$content;
                   $push=$this->android($val['id'],$ticker,'通知',$text,1);

               }
          		   if($result2){
	                	$arr=array(
			                    'code'=>200,
			                    'msg'=>'成功',
				       );
			              $this->returnjson($arr);
            		  }
         }else{
               //如果不是全部成员,根据情况做出判断
         
                // var_dump($send);
                foreach($send as $v){
                    $arrlist=array(
                         'type'=>$v['type'],
                         'id'=>$v['id'],
                     );
          
             
                 //根据传过来的type判断是否为整个部门
                  if($arrlist['type']==1){//表示为单个成员
                      $arr2=array(
                          'pid'=>$num,
                          'uid'=>$userid,
                          'rid'=>$v['id'],
                      );
//                      $result0=$this->db->record('[Q]infocomment',$arr2);
                      $result00=$this->db->record('[Q]inforead',$arr2);
                         // 推送
                   $ticker=$text=$content;
                   $push=$this->android($v['id'],$ticker,'通知',$text,1);
                   
                     
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
                                  'pid'=>$num,
                                  'uid'=>$userid,
                                  'rid'=>$vvv['id'],
                              );
//                              $r0=$this->db->record('[Q]infocomment',$arr4);
                              $r00=$this->db->record('[Q]inforead',$arr4);
                      //     	       // 推送
			                   $ticker=$text=$content;
			                   $push=$this->android($vvv['id'],$ticker,'通知',$text,1);
                          }
                     }
                      //不是子部门的所有成员
                      $arr5=array(
                          'deptid'=>$arrlist['id'],
                      );
                      $d=$this->db->getrows('[Q]admin',$arr5,'`id`');
                      foreach($d as $v1){
                           $arr6=array(
                               'pid'=>$num,
                               'uid'=>$userid,
                               'rid'=>$v1['id'],
                           );
//                          $r1=$this->db->record('[Q]infocomment',$arr6);
                          $r11=$this->db->record('[Q]inforead',$arr6);
                      //          // 推送
			                   $ticker=$text=$content;
			                   $push=$this->android($v1['id'],$ticker,'通知',$text,1);
                      }
                    }
                      
                  }
                   $arr=array(
                            'code'=>200,
                            'msg'=>'成功',
                      );
                  $this->returnjson($arr);
            }
      
   
    }

//   2-5我发送的通知列表
     public function mytoinfoListAction(){
         //远端测试使用
          // $userid=$this->post('userId');
          // $pageNo=$this->post('pageNo');
          // $pageSize=$this->post('pageSize');


         $data=json_decode($this->post('data'),true);
         $pageNo=$data['pageNo'];
         $pageSize=$data['pageSize'];
         $userid=$data['userId'];

         $t_sql='SELECT  *from hrt_infor  where optid='.$userid;
         $t_result=$this->db->query($t_sql);
         $total=mysqli_num_rows($t_result);
         $eNum=ceil($total/$pageSize);
         $limit=(($pageNo-1)*$pageSize).",".$pageSize;

         //1 查询出我发送的20条数据
         $sql1="SELECT id from hrt_infor WHERE optid=$userid  limit $limit";

         $row_1=$this->db->query($sql1);
         $idArr = array();
         foreach($row_1 as $r) {
             $idArr[] = $r['id'];
         }
         $ids = implode(',',$idArr);

         //2 将评论数据条数写入到临时表中
         $onlyid = date('hi').rand(10,99);

         $sql_temp="insert into hrt_infocommenttemp (onlyid,pid,uid,evaluatenum)
select $onlyid,pid,uid,count(1) from hrt_infocomment  where pid in ($ids) group by pid  ";

         $result=$this->db->query($sql_temp);


         //3
         $sql="SELECT a.id,a.title,a.content,
                a.pdate,ifnull(sum(isread),0) as readnum ,ifnull(t.evaluatenum,0) as evaluatenum
                from hrt_infor as a
                left JOIN  hrt_inforead as b
                ON a.id=b.pid
                left JOIN  hrt_infocommenttemp as t
                ON a.id=t.pid  WHERE a.optid=$userid  group by a.id  order by a.pdate desc ";

         $row=$this->db->query($sql);

         $arr1=array(
             'id'=>$userid,
         );

         $rows=$this->db->getone('[Q]admin',$arr1,'`face`');
       
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
         foreach($row as $val){

             $arr['noticelist'][]=array(
                 'id'=>$val['id'],
                 'headiconurl'=>FACE.$rows['face'],
                 'title'=>$val['title'],
                 'content'=>$val['content'],
                 'readnum'=>$val['readnum'],
                 'evaluatenum'=>$val['evaluatenum'],
                 'publishtime'=>$val['pdate'],
             );
             $sql_temp_d="delete from hrt_infocommenttemp where onlyid=$onlyid";
             $dele=$this->db->query($sql_temp_d);
         }

         $this->returnjson($arr);

     }
//  2-6我收到的通知列表
           public function myreceivedListAction(){
                //远端测试用
               // $userid=$this->post('userId');
               // $pageNo=$this->post('pageNo');
               // $pageSize=$this->post('pageSize');

              $data=json_decode($this->post('data'),true);
              $userid=$data['userId'];
              $pageNo=$data['pageNo'];
              $pageSize=$data['pageSize'];

	       	   $t_sql="SELECT * from hrt_infor 
				      	where id in  (select pid from hrt_inforead where rid=$userid)";
	           $t_result=$this->db->query($t_sql);
	           $total=mysqli_num_rows($t_result);
	           $eNum=ceil($total/$pageSize);
             $limit=(($pageNo-1)*$pageSize).",".$pageSize;


               //计算出我收到的所有信息中未读信息的条数
               $w_sql="SELECT count(id) as count  from hrt_infor 
					where id in  (select pid from hrt_inforead where rid=$userid and isread is null) ";
               $w_r=$this->db->query($w_sql);
               $r_w=mysqli_fetch_array($w_r);
               // echo $r_w['count'];   未读消息的数量
    
                if($pageNo>=$eNum){
	               $arr=array(
	                   'code'=>200,
	                   'msg'=>'成功',
	                   'isend'=>true,
	                   'unreadcount'=>$r_w['count'],
	               );
	           }else{
	               $arr=array(
	                   'code'=>200,
	                   'msg'=>'成功',
	                   'isend'=>false,
	                   'unreadcount'=>$r_w['count'],
	               );
	           }
              $sql="select *from hrt_infor where id in
                 (select pid from hrt_inforead where rid=$userid)";

              $row_1=$this->db->query($sql);

               //2 将评论数据条数写入到临时表中
               $idArr = array();
               foreach($row_1 as $r) {
                   $idArr[] = $r['id'];
               }
               $ids = implode(',',$idArr);

               //2 将评论数据条数写入到临时表中
               $onlyid = date('hi').rand(10,99);

               $sql_temp="insert into hrt_infocommenttemp (onlyid,pid,uid,evaluatenum)
               select $onlyid,pid,uid,count(1) from hrt_infocomment  where pid in ($ids) group by pid  ";

               $result=$this->db->query($sql_temp);
               //3
               $sql="SELECT a.id,a.title,a.content,
                a.pdate,ifnull(sum(isread),0) as readnum ,ifnull(t.evaluatenum,0) as evaluatenum, b.isread as isread
                from hrt_infor as a
                left JOIN  hrt_inforead as b
                ON a.id=b.pid
                left JOIN  hrt_infocommenttemp as t
                ON a.id=t.pid  where a.id in($ids) and b.rid=$userid  group by b.pid  order by a.pdate desc  limit $limit";


               $row=$this->db->query($sql);

               $arr1=array(
                   'id'=>$userid,
               );
               $rows=$this->db->getone('[Q]admin',$arr1,'`face`');

               foreach($row as $val){
                   
                    if($val['isread']==1){
                       $arr['noticelist'][]=array(
                           'id'=>$val['id'],
                           'headiconurl'=>FACE.$rows['face'],
                           'title'=>$val['title'],
                           'content'=>$val['content'],
                           'readnum'=>$val['readnum'],
                           'evaluatenum'=>$val['evaluatenum'],
                           'publishtime'=>$val['pdate'],
                           'isread'=>1,
                       );
                   }else{
                       $arr['noticelist'][]=array(
                           'id'=>$val['id'],
                           'headiconurl'=>FACE.$rows['face'],
                           'title'=>$val['title'],
                           'content'=>$val['content'],
                           'readnum'=>$val['readnum'],
                           'evaluatenum'=>$val['evaluatenum'],
                           'publishtime'=>$val['pdate'],
                           'isread'=>0,
                       );
                   }
                   $sql_temp_d="delete from hrt_infocommenttemp where onlyid=$onlyid";
                   $dele=$this->db->query($sql_temp_d);
               }

               $this->returnjson($arr);
}
  //2-7查看通知详情
    public function  inforDetailAction(){
         // $id=$this->post('id'); 
         // $uid=$this->post('userId');

      $data=json_decode($this->post('data'),true);
      $id=$data['id'];
      $uid=$data['userId'];
      $r_sql="UPDATE hrt_inforead SET isread=1 where pid=$id and rid=$uid ";
      $r_re=$this->db->query($r_sql);
//       1 联表查询出基本的数据[]
         $arr=array();
         $sql1='SELECT  a.title ,a.optid ,a.pdate,a.content,a.imglist,a.location ,b.name ,a.userlist ,a.feedback,a.enddt from hrt_infor as a
                  LEFT JOIN hrt_admin as b  on a.optid=b.id
                where a.id='.$id;
         $basic_info=$this->db->query($sql1);
         $row=mysqli_fetch_array($basic_info);
       

         if($row){
            //列出通知的评论数量
             $c_sql='SELECT count(1) as count  from hrt_infocomment where pid='.$id;
             $r_n=$this->db->query($c_sql);
             $num=mysqli_fetch_array($r_n);
            //列出已看该通知的人数
             $isread_sql="SELECT count(1) as count from hrt_inforead where pid=$id and isread=1 ";
             $read=$this->db->query($isread_sql);
             $r_num=mysqli_fetch_array($read);
             //列出未看该通知的人数
             $unread_sql="SELECT count(1) as count from hrt_inforead where pid=$id and isread is null ";
             $unread=$this->db->query($unread_sql);
             $un_num=mysqli_fetch_array($unread);
             define('QU',$row['feedback']);

  			 // 输出图片
             $iArr=json_decode($row['imglist'],true);

             foreach($iArr as $img){
             	$imgArr[]=FACE.$img;	

             }

             //判断反馈
             if($row['feedback']==null){
                 //没有反馈问题
                 
                 $arr=array(
                     'code'=>200,
                     'msg'=>'成功',
                     'title'=>$row['title'],
                     'publishuserid'=>$row['optid'],
                     'publishtruename'=>$row['name'],
                     'publishtime'=>$row['pdate'],
                     'content'=>$row['content'],
                     'imageurllist'=>$imgArr,
                     'locationinfo'=>json_decode($row['location'],true),
                     'evaluatenum'=>$num['count'],
                     'readnum'=>$r_num['count'],
                     'unreadnum'=>$un_num['count'],
                     'feedbackinfo'=>null,
                 );
             }else{
                 //有反馈问题

                 $fb_sql="select *from hrt_inforead where pid=$id and rid=$uid";

                 $fb_result=$this->db->query($fb_sql);
                 $fb_row=mysqli_fetch_array($fb_result);

                 $arr=array(
                     'code'=>200,
                     'msg'=>'成功',
                     'title'=>$row['title'],
                     'publishuserid'=>$row['optid'],
                     'publishtruename'=>$row['name'],
                     'publishtime'=>$row['pdate'],
                     'content'=>$row['content'],
                     'imageurllist'=>$imgArr,
                     'locationinfo'=>json_decode($row['location'],true),
                     'evaluatenum'=>$num['count'],
                     'readnum'=>$r_num['count'],
                     'unreadnum'=>$un_num['count'],

                 );
                 $arr['feedbackinfo']=array();
                 if($fb_row['isfeedback']==1){
                     $arr['feedbackinfo']['status']=1;
                     $arr['feedbackinfo']['endtime']=strtotime($row['enddt']);
                     $arr['feedbackinfo']['question']=QU;
                     $arr['feedbackinfo']['value']=$fb_row['fbcontent'];

                 }
                 else{
                     $arr['feedbackinfo']['status']=0;
                     $arr['feedbackinfo']['endtime']=strtotime($row['enddt']);
                     $arr['feedbackinfo']['question']=QU;
                     $arr['feedbackinfo']['value']=$fb_row['fbcontent'];
                 }


             }

              // print_r($row['userlist']);

             //判断是否发送给全部
             $arr['issendtoall']=$row['userlist']==NULL?1:0;

             //显示发送的成员和部门信息
             $arr['userlist']=array();
             $mArr=json_decode($row['userlist'],true);
          


             foreach($mArr as $m){
                  $arr1=array(
                      'type'=>$m['type'],
                      'id'=>$m['id'],
                  );

                 if($arr1['type']==2){
                     $sql_1='select name from hrt_dept where id='.$arr1['id'];

                     $dept_n=$this->db->query($sql_1);
                     $d1=mysqli_fetch_array($dept_n);

                     $arr['userlist'][]=array(
                         'type'=>$arr1['type'],
                         'id'=>$arr1['type'],
                         'name'=>$d1['name'],
                     );


                 }else{
                     $sql_2='select name,face from hrt_admin where id='.$arr1['id'];
                     $dept_m=$this->db->query($sql_2);
                     $d2=mysqli_fetch_array($dept_m);
                     $arr['userlist'][]=array(
                         'type'=>$arr1['type'],
                         'id'=>$arr1['type'],
                         'name'=>$d2['name'],
                         'headicon'=>FACE.$d2['face'],
                     );
                 }
             }

             $this->returnjson($arr);


         }else{
            $this->showreturn('','失败',201);
         }



    }
  //2-8针对该通知发布评论
    public  function publishCommentAction(){
         //远端测试用
        // $uid=$this->post('userId');
        // $id=$this->post('id');
        // $content=$this->post('content');

       $data=json_decode($this->post('data'),true);
       $id=$data['id'];
       $content=$data['content'];
       $uid=$data['userId'];

        $arr0=array(
            'id'=>$id,
        );
        $field='`optid`';
        $row=$this->db->getone('[Q]infor',$arr0,$field);
        $arr1=array(
            'pid'=>$id,
            'uid'=>$row['optid'],
            'rid'=>$uid,
            'content'=>$content,
            'cdate'=>time(),
        );
        $result=$this->db->record('[Q]infocomment',$arr1);
        if($result){
             $arr=array(
                 'code'=>200,
                 'msg'=>'成功',
             );
            $this->returnjson($arr);
        }else{
           $this->showreturn('','失败0',201);
        }

    }
       //2-9针对该通知的评论列表
     public  function commentListAction(){
        //远端测试
         // $id=$this->post('id');
         // $userid=$this->post('userId');
         // $pageNo=$this->post('pageNo');
         // $pageSize=$this->post('pageSize');



        
        $data=json_decode($this->post('data'),true);
        $id=$data['id'];
        $userid=$data['userId'];
        $pageNo=$data['pageNo'];
        $pageSize=$data['pageSize'];
       
        //查询出该消息的评论总数
           $t_sql='select *from hrt_infocomment  where pid='.$id;
           $t_result=$this->db->query($t_sql);
           $total=mysqli_num_rows($t_result);
           $eNum=ceil($total/$pageSize);
           $limit=(($pageNo-1)*$pageSize).",".$pageSize;




         
         $arr=array();
         $sql="SELECT a.id ,a.rid ,b.face,b.name,a.content ,a.cdate from hrt_infocomment  a
              LEFT JOIN   hrt_admin b  on a.rid=b.id
                where pid=$id  limit  $limit";
         $result=$this->db->query($sql);
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
             foreach($result as $v){
                 $arr['list'][]=array(
                      'id'=>$v['id'],
                      'evaluateuserid'=>$v['rid'],
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
    //2-10已看该通知的成员列表
      public function readListAction(){
             // $uid=$this->post('userId');
             // $id=$this->post('id');
             // $isRead=$this->post('isRead');
             // $pageNo=$this->post('pageNo');
             // $pageSize=$this->post('pageSize');

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $id=$data['id'];
           $isRead=$data['isRead'];
           $pageNo=$data['pageNo'];
           $pageSize=$data['pageSize'];

             if($isRead==1){
                 $t_sql='SELECT rid from hrt_inforead
                        where pid=$id  and isread=1';
                 $t_result=$this->db->query($t_sql);
                 $total=mysqli_num_rows($t_result);
                 $eNum=ceil($total/$pageSize);
                 $limit=(($pageNo-1)*$pageSize).",".$pageSize;

                 //已看该通知的成员列表
                  $y_sql="SELECT rid from hrt_inforead
                        where pid=$id  and isread=1  limit $limit";
                  $y_result=$this->db->query($y_sql);
               
                 $arr=array();
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
              foreach($y_result as $v){
                  $arr1=array(
                      'id'=>$v['rid'],
                  );
                  $field='`id`,`face`,`name`';
                  $result=$this->db->getrows('[Q]admin',$arr1,$field);

                  foreach($result as $vv){

                        $arr['userlist'][]=array(
                            'id'=>$vv['id'],
                            'headiconurl'=>FACE.$vv['face'],
                            'truename'=>$vv['name'],
                        );
                  }

              }
                 echo json_encode($arr);
            }else{

                 $t_sql='SELECT rid from hrt_inforead
                        where pid=$id  and isread is  null';
                 $t_result=$this->db->query($t_sql);
                 $total=mysqli_num_rows($t_result);
                 $eNum=ceil($total/$pageSize);
                 $limit=(($pageNo-1)*$pageSize).",".$pageSize;
               
                //未看该通知的成员列表
                 $n_sql="SELECT rid from hrt_inforead
                    where pid=$id  and isread is null  limit $limit";
                 $n_result=$this->db->query($n_sql);
                 $num=mysqli_num_rows($n_result);
                 $arr=array();

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


                 foreach($n_result as $v_n){
                     $arr1=array(
                         'id'=>$v_n['rid'],
                     );
                     $field='`id`,`face`,`name`';
                     $n_result=$this->db->getrows('[Q]admin',$arr1,$field);

                     foreach($n_result as $vv){

                         $arr['userlist'][]=array(
                             'id'=>$vv['id'],
                             'headiconurl'=>FACE.$vv['face'],
                             'truename'=>$vv['name'],
                         );
                     }

                 }
                 echo json_encode($arr);
             }

      }




//    2-11未看该通知的成员列表
      public   function unreadListAction()
      {
               //2-10中两种判断已经给出
      }

    //2-12提交反馈
     public function submitFeedbackAction(){
          //远程测试使用
         // $uid=$this->post('userId');
         // $id=$this->post('id');
         // $answer=$this->post('answer');

        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $id=$data['id'];
        $answer=$data['answer'];
        $arrs=array(

            'isfeedback'=>1,
            'fbcontent'=>$answer,
        );
         $field=array(
             'rid'=>$uid,
             'pid'=>$id,
         );
       $result=$this->db->record('[Q]inforead',$arrs,$field);
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
    //2-13查看该通知的统计数据
     public function seeInfoDataAction(){
          // $uid=$this->post('userId');
          // $id=$this->post('id');


        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $id=$data['id'];
           $arr0=array(
               'id'=>$id
           );
           $row=$this->db->getone('[Q]infor',$arr0);
           if($row){
               $arr=array(
                   'code'=>200,
                   'msg'=>'成功',
               );
               $sql1="select count(1) as feedbackcount from hrt_inforead
               where pid=$id and  isfeedback=1";

               $row1=$this->db->query($sql1);
               $sql2="select count(1) as nofeedbackcount from hrt_inforead
                where pid=$id and  isfeedback is null";

               $row2=$this->db->query($sql2);
               foreach($row1 as $v){
                   $arr['feedbacktongji']=array(
                       'feedbackcount'=>$v['feedbackcount'],
                   );
               }
               foreach($row2 as $vv){
                   //问题反馈统计
                   $arr['feedbacktongji']=array(
                       'question'=>$row['feedback'],
                       'feedbackcount'=>$arr['feedbacktongji']['feedbackcount'],
                       'nofeedbackcount'=>$vv['nofeedbackcount'],
                   );
               }
               //浏览量统计
               $sql3="select count(1) as readcount from hrt_inforead
               where pid=$id and  isread=1";
               $row3=$this->db->query($sql3);
               foreach($row3 as $v3){
                   $arr['readtongji']=array(
                       'readcount'=>$v3['readcount'],
                   );
               }
               $sql4="select count(1) as unreadcount from hrt_inforead
               where pid=$id and  isread is null ";
               $row4=$this->db->query($sql4);
               foreach($row4 as $v4){
                   //问题反馈统计
                   $arr['readtongji']=array(
                       'readcount'=>$arr['readtongji']['readcount'],
                       'unreadcount'=>$v4['unreadcount'],
                   );
               }
               $this->returnjson($arr);

           }else{
               $this->showreturn('','失败',201);
           }


     }
   //2-14删除通知
    public function deleInforAction(){
        //远端测试用
           // $uid=$this->post('userId');
           // $id=$this->post('id');

        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $id=$data['id'];

        $arrs=array(
            'id'=>$id,
        );
        $result=$this->db->delete('[Q]infor',$arrs);
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

//  2-15 统计带反馈的通知下的反馈列表
          public function feedbackListAction(){
              //远端测试使用
               // $id=$this->post('id');
               // $uid=$this->post('userId');

              $data=json_decode($this->post('data'),true);
              $id=$data['id'];
              $uid=$data['userId'];

             $sql="select b.id,b.face,b.ranking,b.name as truename,c.name as dptname,
             a.fbcontent from hrt_inforead a
             left join hrt_admin as b on a.rid=b.id
             left join hrt_dept c on b.deptid=c.id
             where a.pid=$id and isfeedback=1";

             $result=$this->db->query($sql);

              if($result){

                  $arr=array(
                      'code'=>200,
                      'msg'=>'成功',
              );
                  foreach($result  as $v){
                       $arr['list'][]=array(
                         'user'=>array(
                            'id'=>$v['id'],
                          'headiconurl'=>FACE.$v['face'],
                          'truename'=>$v['truename'],
                          'positionname'=>$v['ranking'],
                          'departmentname'=>$v['dptname'],
                         ),
                           'content'=>$v['fbcontent'],
                       );
                  }
                  $this->returnjson($arr);
              }else{
                  $this->showreturn('','失败',201);
              }

          }
//       2-16通知下未反馈的人数列表
           public function unfeedbackListAction(){
               // $id=$this->post('id');
               // $uid=$this->post('userId');

              $data=json_decode($this->post('data'),true);
              $id=$data['id'];
              $uid=$data['userId'];

               $sql="select b.id,b.face,b.ranking,b.name as truename,c.name as dptname,
             a.fbcontent from hrt_inforead a
             left join hrt_admin as b on a.rid=b.id
             left join hrt_dept c on b.deptid=c.id
             where a.pid=$id and isfeedback is  null";

               $result=$this->db->query($sql);

               if($result){

                   $arr=array(
                       'code'=>200,
                       'msg'=>'成功',
                   );
                   foreach($result  as $v){
                       $arr['userlist'][]=array(

                               'id'=>$v['id'],
                               'headiconurl'=>FACE.$v['face'],
                               'truename'=>$v['truename'],
                               'positionname'=>$v['ranking'],
                               'departmentname'=>$v['dptname'],

                       );
                   }
                   $this->returnjson($arr);
               }else{
                   $this->showreturn('','失败',201);
               }
           }
// <!-------------------------------消息推送----------------------------------------!>
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