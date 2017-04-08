<?php
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');
//12.云盘模块接口
class  clouddiskClassAction extends apiAction{
//    12-1有权限上传至公司共享的成员列表

    public function getmemberListAction(){
        $arr0=array(
            'clouddisk'=>1,
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
    //  12-2添加有权限的成员
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
        $clouddisk=1;
        if($isall==1){

            $result=$this->db->record('[Q]admin',"`clouddisk`='".$clouddisk."'","`clouddisk`=0");
            if($result){
                $user_sende='admin';

                $p_sql='SELECT *from hrt_admin';
                $p_r=$this->db->query($p_sql);
                foreach ($p_r as $key => $value) {
                    $to_users[]='user'.$value['id'];
                }

                $summary='您已获得编辑共享云盘权限';
                $content='您已获得编辑共享云盘权限';

                $push=$this->custmsgpush($user_sende,$to_users,$type=10,1,$summary,$content);
                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
        }else{
            foreach($userlist as $val){
                $arr0=array(
                    'id'=>$val,
                );
                $results=$this->db->record('[Q]admin',"`clouddisk`='".$clouddisk."'","`id`='".$arr0['id']."'");
                 $to_users[]='user'.$val;
            }
             // 第三方平台推送
             $user_sende='admin';

            $summary='您已获得编辑共享云盘的权限';
            $content='您已获得编辑共享云盘的权限';

            $push=$this->custmsgpush($user_sende,$to_users,$type=10,1,$summary,$content);

                         
            $this->returnjson($arr);
        }

    }

//    12-3.移除有权限的成员
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
        $clouddisk=0;
        $result=$this->db->record('[Q]admin',"`clouddisk`='".$clouddisk."'","`id`='".$removedUserId."'");
        if($result){
                $user_sende='admin';
                $to_users='user'.$removedUserId;

                $summary='您的编辑共享云盘权限已被移除';
                $content='您的编辑共享云盘权限已被移除';

                $push=$this->custmsgpush($user_sende,$to_users,$type=10,0,$summary,$content);

            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }

    }
    //12-4个人文件列表
    public  function privateFileListAction(){
            // $uid=$this->post('userId');
            // $pageNo=$this->post('pageNo');
            // $pageSize=$this->post('pageSize');

           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $pageNo=$data['pageNo'];
           $pageSize=$data['pageSize'];
              //计算出总数
            $sql='SELECT *from hrt_files where uid='.$uid.' and type=2';
            $r=$this->db->query($sql);
            $total=mysqli_num_rows($r);
            $eNum=ceil($total/$pageSize);
            $limit=(($pageNo-1)*$pageSize).",".$pageSize;
            if($pageNo>=$eNum){
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'isend'=>true,
                    'baseurl'=>'http://hrtang-cloud.oss-cn-beijing.aliyuncs.com/',

                );
            }else{
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'isend'=>false,
                    'baseurl'=>'http://hrtang-cloud.oss-cn-beijing.aliyuncs.com/',

                );
            }
            $sqls='SELECT *from hrt_files where uid='.$uid.' and type=2  limit '.$limit;
            $rs=$this->db->query($sqls);
            $row=mysqli_num_rows($rs);
            if($row>0){
                foreach($rs as $v){
                    $arr['list'][]=array(
                        'filepath'=>$v['filename'],
                        'size'=>$v['size'],
                    );
                }
            }else{
                $arr['list']=null;
            }
        $this->returnjson($arr);

    }
//    12-5公司文件列表
    public function publicFileListAction(){
        // $uid=$this->post('userId');
        // $pageNo=$this->post('pageNo');
        // $pageSize=$this->post('pageSize');

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $pageNo=$data['pageNo'];
       $pageSize=$data['pageSize'];
        //计算出总数
        $sql='SELECT *from hrt_files where  type=1';
        $r=$this->db->query($sql);
        $total=mysqli_num_rows($r);
        $eNum=ceil($total/$pageSize);
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;
        if($pageNo>=$eNum){
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'isend'=>true,
                'baseurl'=>'http://hrtang-cloud.oss-cn-beijing.aliyuncs.com/',

            );
        }else{
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'isend'=>false,
                'baseurl'=>'http://hrtang-cloud.oss-cn-beijing.aliyuncs.com/',

            );
        }
        $sqls='SELECT *from hrt_files  where  type=1  limit '.$limit;
        $rs=$this->db->query($sqls);
        $row=mysqli_num_rows($rs);
        if($row>0){
            foreach($rs as $v){
                $arr['list'][]=array(
                    'filepath'=>$v['filename'],
                    'size'=>$v['size'],
                );
            }
        }else{
            $arr['list']=null;
        }
        $this->returnjson($arr);



    }
//12-6 上传文件数据
    public function   uploadFileAction(){
            // $uid=$this->post('userId');
            // $filename=$this->post('filename');
            // $type=$this->post('type');
            // $size=$this->post('size');
            // $opdate=date('Y-m-d H:i:s',time());


           $data=json_decode($this->post('data'),true);
           $uid=$data['userId'];
           $filename=$data['filename'];
           $type=$data['type'];
           $size=$data['size'];
           $opdate=date('Y-m-d H:i:s',time());

            $arr0=array(
                'filename'=>$filename,
                'type'=>$type,
                'size'=>$size,
                'uid'=>$uid,
                'opdate'=>$opdate,
            );
            $r=$this->db->record('[Q]files',$arr0);
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