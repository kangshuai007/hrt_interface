<?php
class customerClassAction extends apiAction
{
    public function mark($sql){
        $query=$this->db->query($sql);
        $return=mysqli_num_rows($query);
        if($return>0){
            return 1;
        }else{
            return 0;
        }
    }
    
    //1. 允许查看客户管理的人员列表 
    public function selectlistAction(){
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $sql = "SELECT b.id,b.name,b.face,a.isall,a.rangelist from hrt_customerment a
                left join hrt_admin b
                on  a.uid=b.id";
        $result=$this->db->query($sql);
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
    
    //2.添加一个有查看客户管理权限的人
    public function addcustomerAction(){
        $data=json_decode($this->post('data'),true);
        $isall=$data['isAllMember'];
        $userlist=$data['userList'];
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        if($isall==1){
            $all_admin=$this->db->getrows('[Q]admin','',"`id`");
            foreach($all_admin as $v){
                $sql='SELECT * from hrt_customerment where uid='.$v;
                $custment=mysqli_fetch_assoc($sql);
                if(empty($custment)){
                    $members=array(
                        'uid'=>$v['id'],
                        'isall'=>1,
                        'rangelist'=>0,
                        'date'=>time(),
                    );
                    $result=$this->db->record('[Q]customerment',$members);
                }
            }
            if($result){
                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
        }else{
            foreach($userlist as $v){
                $sql='SELECT * from hrt_customerment where uid='.$v;
                $custment=mysqli_fetch_assoc($sql);
                if(empty($custment)){
                    $members=array(
                        'uid'=>$v,
                        'isall'=>1,
                        'rangelist'=>0,
                        'date'=>time(),
                    );
                    $result=$this->db->record('[Q]customerment',$members);
                }
            }
            if($result){
                $this->returnjson($arr);
            }else{
                $this->showreturn('','失败',201);
            }
        }
    }
    //3.移除一个有查看客户管理权限的人
    public function deletecustomerAction(){
        $data=json_decode($this->post('data'),true);
        $ruleid=$data['hasPermissionUserId'];
        $sql='delete from hrt_customerment where uid='.$ruleid;
        $result=$this->db->query($sql);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //4.修改一个有权限人的查看范围
    public function modifycustomerAction(){
        $data=json_decode($this->post('data'),true);
        $ruleid=$data['hasPermissionUserId'];
        $isall=$data['isAll'];
        $rangelist=$data['rangeList'];
        $member=array(
            'isall'=>$isall,
            'rangelist'=>json_encode($rangelist,true),
        );
        $result=$this->db->record('[Q]customerment',$member,"`uid`='$ruleid'");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //5.新建客户
    public function addcustomersAction(){
        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $name = $data['name'];
        $number = $data['number'];
        $address = $data['address'];
        $remark = $data['remark'];
        $contactList = serialize($data['contactList']);
        $followUserIdList = $data['followUserIdList'];
        $clientStatus = $data['clientStatus'];
        $clientLevel = $data['clientLevel'];
        $member = array(
            'uid'=>$uid,
            'name'=>$name,
            'number'=>$number,
            'address'=>$address,
            'remark'=>$remark,
            'contactList'=>$contactList,
            'followUserIdList'=>implode(",",$followUserIdList),
            'clientStatus'=>$clientStatus,
            'clientLevel'=>$clientLevel,
            'esttime'=>time(),
        );
        $result=$this->db->record('[Q]customers',$member);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //6.修改客户
    public function modifycustomersAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];
        $name = $data['name'];
        $number = $data['number'];
        $address = $data['address'];
        $remark = $data['remark'];
        $contactList = serialize($data['contactList']);
        $followUserIdList = $data['followUserIdList'];
        $clientStatus = $data['clientStatus'];
        $clientLevel = $data['clientLevel'];
        
        $member = array(
            'name'=>$name,
            'number'=>$number,
            'address'=>$address,
            'remark'=>$remark,
            'contactList'=>$contactList,
            'followUserIdList'=>implode(",",$followUserIdList),
            'clientStatus'=>$clientStatus,
            'clientLevel'=>$clientLevel,
            'modtime'=>time()
        );
        $result=$this->db->record('[Q]customers',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //7.我跟进的客户列表
    public function selectcustomersAction(){
        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $clientStatus = $data['clientStatus'];
        $clientLevel = $data['clientLevel'];
        $sort = $data['sort'];
        $pageNo = $data['pageNo'];
        $pageSize = $data['pageSize'];
        //计算页码条数
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;
        
        $limit2 = (($pageNo) * $pageSize) . "," . $pageSize;
        
        $where = " followUserIdList in (".$uid.")";
        if ($clientStatus!=null){
            $where .= " AND clientStatus in ($clientStatus)";
        }
        if($clientLevel!=null){
            $where .= " AND clientLevel in ($clientLevel)";
        }
        if($sort==1){//1:最新创建
            $sort = 'esttime';
        }else{//2：最近更新
            $sort = 'modtime';
        }
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $sql = "SELECT * FROM `hrt_customers` where $where order by $sort desc limit $limit";
        $customers=$this->db->query($sql);
        $see = "SELECT * FROM `hrt_customers` where $where order by $sort desc limit $limit2";
        $return = $this->mark($see);
        if(empty($customers)){
            $arr['isend'] = true;
        }elseif ($customers && $return>0){
            $arr['isend'] = false;
        }elseif ($customers && $return==0){
            $arr['isend'] = true;
        }
        foreach ($customers as $cou){
            $adminlist = array();
            $UserIdList=explode(",",$cou['followUserIdList']);
            foreach ($UserIdList as $id){
                $sql2='SELECT * FROM `hrt_admin` where id = '.$id;
                $query2=$this->db->query($sql2);
                $row=mysqli_fetch_array($query2);
                if(!empty($row)){
                    $adminlist[]=array(
                        'id'=>$row['id'],
                        'truename'=>$row['name'],
                        'headiconurl'=>FACE.$row['face'],
                    );
                }
            }
            $arr['list'][]=array(
                'id'=>$cou['id'],
                'name'=>$cou['name'],
                'followuserlist'=>$adminlist
            );
        }
        $this->returnjson($arr);
    }
    //8.搜索我可以查看的客户列表
    public function searchcustomersAction(){
        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $keyword = $data['keyword'];//关键词（客户名/联系人名/联系人电话）
        $pageNo = $data['pageNo'];
        $pageSize= $data['pageSize'];
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        if($keyword!=null){
            $where .= " AND `name` LIKE '%".$keyword."%' ";
        }
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;
        $limit2 = (($pageNo) * $pageSize) . "," . $pageSize;
        //查看该会员是否有权限
        $sql = "SELECT * FROM `hrt_customerment` where uid = $uid";
        $query=$this->db->query($sql);
        $custment=mysqli_fetch_array($query);
        if($custment['isall']==1){//查看所有人
            $sql2 = "SELECT * FROM `hrt_customers` where 1 $where order by esttime desc limit $limit";
            $customers=$this->db->query($sql2);
            
            $see = "SELECT * FROM `hrt_customers` where 1 $where order by esttime desc limit $limit2";
            $return = $this->mark($see);
            if(empty($customers)){
                $arr['isend'] = true;
            }elseif ($customers && $return>0){
                $arr['isend'] = false;
            }elseif ($customers && $return==0){
                $arr['isend'] = true;
            }
            foreach ($customers as $cou){
                $adminlist = array();
                $contactList = unserialize($cou['contactList']);
                foreach ($contactList as $list){
                    $adminlist[]=array(
                        'name'=>$list['name'],
                        'mobile'=>$list['mobile'],
                        'phone'=>$list['phone'],
                        'job'=>$list['job'],
                        'email'=>$list['email'],
                    );
                }
                $arr['list'][]=array(
                    'id'=>$cou['id'],
                    'name'=>$cou['name'],
                    'contactlist'=>$adminlist
                );
            }
            $this->returnjson($arr);
        }elseif ($custment['isall']==2){//按照查看范围
            $rangelist = json_decode($custment['rangelist']);
            $uids=array();
            foreach($rangelist as  $key=>$value) {
                if($value['type']==1){
                    $uids[] = $value['id'];
                }else{
                    $d_sql='select id from hrt_admin where deptid='.$value['id'];
                    $d_r=$this->db->query($d_sql);
                    foreach($d_r as $vv){
                        $uids[]=$vv['id'];
                    }
                }
            }
            $uids=array_unique($uids); //去重
            $sql2 = "SELECT * FROM `hrt_customers` where uid in('".implode(',',$uids)."') $where order by esttime desc limit $limit";
            $customers=$this->db->query($sql2);
            
            $see = "SELECT * FROM `hrt_customers` where uid in('".implode(',',$uids)."') $where order by esttime desc limit $limit2";
            $return = $this->mark($see);
            if(empty($customers)){
                $arr['isend'] = true;
            }elseif ($customers && $return>0){
                $arr['isend'] = false;
            }elseif ($customers && $return==0){
                $arr['isend'] = true;
            }
            
            foreach ($customers as $cou){
                $adminlist = array();
                $contactList = unserialize($cou['contactList']);
                foreach ($contactList as $list){
                    $adminlist[]=array(
                        'name'=>$list['name'],
                        'mobile'=>$list['mobile'],
                        'phone'=>$list['phone'],
                        'job'=>$list['job'],
                        'email'=>$list['email'],
                    );
                }
                $arr['list'][]=array(
                    'id'=>$cou['id'],
                    'name'=>$cou['name'],
                    'contactlist'=>$adminlist
                );
            }
            $this->returnjson($arr);
        }elseif (empty($custment)){
            $sql2 = "SELECT * FROM `hrt_customers` where uid = $uid $where order by esttime desc limit $limit";
            $customers=$this->db->query($sql2);
            $see = "SELECT * FROM `hrt_customers` where uid = $uid $where order by esttime desc limit $limit2";
            $return = $this->mark($see);
            if(empty($customers)){
                $arr['isend'] = true;
            }elseif ($customers && $return>0){
                $arr['isend'] = false;
            }elseif ($customers && $return==0){
                $arr['isend'] = true;
            }
            foreach ($customers as $cou){
                $adminlist = array();
                $contactList = unserialize($cou['contactList']);
                foreach ($contactList as $list){
                    $adminlist[]=array(
                        'name'=>$list['name'],
                        'mobile'=>$list['mobile'],
                        'phone'=>$list['phone'],
                        'job'=>$list['job'],
                        'email'=>$list['email'],
                    );
                }
                $arr['list'][]=array(
                    'id'=>$cou['id'],
                    'name'=>$cou['name'],
                    'contactlist'=>$adminlist
                );
            }
            $this->returnjson($arr);
        }
    }
    //9.我可以查看的客户
    public function selectmycustomersAction(){
        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $clientStatus = $data['clientStatus'];
        $clientLevel = $data['clientLevel'];
        $sort = $data['sort'];
        $memberId = $data['memberId'];
        $pageNo = $data['pageNo'];
        $pageSize = $data['pageSize'];
        //查看该会员是否有权限
        $sql = "SELECT * FROM `hrt_customerment` where uid = $uid";
        $query=$this->db->query($sql);
        $custment=mysqli_fetch_array($query);
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $where = "";
        // memberId Null:全部跟进人 ！=null  指定跟进人
        if($memberId!=null){
            $where .= " AND followUserIdList LIKE '%".$memberId."%'";
        }
        if ($clientStatus!=null){
            $where .= " AND clientStatus in ($clientStatus)";
        }
        if($clientLevel!=null){
            $where .= " AND clientLevel in ($clientLevel)";
        }
        
        if($sort==1){//1:最新创建
            $sort = 'esttime';
        }else{//2：最近更新
            $sort = 'modtime';
        }
        $limit = (($pageNo - 1) * $pageSize) . "," . $pageSize;
        $limit2 = (($pageNo) * $pageSize) . "," . $pageSize;
        
        if($custment['isall']==1){//查看所有人
            $sql2 = "SELECT * FROM `hrt_customers` where 1 $where order by $sort desc limit $limit";
            $customers=$this->db->query($sql2);
            
            $see = "SELECT * FROM `hrt_customers` where 1 $where order by $sort desc limit $limit2";
            $return = $this->mark($see);
            
            if(empty($customers)){
                $arr['isend'] = true;
            }elseif ($customers && $return>0){
                $arr['isend'] = false;
            }elseif ($customers && $return==0){
                $arr['isend'] = true;
            }
            foreach ($customers as $cou){
                $adminlist = array();
                $UserIdList=explode(",",$cou['followUserIdList']);
                foreach ($UserIdList as $id){
                    $sql3='SELECT * FROM `hrt_admin` where id = '.$id;
                    $query3=$this->db->query($sql3);
                    $row=mysqli_fetch_array($query3);
                    if(!empty($row)){
                        $adminlist[]=array(
                            'id'=>$row['id'],
                            'truename'=>$row['name'],
                            'headiconurl'=>FACE.$row['face'],
                        );
                    }
                }
                $arr['list'][]=array(
                    'id'=>$cou['id'],
                    'name'=>$cou['name'],
                    'followuserlist'=>$adminlist
                );
            }
            $this->returnjson($arr);
        }elseif ($custment['isall']==2){//按照查看范围
            $rangelist = json_decode($custment['rangelist']);
            $uids=array();
            foreach($rangelist as  $key=>$value) {
                if($value['type']==1){
                    $uids[] = $value['id'];
                }else{
                    $d_sql='select id from hrt_admin where deptid='.$value['id'];
                    $d_r=$this->db->query($d_sql);
                    foreach($d_r as $vv){
                        $uids[]=$vv['id'];
                    }
                }
            }
            $uids=array_unique($uids); //去重
            $sql2 = "SELECT * FROM `hrt_customers` where uid in('".implode(',',$uids)."') $where order by $sort desc limit $limit";
            $customers=$this->db->query($sql2);
            
            $see = "SELECT * FROM `hrt_customers` where uid in('".implode(',',$uids)."') $where order by $sort desc limit $limit2";
            $return = $this->mark($see);
            
            if(empty($customers)){
                $arr['isend'] = true;
            }elseif ($customers && $return>0){
                $arr['isend'] = false;
            }elseif ($customers && $return==0){
                $arr['isend'] = true;
            }
            foreach ($customers as $cou){
                $adminlist = array();
                $UserIdList=explode(",",$cou['followUserIdList']);
                foreach ($UserIdList as $id){
                    $sql3='SELECT * FROM `hrt_admin` where id = '.$id;
                    $query3=$this->db->query($sql3);
                    $row=mysqli_fetch_array($query3);
                    if(!empty($row)){
                        $adminlist[]=array(
                            'id'=>$row['id'],
                            'truename'=>$row['name'],
                            'headiconurl'=>FACE.$row['face'],
                        );
                    }
                }
                $arr['list'][]=array(
                    'id'=>$cou['id'],
                    'name'=>$cou['name'],
                    'followuserlist'=>$adminlist
                );
            }
            $this->returnjson($arr);
        }else if(empty($custment)){
            $sql2 = "SELECT * FROM `hrt_customers` where uid=$uid $where order by $sort desc limit $limit";
            $customers=$this->db->query($sql2);
            
            $see = "SELECT * FROM `hrt_customers` where uid=$uid $where order by $sort desc limit $limit2";
            $return = $this->mark($see);
            
            if(empty($customers)){
                $arr['isend'] = true;
            }elseif ($customers && $return>0){
                $arr['isend'] = false;
            }elseif ($customers && $return==0){
                $arr['isend'] = true;
            }
            
            foreach ($customers as $cou){
                $adminlist = array();
                $UserIdList=explode(",",$cou['followUserIdList']);
                foreach ($UserIdList as $id){
                    $sql3='SELECT * FROM `hrt_admin` where id = '.$id;
                    $query3=$this->db->query($sql3);
                    $row=mysqli_fetch_array($query3);
                    if(!empty($row)){
                        $adminlist[]=array(
                            'id'=>$row['id'],
                            'truename'=>$row['name'],
                            'headiconurl'=>FACE.$row['face'],
                        );
                    }
                }
                $arr['list'][]=array(
                    'id'=>$cou['id'],
                    'name'=>$cou['name'],
                    'followuserlist'=>$adminlist
                );
            }
        }
        $this->returnjson($arr);
    }
    
    //10.客户信息详情
    public function detailscustomerAction(){
        $data=json_decode($this->post('data'),true);
        $id=$data['id'];
        $sql = "SELECT * FROM `hrt_customers` where id = $id";
        $query=$this->db->query($sql);
        $cust=mysqli_fetch_array($query);
        if(empty($cust)){
            $this->showreturn('','失败',201);
            return ;
        }
        $contactlist = unserialize($cust['contactList']);
        $followUserIdList = explode(',',$cust['followUserIdList']);
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
            'id'=>$cust['id'],
            'name'=>$cust['name'],
            'number'=>$cust['number'],
            'address'=>$cust['address'],
            'remark'=>$cust['remark'],
            'publishuserid'=>$cust['uid'],
        );
        //联系人
        foreach ($contactlist as $key=>$list){
            $arr['contactlist'][]=array(
                'name'=>$list['name'],
                'mobile'=>$list['mobile'],
                'phone'=>$list['phone'],
                'job'=>$list['job'],
                'email'=>$list['email'],
            );
        }
        //跟进人
        foreach ($followUserIdList as $idlist){
            $sql2='SELECT * FROM `hrt_admin` where id = '.$idlist;
            $query2=$this->db->query($sql2);
            $user=mysqli_fetch_array($query2);
            $arr['followuserlist'][]=array(
                'id'=>$user['id'],
                'truename'=>$user['name'],
                'headiconurl'=>FACE.$user['face'],
            );
        }
        $arr['clientstatus']=$cust['clientStatus']?$cust['clientStatus']:null;
        $arr['clientlevel']=$cust['clientLevel']?$cust['clientLevel']:null;
        //销售机会数量
        $sql3="select * from `hrt_sale` where cid = ".$cust['id'];
        $query3=$this->db->query($sql3);
        $num3=mysqli_num_rows($query3);
        //定时提醒数量
        $sql4="select * from `hrt_timingtask` where cid = ".$cust['id'];
        $query4=$this->db->query($sql4);
        $num4=mysqli_num_rows($query4);
        $arr['salesnum']=$num3;
        $arr['alertnum']=$num4;
        $this->returnjson($arr);
    }
    //11.添加跟进记录
    public function addtrackAction(){
        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $cid=$data['id'];
        $type=$data['type'];
        $content=$data['content'];
        
        $member = array(
            'uid'=>$uid,
            'cid'=>$cid,
            'type'=>$type,
            'content'=>$content,
            'date'=>time(),
        );
        $result=$this->db->record('[Q]customertrack',$member);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //12.跟进记录列表
    public function selecttrackAction(){
        $data=json_decode($this->post('data'),true);
        $cid=$data['id'];
        $pageNo = $data['pageNo'];
        $pageSize = $data['pageSize'];
        $limit = (($pageNo - 1) * $pageSize) . "," . $pageSize;
        $limit2 = (($pageNo) * $pageSize) . "," . $pageSize;
        $arr = array(
            'code'=>200,
            'msg'=>'成功',
        );
        $sql = "SELECT * FROM `hrt_customertrack` where cid = $cid order by date desc limit $limit";
        $track=$this->db->query($sql);
        
        $see = "SELECT * FROM `hrt_customertrack` where cid = $cid order by date desc limit $limit2";
        $return = $this->mark($see);
        
        if(empty($track)){
            $arr['isend'] = true;
        }elseif ($track && $return>0){
            $arr['isend'] = false;
        }elseif ($track && $return==0){
            $arr['isend'] = true;
        }
        
        foreach ($track as $tk){
            $sql2='SELECT * FROM `hrt_admin` where id = '.$tk['uid'];
            $query2=$this->db->query($sql2);
            $user=mysqli_fetch_array($query2);
            $arr['list'][]=array(
                'id'=>$tk['id'],
                'userid'=>$user['id'],
                'truename'=>$user['name'],
                'headiconurl'=>FACE.$user['face'],
                'publishtime'=>$tk['date'],
                'type'=>$tk['type'],
                'content'=>$tk['content']
            );
        }
        $this->returnjson($arr);
    }
    //13.删除客户
    public function deletecustomersAction(){
        $data=json_decode($this->post('data'),true);
        $id=$data['id'];
        
        $sql='delete from hrt_customers where id='.$id;
        $result=$this->db->query($sql);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //14.创建销售机会
    public function addsaleAction(){
        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $cid=$data['clientId'];
        $name = $data['name'];
        $money = $data['predictSalesMoney'];
        $salesLevel = $data['salesLevel'];
        $predictDate = $data['predictDate'];
        $remark = $data['remark'];
        $followUserIdList = $data['followUserIdList'];
        $member = array(
            'uid'=>$uid,
            'cid'=>$cid,
            'name'=>$name,
            'money'=>$money,
            'salesLevel'=>$salesLevel,
            'predictDate'=>$predictDate,
            'remark'=>$remark,
            'followUserIdList'=>implode(",",$followUserIdList),
            'date'=>time()
        );
        $result=$this->db->record('[Q]sale',$member);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //15.修改销售机会
    public function modifysaleAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];
        $cid = $data['clientId'];
        $name = $data['name'];
        $money = $data['predictSalesMoney'];
        $salesLevel = $data['salesLevel'];
        $predictDate = $data['predictDate'];
        $remark = $data['remark'];
        $followUserIdList = $data['followUserIdList'];
        
        $member = array(
            'cid'=>$cid,
            'name'=>$name,
            'money'=>$money,
            'salesLevel'=>$salesLevel,
            'predictDate'=>$predictDate,
            'remark'=>$remark,
            'followUserIdList'=>implode(",",$followUserIdList),
            'date'=>time()
        );
        $result=$this->db->record('[Q]sale',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //16.销售机会列表
    public function selectsaleAction(){
        $data=json_decode($this->post('data'),true);
        //客户id
        $cid = $data['id'];
        $pageNo = $data['pageNo'];
        $pageSize= $data['pageSize'];
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;
        $limit2 = (($pageNo) * $pageSize) . "," . $pageSize;
        
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $sql = "SELECT * FROM `hrt_sale` where cid = $cid order by date desc limit $limit";
        $sale=$this->db->query($sql);
        
        $see = "SELECT * FROM `hrt_sale` where cid = $cid order by date desc limit $limit2";
        $return = $this->mark($see);
        if(empty($sale)){
            $arr['isend'] = true;
        }elseif ($sale && $return>0){
            $arr['isend'] = false;
        }elseif ($sale && $return==0){
            $arr['isend'] = true;
        }
        foreach ($sale as $sa){
            $sql2='SELECT * FROM `hrt_customers` where id = '.$sa['cid'];
            $query2=$this->db->query($sql2);
            $cust=mysqli_fetch_array($query2);
            $arr['list'][]=array(
                'id'=>$sa['id'],
                'name'=>$sa['name'],
                'predictsalesmoney'=>$sa['money'],
                'clientid'=>$cust['id'],
                'clientname'=>$cust['name'],
                'saleslevel'=>$sa['salesLevel'],
            );
        }
        $this->returnjson($arr);
    }
    //17.  销售机会详情
    public function detailssaleAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];
        $sql="SELECT a.*,b.id as c_id,b.`name` as c_name FROM `hrt_sale` a
                LEFT JOIN `hrt_customers` b ON a.cid=b.id
                where a.id = $id";
        $query=$this->db->query($sql);
        $sale=mysqli_fetch_array($query);
        if($sale){
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'id'=>$sale['id'],
                'name'=>$sale['name'],
                'predictsalesmoney'=>$sale['money'],
                'clientid'=>$sale['c_id'],
                'clientname'=>$sale['c_name'],
                'predictdate'=>$sale['predictDate'],
                'saleslevel'=>$sale['salesLevel'],
                'remark'=>$sale['remark'],
                'publishuserid'=>$sale['uid'],
            );
            $UserIdList=explode(",",$sale['followUserIdList']);
            foreach ($UserIdList as $id){
                $sql2="SELECT * FROM `hrt_admin` where id = $id";
                $query2=$this->db->query($sql2);
                $user=mysqli_fetch_array($query2);
                $arr['followuserlist'][]=array(
                    'id'=>$user['id'],
                    'truename'=>$user['name'],
                    'headiconurl'=>FACE.$user['face'],
                );
            }
            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //18.删除销售机会  saletrack
    public function deletesaleAction(){
        $data=json_decode($this->post('data'),true);
        //客户id
        $id = $data['id'];
        $sql = "delete from `hrt_sale` where id = $id";
        $result=$this->db->query($sql);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //19.添加销售机会下的跟进记录
    public function addsaletrackAction(){
        $data=json_decode($this->post('data'),true);
        $sid = $data['id'];
        $uid=$data['userId'];//销售机会id
        $content = $data['content'];
        $date = time();
        $query="INSERT INTO `hrt_saletrack` (`uid`,`sid`,`content`,`date`) VALUES ('$uid','$sid','$content','$date')";
        $this->db->query($query);
        $query="SELECT LAST_INSERT_ID()";
        $result=$this->db->query($query);
        $rows=mysqli_fetch_array($result);
        if($rows[0]){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
                'id'=>$rows[0]
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //20.销售机会下的跟进记录id
    public function selectsaletrackAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];//销售机会id
        $pageNo = $data['pageNo'];
        $pageSize= $data['pageSize'];
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;
        $limit2 = (($pageNo) * $pageSize) . "," . $pageSize;
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $sql = "SELECT * FROM `hrt_saletrack` where sid = $id order by date desc limit $limit";
        $sale=$this->db->query($sql);
        
        $see = "SELECT * FROM `hrt_saletrack` where sid = $id order by date desc limit $limit2";
        $return = $this->mark($see);
        if(empty($sale)){
            $arr['isend'] = true;
        }elseif ($sale && $return>0){
            $arr['isend'] = false;
        }elseif ($sale && $return==0){
            $arr['isend'] = true;
        }
        foreach($sale as $s){
            $sql2='SELECT * FROM `hrt_admin` where id = '.$s['uid'];
            $query2=$this->db->query($sql2);
            $user=mysqli_fetch_array($query2);
            $arr['list'][]=array(
                'id'=>$s['id'],
                'userid'=>$user['id'],
                'truename'=>$user['name'],
                'headiconurl'=>FACE.$user['face'],
                'publishtime'=>$s['date'],
                'content'=>$s['content']
            );
        }
        $this->returnjson($arr);
    }
    //21.添加客户下的定时提醒
    public function addtimingtaskAction(){
        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $cid = $data['clientId'];//客户id
        $content = $data['content'];
        $alerttime = $data['alerttime'];
        $followUserIdList = $data['followUserIdList'];
        $member = array(
            'uid'=>$uid,
            'cid'=>$cid,
            'content'=>$content,
            'alerttime'=>$alerttime,
            'followUserIdList'=>implode(",",$followUserIdList),
            'date'=>time()
        );
        $result=$this->db->record('[Q]timingtask',$member);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //22.修改客户定时提醒
    public function modifytimingtaskAction(){
        $data=json_decode($this->post('data'),true);
        $id=$data['id'];
        $cid = $data['clientId'];//客户id
        $content = $data['content'];
        $alerttime = $data['alerttime'];
        $followUserIdList = $data['followUserIdList'];
        $member = array(
            'cid'=>$cid,
            'content'=>$content,
            'alerttime'=>$alerttime,
            'followUserIdList'=>implode(",",$followUserIdList),
            'date'=>time()
        );
        $result=$this->db->record('[Q]timingtask',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //23.客户下的定时提醒列表
    public function selecttimingtaskAction(){
        $data=json_decode($this->post('data'),true);
        $cid=$data['id'];//客户id
        $pageNo = $data['pageNo'];
        $pageSize= $data['pageSize'];
        $limit=(($pageNo-1)*$pageSize).",".$pageSize;
        $limit2 = (($pageNo) * $pageSize) . "," . $pageSize;
        $arr=array(
            'code'=>200,
            'msg'=>'成功',
        );
        $sql = "SELECT * FROM `hrt_timingtask` where cid = $cid order by date desc limit $limit";
        $task=$this->db->query($sql);
        
        $see = "SELECT * FROM `hrt_timingtask` where cid = $cid order by date desc limit $limit2";
        $return = $this->mark($see);
        if(empty($task)){
            $arr['isend'] = true;
        }elseif ($task && $return>0){
            $arr['isend'] = false;
        }elseif ($task && $return==0){
            $arr['isend'] = true;
        }
        foreach($task as $v){
            $arr['list'][]=array(
                'id'=>$v['id'],
                'content'=>$v['content'],
                'alerttime'=>strtotime($v['alerttime']),
                'publishuserid'=>$v['uid']
            );
        }
        $this->returnjson($arr);
    }
    //24.删除定时提醒
    public function deletetimingtaskAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];//定时提醒id
        $sql = "delete from `hrt_timingtask` where id = $id";
        $result=$this->db->query($sql);
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //25.更新客户的跟进人列表
    public function updateuseridlistAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];//客户id
        $followUserIdList = json_encode($data['followUserIdList'],true);
        $member = array(
            'followUserIdList'=>implode(",",$followUserIdList),
            'modtime'=>time()
        );
        $result=$this->db->record('[Q]customers',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //26.更新客户状态
    public function updateclientstatusAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];//客户id
        $clientStatus = $data['clientStatus'];
        $member = array(
            'clientStatus'=>$clientStatus,
            'modtime'=>time()
        );
        $result=$this->db->record('[Q]customers',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //27.更新客户分级
    public function updateclientlevelAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];//客户id
        $clientLevel = $data['clientLevel'];
        $member = array(
            'clientLevel'=>$clientLevel,
            'modtime'=>time()
        );
        $result=$this->db->record('[Q]customers',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //28.更新销售机会的销售阶段
    public function updatesaleslevelAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];//销售机会id
        $salesLevel = $data['salesLevel'];
        $member = array(
            'salesLevel'=>$salesLevel,
        );
        $result=$this->db->record('[Q]sale',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
    //29.更新销售机会的跟进人列表
    public function updateidlistAction(){
        $data=json_decode($this->post('data'),true);
        $id = $data['id'];//销售机会id
        $followUserIdList = json_encode($data['followUserIdList'],true);
        $member = array(
            'followUserIdList'=>implode(",",$followUserIdList),
        );
        $result=$this->db->record('[Q]sale',$member,"`id`=$id");
        if($result){
            $this->returnjson(array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','失败',201);
        }
    }
}
?>