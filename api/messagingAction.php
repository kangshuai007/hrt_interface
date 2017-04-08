<?php
include(''.ROOT_PATH.'/webmain/task/jstx/TopSdk.php');

class messagingClassAction extends apiAction
{
    
    //添加用户
    public function adduserAction($nick,$userid){

        $c = new TopClient();
        $req = new OpenimUsersAddRequest;
        $userinfos = new Userinfos;
        
        $userinfos->nick="测试者123";
        // $userinfos->icon_url="http://xxx.com/xxx";
        $userinfos->userid='test123';
        
        $req->setUserinfos(json_encode($userinfos));
        $list = $this->toArray($c->execute($req));
        //返回添加失败的用户
       
        if(!empty($list['uid_fail'])){
            return $list['uid_fail']; 
           
         
        }else {
            return 1; 
         
        }
    }
    
    //查询用户
    public function selectAction($userids){
        $userids = implode(",", $userids);
    	$c = new TopClient;
    	$req = new OpenimUsersGetRequest;
    	$req->setUserids($userids);
    	$resp = $c->execute($req);
    	var_dump($resp);
    }
    //删除用户
    public function userdeleteAction($userids){
        $userids='user1';
        // $userids = implode(",", $userids);
        $c = new TopClient;
        $req = new OpenimUsersDeleteRequest;
        $req->setUserids($userids);
        $resp = $c->execute($req);
        var_dump($resp);
    }
    //批量更新用户信息
    public function userupdateAction($nick,$icon_url,$userid){
        $c = new TopClient;
        $req = new OpenimUsersUpdateRequest;
        $userinfos = new Userinfos;
        if(!empty($nick)){
            $userinfos->nick=$nick;
        }elseif (!empty($icon_url)){
            $userinfos->icon_url=$icon_url;
        }
        $userinfos->userid=$userid;
        $req->setUserinfos(json_encode($userinfos));
        $list = $this->toArray($c->execute($req));
        //返回添加失败的用户
        if(!empty($list['uid_fail'])){
            return $list['uid_fail'];
        }else {
            return 1;
        }
    }
    /*
     * $to_users = array('admin1','admin2');
     */
    //发送自定义消息
    public function custmsgpushAction($user_sende,$to_users,$type,$status,$summary,$content){
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
    //发送业务自定义消息
    public function businessnewsAction($user_sende,$to_users,$type,$id,$summary,$content){
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
    
    //http://localhost/xinhu/api.php?d=task&m=messaging&a=add
    //标准消息发送
    public function standardAction($from_user,$to_users,$msg_type,$context){
        $c = new TopClient;
        $req = new OpenimImmsgPushRequest;
        $immsg = new ImMsg;
        $immsg->from_user=$from_user;
        $immsg->to_users=$to_users;
        $immsg->msg_type=$msg_type;
        $immsg->context=$context;
        $immsg->to_appkey="0";
        $immsg->media_attr="{\"type\":\"amr\",\"playtime\":6}";
        $immsg->from_taobao="0";
        $req->setImmsg(json_encode($immsg));
        $resp = $c->execute($req);
    }
    /*
     * 创建群
     * $uid 用户信息
     * $tribe_name 群名称
     * $notice 群公告
     * $tribe_type 群类型有两种tribe_type = 0 普通群 普通群有管理员角色，对成员加入有权限控制tribe_type = 1 讨论组 讨论组没有管理员，不能解散   注：（传过来的数字是字符串） $tribe_type="0";
     * 讨论组ID：1901253097
     * 
     * $uid,$tribe_name,$notice
     */
    public function createAction($uid,$tribe_name,$notice){
        $c = new TopClient;
        $req = new OpenimTribeCreateRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeName($tribe_name);
        $req->setNotice($notice);
        $req->setTribeType("1");
        $members = new OpenImUser;
        $members->uid="admin";
        $members->taobao_account="false";
        $members->app_key=APPKEY;
        $req->setMembers(json_encode($members));
        $list = $this->toArray($c->execute($req));
        if(!empty($list['tribe_info']['tribe_id'])){
            return   $list['tribe_info']['tribe_id'];
        }else{
            return  "创建失败";
        }
    }
    /*
     * 群邀请加入
     * $tribe_id ： 群id 
     * $uid : 用户id
     * $bid : 被邀请用户id
     * $tribe_id,$uid,$bid
     * 1901248914
     */
    public function inviteAction($tribe_id,$uid,$bid){
        $bid = implode(",", $bid);
        $c = new TopClient;
        $req = new OpenimTribeInviteRequest;
        $req->setTribeId($tribe_id);
        $members = new OpenImUser;
        $members->uid=$bid;
        $members->taobao_account="false";
        $members->app_key=APPKEY;
        $req->setMembers(json_encode($members));
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
            return  "操作成功";
        }else{
            return "创建失败";
        }
    }
    /*
     * 踢出群成员
     * $tribe_id : 群ID
     * $uid ： 用户ID
     * $tid ： 被踢走用户ID
     */
    public function expelAction($tribe_id,$uid,$tid){  
        $c = new TopClient;
        $req = new OpenimTribeExpelRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $member = new OpenImUser;
        $member->uid=$tid;
        $member->taobao_account="false";
        $member->app_key=APPKEY;
        $req->setMember(json_encode($member));
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
            return  "操作成功";
        }else{
            return "创建失败";
        }
    }
    /*
     * 群解散
     * $uid,$tribe_id
     */
    public function dismissAction($uid,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeDismissRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
            return  "操作成功";
        }else{
            return "创建失败";
        }
    }
    
    /*
     * OPENIM群成员退出
     * $uid:用户id
     * $tribe_id：群id
     */
    public function toexitAction($uid,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeQuitRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $resp = $c->execute($req);
    }
    /*
     * 获取群消息
     * 
     */
    public function gettribeinfoAction($uid,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeGettribeinfoRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId($tribe_id);
        $list = $this->toArray($c->execute($req));
        if(!empty($list['code'])){
            return   '获取失败';
        }else{
            return $list;
        }
    }
    /*
     * OPENIM群信息修改
     * $uid  用户ID
     * $tribe_name  群名称
     * $notice 群公告
     * $tribe_id 群id 
     */
    public function modifytribeinfoAction($uid,$tribe_name,$notice,$tribe_id){
        $c = new TopClient;
        $req = new OpenimTribeModifytribeinfoRequest;
        $user = new OpenImUser;
        $user->uid=$uid;
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeName($tribe_name);
        $req->setNotice($notice);
        $req->setTribeId($tribe_id);
        $resp = $c->execute($req);
        $list = $this->toArray($c->execute($req));
        if($list['tribe_code']==0){
            return  "操作成功";
        }else{
            return "创建失败";
        }
    }
    /*
     * OPENIM群成员获取
     * 
     */
    public function getmembersAction(){
        $c = new TopClient;
        $req = new OpenimTribeGetmembersRequest;
        $user = new OpenImUser;
        $user->uid="user1";
        $user->taobao_account="false";
        $user->app_key=APPKEY;
        $req->setUser(json_encode($user));
        $req->setTribeId("1993238960");
        $resp = $c->execute($req);
        var_dump($resp);
    }
    
    //object(SimpleXMLElement) 对象转换为数组的方式
 
       function toArray($simplexml_obj, $array_tags=array(), $strip_white=1)
    {
        if($simplexml_obj)
        {
            // if( count($simplexml_obj)==0 )
            //     return $strip_white?trim((string)$simplexml_obj):(string)$simplexml_obj;
            // $attr = array();
            // foreach ($simplexml_obj as $k=>$val) {
            //     if( !empty($array_tags) && in_array($k, $array_tags) ) {
            //         $attr[] = self::toArray($val, $array_tags, $strip_white);
            //     }else{
            //         $attr[$k] = self::toArray($val, $array_tags, $strip_white);
            //     }
            // }
            // return $attr;

            $arr = (array)$simplexml_obj;
            foreach ($arr as $k => $value) {
                if (is_object($value)) {
                    $arr[$k] = $this->toArray($value);
                }
            }
            return $arr;
        }
        return false;
    }
    /*
     * 友盟+消息推送
     */
    
    /*
     * android商户推送自定义播
     */
    public function androidAction($uid,$ticker,$title,$text,$type) {
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
    public function iosAction($uid,$alert,$type) {
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