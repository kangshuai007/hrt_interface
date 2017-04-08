<?php
//培训模块相关
class trainingClassAction extends apiAction{
//8-1我的答题战绩数据列表
public function  myAnswerListAction(){
        // $uid=$this->post('userId');

       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];

//       1. 查询出星级
        $sql1='SELECT star from hrt_admin  where id='.$uid;
        $r1=$this->db->query($sql1);
        $row1=mysqli_fetch_array($r1);
        $star=$row1['star'];
        $t='star'.$star;
    //        2.根据星级查询出需要答题的总数
        $sql2="SELECT  b.name,$t,a.tid from hrt_knowstar a
                left join hrt_option b  on a.tid=b.id";

        $r2=$this->db->query($sql2);
        if($r2){
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'starlevel'=>$star,
                 );
            foreach($r2 as $v2){
            // print_r($v2);
//              根据分类tid查询出下面的子类
                    $sql2_1='SELECT id from hrt_option where pid='.$v2['tid'];
                    $r2_1=$this->db->query($sql2_1);
                    $aArr=array();
                    $count=0;
                    foreach($r2_1 as $vv){

                      $aArr[]=$vv['id'];
                        $sql2_2='SELECT *from hrt_knowmytiku a
                              left join hrt_knowmyresult b on a.id=b.eid
                              where a.uid='.$uid.' and cid='.$vv['id'];
                        $r2_2=$this->db->query($sql2_2);
                    // 答对数
                        foreach($r2_2 as $k=>$vr){
                    
                            $count=(int)($vr['rightcount']+$count);
                        }
                    }
                    $arr['list'][]=array(
                        'id'=>$v2['tid'],
                        'categoryname'=>$v2['name'],
                        'count'=>$v2["$t"],
                        'finishedcount'=>$count,
                    );
         
        }
             $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }
   


}

//      8-2选择试题的分类列表
public function getTestTypeAction(){
         // $categoryid=$this->post('categoryId');


       $data=json_decode($this->post('data'),true);
       $categoryid=$data['categoryId'];

        if($categoryid==null){
            //226 在数据库已经定义好  或者字段有knowtikutype的id=226(先查询出也可以)
            $sql='SELECT *from hrt_option where pid=226';
            $r=$this->db->query($sql);
            $row=mysqli_num_rows($r);
            if($row >0){

                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                );
                foreach($r as $v){
//                查询是否还有下一级目录
                    $s_sql='SELECT *from hrt_option where pid='.$v['id'];
                    $s_r=$this->db->query($s_sql);
                    $num=mysqli_num_rows($s_r);
                    if($num >0){
                        $islast=0;
                    }else{
                        $islast=1;
                    }
                    $arr['categorylist'][]=array(
                        'categoryid'=>$v['id'],
                        'name'=>$v['name'],
                        'islastlevel'=>$islast,

                    );
                }
            }else{
                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                    'categorylist'=>null,
                );
            }
            $this->returnjson($arr);

        }else{
            //不是最顶层  查询出下面的子层
            $sql='SELECT *from hrt_option where pid='.$categoryid;
            $r=$this->db->query($sql);
            $row=mysqli_num_rows($r);
            if($row >0){

                $arr=array(
                    'code'=>200,
                    'msg'=>'成功',
                );
                 foreach($r as  $v){
                     //                查询是否还有下一级目录
                     $s_sql='SELECT *from hrt_option where pid='.$v['id'];
                     $s_r=$this->db->query($s_sql);
                     $num=mysqli_num_rows($s_r);
                     if($num >0){
                         $islast=0;
                     }else{
                         $islast=1;
                     }
                    $arr['categorylist'][]=array(
                        'categoryid'=>$v['id'],
                        'name'=>$v['name'],
                        'islastlevel'=>$islast,
                    );
                 }
            }else{
                 $arr=array(
                     'code'=>200,
                     'msg'=>'成功',
                     'categorylist'=>null,
                 );
            }
            $this->returnjson($arr);
        }
}
// 生成八位随机数函数
    public  function  getCode($length=6){
        $characters = '0123456789';
        $randomCode = '';
        for ($i = 0; $i < $length; $i++) {
            $randomCode .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'EX'.$randomCode;
    }
//3获取随机抽取的考题列表
public function  getExamListAction(){
        // $uid=$this->post('userId');
        // $categoryid=$this->post('categoryId');
        // $starlevel=$this->post('starLevel');
        // $roletype=$this->post('roleType');


       $data=json_decode($this->post('data'),true);
       $uid=$data['userId'];
       $categoryid=$data['categoryId'];
       $starlevel=$data['starLevel'];
       $roletype=$data['roleType'];


        $examcode=$this->getCode($length=8);
        $sql="SELECT id,title,`type`,ana,anb,anc,`and`  from  hrt_knowtiku
            where typeid=$categoryid and xingji like '%$starlevel%'  and dengji
             like '%$roletype%' order by rand() limit 20";
        $r=$this->db->query($sql);
        foreach($r as $v){

        $idArr[]=$v['id'];

    }
       $ids=implode(',',$idArr);
       $arr0=array(
           'examcode'=>$examcode,
           'uid'=>$uid,
           'eid'=>$ids,
           'cid'=>$categoryid,
           'opdate'=>date('Y-m-d H:i:s',time()),
       );
      $rs=$this->db->record('[Q]knowmytiku',$arr0);
        if($rs){
            $examid=$this->db->insert_id();
//            试题编号
            $sql_1='SELECT examcode from hrt_knowmytiku where id='.$examid;

            $r_1=$this->db->query($sql_1);
            $row=mysqli_fetch_array($r_1);
            $examcode=$row['examcode'];
            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'examid'=>$examid,
                'examcode'=>$examcode,
            );
            foreach($r as $v){
                $cArr=array('A'=>$v['ana'], 'B'=>$v['anb'], 'C'=>$v['anc'], 'D'=>$v['and']);
                $ccArr=array();
                foreach($cArr as $k=>$cc){
                    $ccArr[]=array(
                        'id'=>$k,
                        'item'=>$cc,
                    );
                }
                if($v['type']==0){
                    $t=1;
                }else{
                    $t=2;
                }
                $arr['list'][]=array(
                    'id'=>$v['id'],
                    'name'=>$v['title'],
                    'type'=>$t,
                    'choices'=>$ccArr,
                );
            }
            $this->returnjson($arr);
        }else{
            $this->showreturn('','选题入库未成功',201);
        }

}


//8-4提交考试结果
    public  function  submitResultAction(){
         // $uid=$this->post('userId');
         // $examid=$this->post('examId');
         // $result=$this->post('resultList');


        $data=json_decode($this->post('data'),true);
        $uid=$data['userId'];
        $examid=$data['examId'];
        $result=json_encode($data['resultList'],true);



//        匹配答对的个数
                $rArr=json_decode($result,true);
                $rightcount=0;


                foreach($rArr as $v){

                    $sql_2='SELECT answer from hrt_knowtiku where  id='.$v['id'];
                    $r_2=$this->db->query($sql_2);
                    $sArr=array();
                    //遍历正确答案
                    foreach($r_2 as $r) {
                        if(strlen($r['answer'])>1){
                            $sArr=explode(',',$r['answer']);
                        }else{
                            $sArr[] = $r['answer'];
                        }
                    }
        //               答题人所选答案
                    $aArr=array();

                    foreach($v['choices'] as $vv){
                        $aArr[]=$vv['id'];
                    }

        //                比对
                    if($sArr==$aArr){
                        $rightcount=$rightcount+1;
                    }
                }
            // 根据uid得到答题人信息
            $a_sql='SELECT name from hrt_admin where id='.$uid; 
            $a_r=$this->db->query($a_sql);
            $arow=mysqli_fetch_array($a_r);
            // echo $arow['name'];


            $arr0=array(
                'uid'=>$uid,
                'username'=>$arow['name'],
                'eid'=>$examid,
                'result'=>$result,
                'rightcount'=>$rightcount,
                'opdate'=>time(),
                'dt'=>date('Y-m-d H:i:s',time()),
            );
        $result=$this->db->record('[Q]knowmyresult',$arr0);
        if($result){
//         题目的总数
            $sql_1='SELECT *from hrt_knowmytiku where id='.$examid;
            $r_1=$this->db->query($sql_1);
            $row1=mysqli_fetch_array($r_1);
            $eidArr=explode(',',$row1['eid']);
            $count=count($eidArr);


            $arr=array(
                'code'=>200,
                'msg'=>'成功',
                'count'=>$count,
                'rightcount'=>$rightcount,
            );
            $this->returnjson($arr);
        }else{
            $this->showreturn('','失败',201);
        }

    }


















}