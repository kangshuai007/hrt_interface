<?php 
class kaoqinClassAction extends apiAction
{
	public function adddkjlAction()
	{

		$mac 	= $this->post('mac');
		$ip 	= $this->post('ip');
		$msg 	= m('kaoqin')->adddkjl($this->adminid,0,'',$ip,$mac);
		if($msg!='')$this->showreturn('', $msg, 201);
		$this->showreturn($this->now);
	}
/*----------------------------------------------------------------------------------*/
	//1:初始化数据
	public function getTimeSheetsAction(){

		 // $uid=$this->post('userId');
		 // $day=date('Y-m-d',time());
		$data=json_decode($this->post('data'),true);
		$uid=$data['userId'];
		$day=date('Y-m-d',time());



		//			根据uid查询出遵循的考勤规则即考勤的id
		$dpsql='SELECT deptid from hrt_admin where id='.$uid;
		$dr=$this->db->query($dpsql);
		$drow=mysqli_fetch_array($dr);
		$deptid= $drow['deptid'];

		$d_s='SELECT id,deptid,sxdate,kqmodel from hrt_kq_data where exdate is NULL';

		$d_r=$this->db->query($d_s);
		$mykqdata = 0;
		foreach($d_r as $rr){
            if(time()>=strtotime($rr['sxdate'])){
                $dpid=json_decode($rr['deptid'],true);
                $suid=json_decode($rr['suid'],true);
                
                if(in_array((int)$deptid,$dpid)){
                    $aids[]=$rr['id'];
                    if(!in_array((int)$uid,$suid)){
                        $mykqdata=1;
                    }else{
                        $mykqdata=0;
                    }
                }else{
                    $aids[]='';
                    $mykqdata=0;
                }
            }
		}
		$aidArr=array_filter($aids);
		$aid=implode(',',$aidArr);  //考勤id

		$time_const=3600;
		$csql="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=1 and status !=0";
		$c_r=$this->db->query($csql);
		$snum=mysqli_num_rows($c_r);
		if($snum>0){
			$s=0;
		}else{
			$s=2;
		}
		//	  是否有考勤数据
 		$kqdataSql='SELECT *from hrt_kq_data ';
		$kq=$this->db->query($kqdataSql);
		$num=mysqli_num_rows($kq);
		if($num>0){
			$kqdata=1;
		}else{
			$kqdata=0;
		}
		//考勤模型
		$modelsql='SELECT kqmodel from hrt_kq_data where id='.$aid;
		$m=$this->db->query($modelsql);
		@$mm=mysqli_fetch_array($m);
		$arr=array(
			'code'=>200,
			'msg'=>'成功',
			'attendancetime'=>strtotime($day),
			'hasattendancedata'=>$kqdata,
			'hasmyattendancedata'=>$mykqdata,
			'attendancemode'=>$mm['kqmodel'],

		);
		$csql_1="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=3 and status !=0";
		$c_r_1=$this->db->query($csql_1);
		$snum_1=mysqli_num_rows($c_r_1);
		if($snum_1>0){
			$s1=0;
		}else{
			$s1=2;
		}
		
// 		$csql_11="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=1 and status !=0";
// 		$c_r_11=$this->db->query($csql_11);
// 		$snum_11=mysqli_num_rows($c_r_11);
// 		if($snum_11>0){
// 		    $s11=0;
// 		}else{
// 		    $s11=2;
// 		}
		
		if($mm['kqmodel']==1){
		    //  1.先去考勤记录表中查询是否有这个人的考勤记录(第一条)
		    $s_sql_1="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=1";
		    $s_r_1=$this->db->query($s_sql_1);
		    $num1=mysqli_num_rows($s_r_1);
		    if($num1>0){
		        // 已有上午上班打卡考勤记录
		        foreach ($s_r_1  as  $v1) {
		            $arr['list'][]=array(
		                'type'=>$v1['type'],
		                'status'=>$v1['status'],
		                'time'=>$v1['kqsj'],
		                'signtime'=>$v1['signtime'],
		            );
		        }
		    }else{
		        // 没有type =1记录  比对
		        $sql_1="SELECT amstime as stime,pmetime  from hrt_kq_data where id=$aid";
		        $r1=$this->db->query($sql_1);
		        @$row1=mysqli_fetch_array($r1);
		        $bt_1=strtotime($day.$row1['stime'])-3600;
		        $st_1=strtotime($day.$row1['stime']);
		        $et_1=strtotime($day.$row1['pmetime']);
		        $nowdt_1=strtotime(date('Y-m-d H:i:s',time()));
		        if($nowdt_1<$bt_1){ //未开始
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>2,
		                'time'=>$row1['stime'],
		            );
		        }else if($bt_1<$nowdt_1 &&  $nowdt_1<$st_1){//未签到
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>0,
		                'time'=>$row1['stime'],
		            );
		    
		        }else if($st_1<$nowdt_1   && $nowdt_1<$et_1){
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>0,
		                'time'=>$row1['stime'],
		            );
		    
		        }else{
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>1,
		                'time'=>$row1['stime'],
		            );
		        }
		    }
		    //  4.先去考勤记录表中查询是否有这个人的考勤记录(第四条)
		    $s_sql_4="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=4";
		    $s_r_4=$this->db->query($s_sql_4);
		    $num4=mysqli_num_rows($s_r_4);
		    if($num4>0){
		        // 已有上午下班打卡考勤记录
		        foreach ($s_r_4  as  $v4) {
		            $arr['list'][]=array(
		                'type'=>$v4['type'],
		                'status'=>$v4['status'],
		                'time'=>$v4['kqsj'],
		                'signtime'=>$v4['signtime'],
		            );
		        }
		    }else{
		        // 没有type =4记录  比对
		        // $sql_4_1="SELECT *from hrt_kqsjgz where name='下午上班'";
		        // $r4_1=$this->db->query($sql_4_1);
		        // $row4_1=mysqli_fetch_array($r4_1);
		    
		        $sql_4="SELECT pmetime as stime from hrt_kq_data where id=$aid";
		        $r4=$this->db->query($sql_4);
		        @$row4=mysqli_fetch_array($r4);
		    
		        // $stt=strtotime($day.$row4_1['stime']);
		    
		        $st_4=strtotime($day.$row4['stime']);
		        $et_4=strtotime($day.'23:59');
		        $nowdt_4=strtotime(date('Y-m-d H:i:s',time()));
		        if($nowdt_4<$et_4){
		            if($arr['list'][0]['status']==1){
		                $arr['list'][]=array(
		                    'type'=>4,
		                    'status'=>0,
		                    'time'=>$row4['stime'],
		                );
		            }else{
		                $arr['list'][]=array(
		                    'type'=>4,
		                    'status'=>$s,
		                    'time'=>$row4['stime'],
		                );
		            }
		    
		        }else{
		            $arr['list'][]=array(
		                'type'=>4,
		                'status'=>1,
		                'time'=>$row4['stime'],
		            );
		        }
		    }
		}else{
		    //  1.先去考勤记录表中查询是否有这个人的考勤记录(第一条)
		    $s_sql_1="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=1";
		    $s_r_1=$this->db->query($s_sql_1);
		    $num1=mysqli_num_rows($s_r_1);
		    if($num1>0){
		        // 已有上午上班打卡考勤记录
		        foreach ($s_r_1  as  $v1) {
		            $arr['list'][]=array(
		                'type'=>$v1['type'],
		                'status'=>$v1['status'],
		                'time'=>$v1['kqsj'],
		                'signtime'=>$v1['signtime'],
		            );
		        }
		    }else{
		        // 没有type =1记录  比对
		        $sql_1="SELECT amstime as stime from hrt_kq_data where id=$aid";
		        $r1=$this->db->query($sql_1);
		        @$row1=mysqli_fetch_array($r1);
		        $bt_1=strtotime($day.$row1['stime'])-3600;
		        $st_1=strtotime($day.$row1['stime']);
		        $et_1=strtotime($day.$row1['etime']);
		        $nowdt_1=strtotime(date('Y-m-d H:i:s',time()));
		        if($nowdt_1<$bt_1){
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>2,
		                'time'=>$row1['stime'],
		            );
		        }else if($bt_1<$nowdt_1 &&  $nowdt_1<$st_1){
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>0,
		                'time'=>$row1['stime'],
		            );
		    
		        }else if($st_1<$nowdt_1   && $nowdt_1<$et_1){
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>0,
		                'time'=>$row1['stime'],
		            );
		    
		        }else{
		            $arr['list'][]=array(
		                'type'=>1,
		                'status'=>1,
		                'time'=>$row1['stime'],
		            );
		        }
		    }
		    //  2.先去考勤记录表中查询是否有这个人的考勤记录(第二条)
		    $s_sql_2="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=2";
		    $s_r_2=$this->db->query($s_sql_2);
		    $num2=mysqli_num_rows($s_r_2);
		    if($num2>0){
		        // 已有上午下班打卡考勤记录
		        foreach ($s_r_2  as  $v2) {
		            $arr['list'][]=array(
		                'type'=>$v2['type'],
		                'status'=>$v2['status'],
		                'time'=>$v2['kqsj'],
		                'signtime'=>$v2['signtime'],
		            );
		        }
		    }else{
		        // 没有type =2记录  比对
		        // print_r($arr['list'][0]['status']);
		        $sql_2="SELECT ametime as stime,pmstime as etime  from hrt_kq_data where id=$aid";
		        $r2=$this->db->query($sql_2);
		        @$row2=mysqli_fetch_array($r2);
		        $et_2=strtotime($day.$row2['etime']);
		        $nowdt_2=strtotime(date('Y-m-d H:i:s',time()));
		        if($nowdt_2<$et_2){
		            if($arr['list'][0]['status']==1){  //判断是否为过期
		                $arr['list'][]=array(
		                    'type'=>2,
		                    'status'=>0,
		                    'time'=>$row2['stime'],
		                );
		            }else{
		                $arr['list'][]=array(
		                    'type'=>2,
		                    'status'=>$s,
		                    'time'=>$row2['stime'],
		                );
		            }
		    
		        }else{
		            $arr['list'][]=array(
		                'type'=>2,
		                'status'=>1,
		                'time'=>$row2['stime'],
		            );
		        }
		    
		    }
		    //  3.先去考勤记录表中查询是否有这个人的考勤记录(第三条)
		    $s_sql_3="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=3";
		    $s_r_3=$this->db->query($s_sql_3);
		    $num3=mysqli_num_rows($s_r_3);
		    
		    if($num3>0){
		        // 已有下午上班打卡考勤记录
		        $t=0;
		        foreach ($s_r_3  as  $v3) {
		            $arr['list'][]=array(
		                'type'=>$v3['type'],
		                'status'=>$v3['status'],
		                'time'=>$v3['kqsj'],
		                'signtime'=>$v3['signtime'],
		            );
		        }
		    }else{
		        // 没有type =3记录  比对
		        $t=2;
		        $sql_3="SELECT pmstime as stime,pmetime  as etime from hrt_kq_data where id=$aid";
		        $r3=$this->db->query($sql_3);
		        @$row3=mysqli_fetch_array($r3);
		        $bt_3=strtotime($day.$row3['stime'])-3600;
		        $st_3=strtotime($day.$row3['stime']);
		        $et_3=strtotime($day.$row3['etime']);
		        $nowdt_3=strtotime(date('Y-m-d H:i:s',time()));
		        if($nowdt_3<$bt_3){
		            $arr['list'][]=array(
		                'type'=>3,
		                'status'=>2,
		                'time'=>$row3['stime'],
		            );
		        }else if($bt_3<$nowdt_3 &&  $nowdt_3<$st_3){
		            $arr['list'][]=array(
		                'type'=>3,
		                'status'=>0,
		                'time'=>$row3['stime'],
		            );
		    
		        }else if($st_3<$nowdt_3   && $nowdt_3<$et_3){
		            $arr['list'][]=array(
		                'type'=>3,
		                'status'=>0,
		                'time'=>$row3['stime'],
		            );
		    
		        }else{
		            $arr['list'][]=array(
		                'type'=>3,
		                'status'=>1,
		                'time'=>$row3['stime'],
		            );
		        }
		    
		    }
		    //  4.先去考勤记录表中查询是否有这个人的考勤记录(第四条)
		    $s_sql_4="SELECT * from hrt_kq_records where uid=$uid and day='$day' and type=4";
		    $s_r_4=$this->db->query($s_sql_4);
		    $num4=mysqli_num_rows($s_r_4);
		    if($num4>0){
		        // 已有上午下班打卡考勤记录
		        foreach ($s_r_4  as  $v4) {
		            $arr['list'][]=array(
		                'type'=>$v4['type'],
		                'status'=>$v4['status'],
		                'time'=>$v4['kqsj'],
		                'signtime'=>$v4['signtime'],
		            );
		        }
		    }else{
		        // 没有type =4记录  比对
		        // $sql_4_1="SELECT *from hrt_kqsjgz where name='下午上班'";
		        // $r4_1=$this->db->query($sql_4_1);
		        // $row4_1=mysqli_fetch_array($r4_1);
		    
		        $sql_4="SELECT pmetime as stime from hrt_kq_data where id=$aid";
		        $r4=$this->db->query($sql_4);
		        @$row4=mysqli_fetch_array($r4);
		    
		        // $stt=strtotime($day.$row4_1['stime']);
		    
		        $st_4=strtotime($day.$row4['stime']);
		        $et_4=strtotime($day.'23:59');
		        $nowdt_4=strtotime(date('Y-m-d H:i:s',time()));
		        if($nowdt_4<$et_4){
		            if($arr['list'][2]['status']==1){
		                $arr['list'][]=array(
		                    'type'=>4,
		                    'status'=>0,
		                    'time'=>$row4['stime'],
		                );
		            }else{
		                $arr['list'][]=array(
		                    'type'=>4,
		                    'status'=>$s1,
		                    'time'=>$row4['stime'],
		                );
		            }
		    
		        }else{
		            $arr['list'][]=array(
		                'type'=>4,
		                'status'=>1,
		                'time'=>$row4['stime'],
		            );
		        }
		    }
		}
		$this->returnjson($arr);
	}


//	2.创建新考勤
	public function createKaoqinAction(){

		 $time=date('Y-m-d',time()+86400);

		 $data=json_decode($this->post('data'),true);
		 $isall=$data['isSelectedAllDepartment'];
		 $deptid=json_encode($data['selectedDepartmentList'],true);
		 $suid=json_encode($data['allowLookStatisticsUserList'],true);
		 $nuid=json_encode($data['noNeedAttendanceUserList'],true);
		 $lat=$data['latitude'];
		 $lng=$data['longitude'];
		 $address=$data['address'];
		 $range=$data['locationRange'];
		 $kqmodel=$data['attendanceMode'];
		 $amstime=$data['attendanceMorningUp'];
		 $ametime=$data['attendanceMorningDown'];
		 $pmstime=$data['attendanceAfternoonUp'];
		 $pmetime=$data['attendanceAfternoonDown'];
		 $kqdate=json_encode($data['attendanceDateList'],true);
		 $sxdate=$time;

		// $isall=$this->post('isSelectedAllDepartment');
		// $deptid=$this->post('selectedDepartmentList');
		// $suid=$this->post('allowLookStatisticsUserList');
		// $nuid=$this->post('noNeedAttendanceUserList');
		// $lat=$this->post('latitude');
		// $lng=$this->post('longitude');
		// $address=$this->post('address');
		// $range=$this->post('locationRange');
		// $kqmodel=$this->post('attendanceMode');
		// $amstime=$this->post('attendanceMorningUp');
		// $ametime=$this->post('attendanceMorningDown');
		// $pmstime=$this->post('attendanceAfternoonUp');
		// $pmetime=$this->post('attendanceAfternoonDown');
		// $kqdate=$this->post('attendanceDateList');

		$arr0=array(
				'isall'=>$isall,
				'deptid'=>$deptid,
				'suid'=>$suid,
				'nuid'=>$nuid,
				'lat'=>$lat,
				'lng'=>$lng,
				'address'=>$address,
				'range'=>$range,
				'kqmodel'=>$kqmodel,
				'amstime'=>$amstime,
				'ametime'=>$ametime,
				'pmstime'=>$pmstime,
				'pmetime'=>$pmetime,
				'kqdate'=>$kqdate,
				'sxdate'=>$time,

		);
		$r=$this->db->record('[Q]kq_data',$arr0);
		if($r){
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
			$this->returnjson($arr);
		}else{
			$this->showreturn('','创建失败',201);
		}
	}

      //3修改考勤
	  public  function editKaoqinAction(){

		 $data=json_decode($this->post('data'),true);
		 $id=$data['id'];
		 $isall=$data['isSelectedAllDepartment'];
		 $deptid=json_encode($data['selectedDepartmentList'],true);
		 $suid=json_encode($data['allowLookStatisticsUserList'],true);
		 $nuid=json_encode($data['noNeedAttendanceUserList'],true);
		 $lat=$data['latitude'];
		 $lng=$data['longitude'];
		 $address=$data['address'];
		 $range=$data['locationRange'];
		 $kqmodel=$data['attendanceMode'];
		 $amstime=$data['attendanceMorningUp'];
		 $ametime=$data['attendanceMorningDown'];
		 $pmstime=$data['attendanceAfternoonUp'];
		 $pmetime=$data['attendanceAfternoonDown'];
		 $kqdate=json_encode($data['attendanceDateList'],true);
		 $time=date('Y-m-d',time()+86400);
		 $sxdate=$time;


		  // $id=$this->post('id');
		  // $isall=$this->post('isSelectedAllDepartment');
		  // $deptid=$this->post('selectedDepartmentList');
		  // $suid=$this->post('allowLookStatisticsUserList');
		  // $nuid=$this->post('noNeedAttendanceUserList');
		  // $lat=$this->post('latitude');
		  // $lng=$this->post('longitude');
		  // $address=$this->post('address');
		  // $range=$this->post('locationRange');
		  // $kqmodel=$this->post('attendanceMode');
		  // $amstime=$this->post('attendanceMorningUp');
		  // $ametime=$this->post('attendanceMorningDown');
		  // $pmstime=$this->post('attendanceAfternoonUp');
		  // $pmetime=$this->post('attendanceAfternoonDown');
		  // $kqdate=$this->post('attendanceDateList');
		  // $sxdate=$time;

		  $arr0=array(
			  'exdate'=>$time,
		  );
		  $f0=array(
			  'id'=>$id,
		  );
		  $r0=$this->db->record('[Q]kq_data',$arr0,$f0);
		  if($r0){
			  $arr1=array(
				  'isall'=>$isall,
				  'deptid'=>$deptid,
				  'suid'=>$suid,
				  'nuid'=>$nuid,
				  'lat'=>$lat,
				  'lng'=>$lng,
				  'address'=>$address,
				  'range'=>$range,
				  'kqmodel'=>$kqmodel,
				  'amstime'=>$amstime,
				  'ametime'=>$ametime,
				  'pmstime'=>$pmstime,
				  'pmetime'=>$pmetime,
				  'kqdate'=>$kqdate,
				  'sxdate'=>$time,
			  );
			  $r1=$this->db->record('[Q]kq_data',$arr1);
			  if($r1){
				  $arr=array(
					  'code'=>200,
					  'msg'=>'成功',
				  );
				  $this->returnjson($arr);
			  }else{
				  $this->showreturn('','修改考勤失败',201);
			  }

		  }else{
			  $this->showreturn('','更新上次考勤失效时间失败',201);
		  }
	  }

//   4:获取已经创建的考勤列表
	public function alreadyCreatedListAction(){

		 $sql='SELECT *from hrt_kq_data where exdate is NULL';
		 $r=$this->db->query($sql);
		 if($r){
			 $arr=array(
			 'code'=>200,
			 'msg'=>'成功',
			 );
				//获取部门的信息
				$sql1='SELECT deptid from hrt_kq_data where exdate is NULL ';
				$r1=$this->db->query($sql1);
			 	foreach($r1 as $v1){
					$dpid=json_decode($v1['deptid'],true);
  					foreach($dpid as $i){
						$sql00='SELECT pid from hrt_dept where id='.$i;
						$r00=$this->db->query($sql00);
						$row=mysqli_fetch_array($r00);
						if($row['pid']==1){
							$dpidArr[]=array(
								'id'=>$i,
							);
						}else{
							$this->tmparr = array();
							$dpidArr[]=$this->test2($i);
						}
					}
				}
			 foreach($dpidArr as $k=>$nn){
				 $arr['departmentlist'][]=$nn;
			 }

			foreach($r as $v){
				//判断是否为生效
				$nowdt=time();
				$sdt=strtotime($v['sxdate']);
				$edt=$v['exdate'];
				if($edt !=null){
					$edts=strtotime($edt);
				}
				if($edt==null && $nowdt >$sdt){
					$isstartuse=1;
				}else if($edt==null && $nowdt<$sdt){
					$isstartuse=0;
				}else if($edt !=null && $nowdt<=$edts){
					$isstartuse=1;
				}else{
					$isstartuse=0;
				}

				$deptid=json_decode($v['deptid'],true);
				
				$suid=json_decode($v['suid'],true);
				$nuid=json_decode($v['nuid'],true);
				
				foreach ($suid as $sid){
				    $dr1=$this->db->query('SELECT face,name from hrt_admin where id='.$sid);
				    $dd1=mysqli_fetch_assoc($dr1);
				    $arr111[]=array(
				        'id'=>$sid,
				        'headiconurl'=>FACE.$dd1['face'],
				        'truename'=>$dd1['name'],
				    );
				}
				foreach ($nuid as $nid){
				    $dr2=$this->db->query('SELECT face,name from hrt_admin where id='.$nid);
				    $dd2=mysqli_fetch_assoc($dr2);
				    $arr222[]=array(
				        'id'=>$sid,
				        'headiconurl'=>FACE.$dd2['face'],
				        'truename'=>$dd2['name'],
				    );
				}
				
				$dpArr=array();
				foreach($deptid as $dd){
					$dsql='SELECT id,name from hrt_dept where id='.$dd;
					$dr=$this->db->query($dsql);
					$ddd=mysqli_fetch_assoc($dr);
					$dpArr[]=$ddd;
				}
				$arr['list'][]=array(
					'departmentlist'=>$dpArr,
					'id'=>$v['id'],
					'isall'=>$v['isall'],
					'latitude'=>$v['lat'],
					'longitude'=>$v['lng'],
					'address'=>$v['address'],
					'attendancemode'=>$v['kqmodel'],
					'attendancetimemorningup'=>$v['amstime'],
					'attendancetimemorningdown'=>$v['ametime'],
					'attendancetimeafternoonup'=>$v['pmstime'],
					'attendancetimeafternoondown'=>$v['pmetime'],
					'attendancedatelist'=>json_decode($v['kqdate'],true),
					'isstartuse'=>	$isstartuse,
				    
				    'allowlookstatisticsuserList'=>$arr111,
				    'noneedattendanceuserList'=>$arr222,
				);
			}
				}else{
					$arr=array(
						'code'=>200,
						'msg'=>'成功',
						'list'=>null,
						'departmentlist'=>null,
					);
				}

			$this->returnjson($arr);

	}

//		5.获取我可以查看统计的考勤列表
   		public  function mySeeCountListAction(){
			$data=json_decode($this->post('data'),true);
			$uid=$data['userId'];


			// $uid=$this->post('userId');

			$sql='SELECT   id,suid from hrt_kq_data where exdate is NULL ';
			$r=$this->db->query($sql);
			foreach($r as $v){
				 $suid=json_decode($v['suid'],true);

				 if(in_array((int)$uid,$suid)){
						$ids[]=$v['id'];
				 }else{
						$ids[]='';
				 }
			}
			$arr=array(
				'code'=>200,
				'msg'=>'成功',
			);
			$idArr=array_filter($ids);
			if(empty($idArr)){
				$arr['list']=null;
			}
			foreach($idArr as $d){
				$csql='SELECT *from hrt_kq_data where id='.$d;
				$cr=$this->db->query($csql);
				$crow=mysqli_fetch_array($cr);

				$deptid=json_decode($crow['deptid'],true);
				$dpArr=array();
				foreach($deptid as $dd){
					$dsql='SELECT id,name from hrt_dept where id='.$dd;
					$dr=$this->db->query($dsql);
					$ddd=mysqli_fetch_assoc($dr);
					$dpArr[]=$ddd;
				}
				$arr['list'][]=array(
					'departmentlist'=>$dpArr,
					'id'=>$crow['id'],
					'isall'=>$crow['isall'],
					'latitude'=>$crow['lat'],
					'longitude'=>$crow['lng'],
					'address'=>$crow['address'],
					'attendancemode'=>$crow['kqmodel'],
					'attendancetimemorningup'=>$crow['amstime'],
					'attendancetimemorningdown'=>$crow['ametime'],
					'attendancetimeafternoonup'=>$crow['pmstime'],
					'attendancetimeafternoondown'=>$crow['pmetime'],
					'attendancedatelist'=>json_decode($crow['kqdate'],true),
				);

			}
     		 $this->returnjson($arr);
		}
//		6.签到/签退
        public function signAction(){
			$data=json_decode($this->post('data'),true);
			$uid=$data['userId'];
			$type=$data['type'];
			$lat=$data['latitude'];
			$lng=$data['longitude'];
			$address=$data['address'];
			$day=date('Y-m-d',time());
			$time=date('H:i:s',time());

			    // $uid=$this->post('userId');
			    // $type=$this->post('type');
			    // $lat=$this->post('latitude');
			    // $lng=$this->post('longitude');
			    // $address=$this->post('address');
			    // $day=date('Y-m-d',time());
			    // $time=date('H:i:s',time());
//			根据uid查询出遵循的考勤规则即考勤的id
			$dpsql='SELECT deptid from hrt_admin where id='.$uid;
			$dr=$this->db->query($dpsql);
			$drow=mysqli_fetch_array($dr);
			$deptid= $drow['deptid'];

			$d_s='SELECT id,deptid from hrt_kq_data where exdate is NULL ';

			$d_r=$this->db->query($d_s);
			foreach($d_r as $rr){

				$dpid=json_decode($rr['deptid'],true);

				if(in_array((int)$deptid,$dpid)){
						$aids[]=$rr['id'];
				}else{
						$aids[]='';
				}
			}
			$aidArr=array_filter($aids);
			$aid=implode(',',$aidArr);  //考勤id


			// 查询出考勤的时间
			switch($type){
				case 1:
					$sql='SELECT amstime as stime from hrt_kq_data where id='.$aid;
					break;
				case 2:
					$sql='SELECT ametime as stime from hrt_kq_data where id='.$aid;
					break;
				case 3:
					$sql='SELECT pmstime as stime from hrt_kq_data where id='.$aid;
					break;
				default:
					$sql='SELECT pmetime as stime from hrt_kq_data where id='.$aid;

			}

			$r=$this->db->query($sql);
			$row=mysqli_fetch_array($r);
			$kqtime=$row['stime'];
			// echo $row['stime'];

			// 判断范围
			$l_sql='SELECT lat,lng,`range` from hrt_kq_data where id='.$aid;
			$l_r=$this->db->query($l_sql);
			$l_row=mysqli_fetch_array($l_r);
			$location=$this->getDistance($lat, $lng, $l_row['lat'], $l_row['lng']);

			$count=0;
			if($location<=$l_row['range']){
				$count=$count+1;
			}
			if($count>0){
				// 在考勤范围内比对时间
				$settime=strtotime($day.$kqtime);
				$nowtime=strtotime(date('Y-m-d H:i:s',time()));
				$f0=array(
					'uid'=>$uid,
					'type'=>$type,
					'day'=>$day,
				);
				$del=$this->db->delete('[Q]kq_records',$f0);
				if($type==2 || $type==4){
					if($nowtime<$settime){
						$arr_kq=array(
							'uid'=>$uid,
							'day'=>$day,
							'type'=>$type,
							'status'=>0,
							'kqsj'=>$kqtime,
							'address'=>$address,
							'lat'=>$lat,
							'lng'=>$lng,
							'signtime'=>$time,
							'result'=>4,
							'aid'=>$aid,
						);
						$r_kq=$this->db->record('[Q]kq_records',$arr_kq);
						if($r_kq){
							$arr=array(
								'code'=>200,
								'msg'=>'成功',
								'result'=>4,
								'signtime'=>$time,
							);
						}else{
							$this->showreturn('','考勤范围内下班签到写入失败',201);
						}
					}else{
						$f0=array(
							'uid'=>$uid,
							'type'=>$type,
							'day'=>$day,
						);
						$del=$this->db->delete('[Q]kq_records',$f0);
						$arr_kq=array(
							'uid'=>$uid,
							'day'=>$day,
							'type'=>$type,
							'status'=>3,
							'kqsj'=>$row['stime'],
							'address'=>$address,
							'lat'=>$lat,
							'lng'=>$lng,
							'signtime'=>$time,
							'result'=>1,
							'aid'=>$aid,
						);
						$r_kq=$this->db->record('[Q]kq_records',$arr_kq);
						if($r_kq){
							$arr=array(
								'code'=>200,
								'msg'=>'成功',
								'result'=>1,
								'signtime'=>$time,
							);
						}else{
							$this->showreturn('','考勤范围内迟到写入失败',201);
						}
					}
				}else{
					//上班签到情况
					if($nowtime<$settime){
						$arr_kq=array(
							'uid'=>$uid,
							'day'=>$day,
							'type'=>$type,
							'status'=>3,
							'kqsj'=>$kqtime,
							'address'=>$address,
							'lat'=>$lat,
							'lng'=>$lng,
							'signtime'=>$time,
							'result'=>1,
							'aid'=>$aid,
						);
						$r_kq=$this->db->record('[Q]kq_records',$arr_kq);
						if($r_kq){
							$arr=array(
								'code'=>200,
								'msg'=>'成功',
								'result'=>1,
								'signtime'=>$time,
							);
						}else{
							$this->showreturn('','考勤范围内下班签到写入失败',201);
						}
					}else{
						$f0=array(
							'uid'=>$uid,
							'type'=>$type,
							'day'=>$day,
						);
						$del=$this->db->delete('[Q]kq_records',$f0);
						$arr_kq=array(
							'uid'=>$uid,
							'day'=>$day,
							'type'=>$type,
							'status'=>0,
							'kqsj'=>$row['stime'],
							'address'=>$address,
							'lat'=>$lat,
							'lng'=>$lng,
							'signtime'=>$time,
							'result'=>2,
							'aid'=>$aid,
						);
						$r_kq=$this->db->record('[Q]kq_records',$arr_kq);
						if($r_kq){
							$arr=array(
								'code'=>200,
								'msg'=>'成功',
								'result'=>2,
								'signtime'=>$time,
							);
						}else{
							$this->showreturn('','考勤范围内迟到写入失败',201);
						}
					}
				}



			}else{
				// 在考勤范围外
				$f0=array(
					'uid'=>$uid,
					'type'=>$type,
					'day'=>$day,
				);
				$del=$this->db->delete('[Q]kq_records',$f0);

				$arr_rw=array(
					'uid'=>$uid,
					'day'=>$day,
					'type'=>$type,
					'status'=>0,
					'kqsj'=>$row['stime'],
					'address'=>$address,
					'lat'=>$lat,
					'lng'=>$lng,
					'signtime'=>$time,
					'result'=>3,
					'aid'=>$aid,
				);

				$r_rw=$this->db->record('[Q]kq_records',$arr_rw);
				if($r_rw){
					$arr=array(
						'code'=>200,
						'msg'=>'成功',
						'result'=>3,
						'signtime'=>$time,
					);
				}else{
					$this->showreturn('','考勤范围外写入失败',201);
				}

			}

			$this->returnjson($arr);
		}
//	7提交签到异常原因的信息
	public  function   submitReasonAction(){
		$data=json_decode($this->post('data'),true);
		$uid=$data['userId'];
		$type=$data['type'];
		$exceptionType=$data['exceptionType'];
		$content=$data['content'];
		$img=json_encode($data['imageList'],true);
		$day=date('Y-m-d',time());


		// $uid=$this->post('userId');
		// $type=$this->post('type');
		// $exceptionType=$this->post('exceptionType');
		// $content=$this->post('content');
		// $img=$this->post('imageList');
		// $auditUserId=$this->post('auditUserId');
		// $day=date('Y-m-d',time());

		$arr0=array(
			'content'=>$content,
			'exceptiontype'=>$exceptionType,
			'imglist'=>$img,
			'status'=>3,
		);
		$f0=array(
			'uid'=>$uid,
			'type'=>$type,
			'day'=>$day,
		);
		$r0=$this->db->record('[Q]kq_records',$arr0,$f0);
		if($r0){

			$arr=array(
				'code'=>200,
				'msg'=>'成功',

			);
			$this->returnjson($arr);
		}else{
			$this->showreturn('','提交考勤异常原因失败',201);
		}


	}

//		8考勤统计日期列表
     public  function  kaoqinDatelistAction(){
		 $data=json_decode($this->post('data'),true);
		 $id=$data['attendanceId'];

		 // $id=$this->post('attendanceId');


		 $sql='SELECT kqdate,sxdate from hrt_kq_data where id='.$id;
		 $result=$this->db->query($sql);
		 $row=mysqli_fetch_array($result);
		 $datelist=$row['kqdate'];
		 $weekday=json_decode($datelist,true);  //数组
//		 print_r($weekday);

//		 $weeks=implode(',',$weekday);
//		 echo $weeks;   如  1,2,3,4,5

//		   先获取当前的时间戳
		  $dt=time();
		  $day=86400;
			 $arr=array(
			 'code'=>200,
			 'msg'=>'成功',
		 );
	    $days = $this->timedif(time(),strtotime($row['sxdate']));
	    if($days>40){
	        $bl = 40;
	    }else{
	        $bl = $days;
	    }
//		 向前推40天
		  for($i=0;$i<=$bl;$i++) {
			  $st = $dt - $day * $i;
			  $w = date('w', $st);
			  if (in_array($w, $weekday)) {
				  $dd = $st;
			  } else {
				  $dd = '';
			  }
			  $dArr[] = $dd;
		  }

		 $dArrs=array_filter($dArr);
		 foreach($dArrs as $v){
			 $arr['list'][]=$v;
		 }
		 $this->returnjson($arr);
	 }
 
 public function timedif($begin_time,$end_time){
     if($begin_time < $end_time){
         $starttime = $begin_time;
         $endtime = $end_time;
     }else{
         $starttime = $end_time;
         $endtime = $begin_time;
     }
     //计算天数
     $timediff = $endtime-$starttime;
     $days = intval($timediff/86400);
     return $days;
 }

//9获取某日期下的考勤记录详情
	public function kqDataDetailAction(){
		$data=json_decode($this->post('data'),true);
		$date=$data['date'];
		$uid=$data['userId'];
		$aid=$data['attendanceId'];

// 		 $date=$this->post('date');
// 		 $uid=$this->post('userId');
// 		 $aid=$this->post('attendanceId');
		// 计算出此考勤规则下的人员总数
		$query=$this->db->query('SELECT kqmodel from hrt_kq_data where id='.$aid);
		$list=mysqli_fetch_array($query);
		$arr=array(
			'code'=>200,
			'msg'=>'成功',
		);
		if($list['kqmodel']==1){
		    for($i=1;$i<5;$i=$i+3){
		        switch($i){
		            case 1:
		                $sql='SELECT amstime as stime from hrt_kq_data where id='.$aid;
		                break;
		            default:
		                $sql='SELECT pmetime as stime from hrt_kq_data where id='.$aid;
		        }
		        $r=$this->db->query($sql);
		        $row=mysqli_fetch_array($r);
		        
		        $sql_1="SELECT count(1) as count1 from hrt_kq_records where day='$date' and result=1  and type=$i ";
		        $r_1=$this->db->query($sql_1);
		        $row_1=mysqli_fetch_array($r_1);
		        
		        $sql_2="SELECT count(1) as count2 from hrt_kq_records where day='$date' and result=2  and type=$i ";
		        $r_2=$this->db->query($sql_2);
		        $row_2=mysqli_fetch_array($r_2);
		        
		        $sql_3="SELECT count(1) as count3 from hrt_kq_records where day='$date' and result=3  and type=$i ";
		        $r_3=$this->db->query($sql_3);
		        $row_3=mysqli_fetch_array($r_3);
		        
		        $sql_4="SELECT count(1) as count4 from hrt_kq_records where day='$date' and result=4  and type=$i ";
		        $r_4=$this->db->query($sql_4);
		        $row_4=mysqli_fetch_array($r_4);
		        
		        // 计算出此考勤规则下的人员总数
		        $deptsql='SELECT deptid,kqmodel from hrt_kq_data where id='.$aid;
		        $d_r=$this->db->query($deptsql);
		        $drow=mysqli_fetch_array($d_r);
		         
		        $dpid=json_decode($drow['deptid'],true);
		        
		        $list2 = array();
		        foreach($dpid as $dd){
                   $list3 = $this->getDeptid($dd,$arrs=array($dd));
                   foreach ($list3 as $l3){
                       $list2[] = $l3;
                   }
		        }
		        $list2 = array_unique($list2);
		        $total = array();
		        foreach($list2 as $key=>$dd){
		            $num='SELECT count(1) as count from hrt_admin where deptid='.$dd;
		            $nr=$this->db->query($num);
		            $count=mysqli_fetch_array($nr);
		            $total[]=$count['count'];
		        }
		        $tn=array_sum($total);  //人员总数
		        // 未签到的人数
		        $unsignnum=$tn-$row_1['count1']-$row_2['count2']-$row_3['count3']-$row_4['count4'];
		         
		        $dArr[]=array(
		            'type'=>$i,
		            'signtime'=>$row['stime'],
		            'ontimenum'=>$row_1['count1'],
		            'onlatenum'=>$row_2['count2'],
		            'onquitenum'=>$row_4['count4'],
		            'outrangenum'=>$row_3['count3'],
		            'unsignnum'=>$unsignnum,
		        );
		    }
		}else{
		    for($i=1;$i<5;$i++){
		        //			查询出考勤的时间
		        switch($i){
		            case 1:
		                $sql='SELECT amstime as stime from hrt_kq_data where id='.$aid;
		                break;
		            case 2:
		                $sql='SELECT ametime as stime from hrt_kq_data where id='.$aid;
		                break;
		            case 3:
		                $sql='SELECT pmstime as stime from hrt_kq_data where id='.$aid;
		                break;
		            default:
		                $sql='SELECT pmetime as stime from hrt_kq_data where id='.$aid;
		    
		        }
		        $r=$this->db->query($sql);
		        $row=mysqli_fetch_array($r);
		    
		    
		        $sql_1="SELECT count(1) as count1 from hrt_kq_records where day='$date' and result=1  and type=$i ";
		        $r_1=$this->db->query($sql_1);
		        $row_1=mysqli_fetch_array($r_1);
		    
		        $sql_2="SELECT count(1) as count2 from hrt_kq_records where day='$date' and result=2  and type=$i ";
		        $r_2=$this->db->query($sql_2);
		        $row_2=mysqli_fetch_array($r_2);
		    
		        $sql_3="SELECT count(1) as count3 from hrt_kq_records where day='$date' and result=3  and type=$i ";
		        $r_3=$this->db->query($sql_3);
		        $row_3=mysqli_fetch_array($r_3);
		    
		        $sql_4="SELECT count(1) as count4 from hrt_kq_records where day='$date' and result=4  and type=$i ";
		        $r_4=$this->db->query($sql_4);
		        $row_4=mysqli_fetch_array($r_4);
		    
		        // 计算出此考勤规则下的人员总数
		        $deptsql='SELECT deptid,kqmodel from hrt_kq_data where id='.$aid;
		        $d_r=$this->db->query($deptsql);
		        $drow=mysqli_fetch_array($d_r);
		         
		        $dpid=json_decode($drow['deptid'],true);
		        
		        $list2 = array();
		        foreach($dpid as $dd){
		           $list2[] = $dd;
                   $list3 = $this->getDeptid($dd);
                   foreach ($list3 as $l3){
                       $list2[] = $l3;
                   }
		        }
		        $list2 = array_unique($list2);
		        $total = array();
		        foreach($list2 as $key=>$dd){
		            $num='SELECT count(1) as count from hrt_admin where deptid='.$dd;
		            $nr=$this->db->query($num);
		            $count=mysqli_fetch_array($nr);
		            $total[]=$count['count'];
		        }
		        $tn=array_sum($total);  //人员总数
		    
		        // 未签到的人数
		        $unsignnum=$tn-$row_1['count1']-$row_2['count2']-$row_3['count3']-$row_4['count4'];
		        	
		        $dArr[]=array(
		            'type'=>$i,
		            'signtime'=>$row['stime'],
		            'ontimenum'=>$row_1['count1'],
		            'onlatenum'=>$row_2['count2'],
		            'onquitenum'=>$row_4['count4'],
		            'outrangenum'=>$row_3['count3'],
		            'unsignnum'=>$unsignnum,
		        );
		    }
		}

		$arr['list']=$dArr;
		$arr['attendancemode']=$drow['kqmodel'];
		$this->returnjson($arr);

	}

//10考勤统计人员列表
	public  function kqMemberListAction(){
		$data=json_decode($this->post('data'),true);
		$date=$data['date'];
		$type=$data['attendanceType'];
		$result=$data['resultType'];
		$aid=$data['attendanceId'];

// 		$date=$this->post('date');
// 		$type=$this->post('attendanceType');
// 		$result=$this->post('resultType');
//      	$aid=$this->post('attendanceId');
		$arr=array(
			'code'=>200,
			'msg'=>'成功',
		);
		if($result==100){
			$sql="SELECT  a.id ,a.uid,b.face,b.name,b.deptname,b.ranking,
         	a.signtime,a.address,a.content,
			from hrt_kq_records  a
			left join hrt_admin  b  on a.uid=b.id
		    where a.day='$date' and a.type=$type and a.exceptiontype is null and a.aid=$aid";

			$r=$this->db->query($sql);
			$num=mysqli_num_rows($r);
			if($num>0){

				foreach($r as $v){
					$arr['list'][]=array(
						'id'=>$v['id'],
						'userid'=>$v['uid'],
						'headiconurl'=>FACE.$v['face'],
						'truename'=>$v['name'],
						'departmentname'=>$v['deptname'],
						'positionname'=>$v['ranking'],

						'signtime'=>$v['signtime'],
						'address'=>$v['address'],
						'content'=>$v['content'],

					);

				}
			}else{
				$arr['list']=null;
			}
		}else if($result==200){
			//未签到/签退的
			// 已经签到完毕的
			$u_sql="SELECT uid from hrt_kq_records where day='$date' and  status !=0 and type=$type  and aid=$aid";

			$u_r=$this->db->query($u_sql);
			$num=mysqli_num_rows($u_r);
			if($num>0){
				foreach ($u_r as $uv) {
					$ids[]=$uv['uid'];
				}
				// 计算出此考勤规则下的人员总数
				$deptsql='SELECT deptid from hrt_kq_data where id='.$aid;
				$d_r=$this->db->query($deptsql);
				$drow=mysqli_fetch_array($d_r);
				$dpid=json_decode($drow['deptid'],true);
				
				$list2 = array();
				$tids = array();
				
				foreach($dpid as $dd){
				    $list3 = $this->getDeptid($dd,$arrs=array($dd));
				    foreach ($list3 as $l3){
				        $list2[] = $l3;
				    }
				}
				foreach($list2 as $l2){
				    $num='SELECT id from hrt_admin where deptid='.$l2;
				    $nr=$this->db->query($num);
				    foreach ($nr as $c1){
				        $tids[]=$c1['id'];
				    }
				}
				
// 				foreach($dpid as $dd){
// 					echo $num='SELECT id  from hrt_admin where deptid='.$dd;
// 					$nr=$this->db->query($num);
// 					$count=mysqli_fetch_array($nr);
// 					$tids[]=$count['id'];
// 				}
				// 未签到的
				$idArr=array_diff($tids,$ids);
				//$idArr=array_unique($tids);
				foreach($idArr as $i){
					$i_sql='SELECT id,face,name,deptname,ranking from hrt_admin where id='.$i;
					$i_r=$this->db->query($i_sql);
					$i_row=mysqli_fetch_array($i_r);
					$arr['list'][]=array(
						'userid'=>$i_row['id'],
						'headiconurl'=>FACE.$i_row['face'],
						'truename'=>$i_row['name'],
						'departmentname'=>$i_row['deptname'],
						'positionname'=>$i_row['ranking'],
					);
				}
			}else{
				$deptsql='SELECT deptid from hrt_kq_data where id='.$aid;
				$d_r=$this->db->query($deptsql);
				$drow=mysqli_fetch_array($d_r);
				$dpid=json_decode($drow['deptid'],true);
				
				$list2 = array();
				$tids = array();
				
				foreach($dpid as $dd){
				    $list3 = $this->getDeptid($dd,$arrs=array($dd));
				    foreach ($list3 as $l3){
				        $list2[] = $l3;
				    }
				}
				
				foreach($list2 as $l2){
					$num='SELECT id from hrt_admin where deptid='.$l2;
					$nr=$this->db->query($num);
					foreach ($nr as $c1){
					    $tids[]=$c1['id'];
					}
				}
				$tids = array_unique($tids);
				// 未签到的
				foreach($tids as $i){
					$i_sql='SELECT id,face,name,deptname,ranking from hrt_admin where id='.$i;
					$i_r=$this->db->query($i_sql);
					$i_row=mysqli_fetch_array($i_r);
					$arr['list'][]=array(
						'userid'=>$i_row['id'],
						'headiconurl'=>FACE.$i_row['face'],
						'truename'=>$i_row['name'],
						'departmentname'=>$i_row['deptname'],
						'positionname'=>$i_row['ranking'],
					);
				}
			}
		}else{
			$sql="SELECT  a.id ,a.uid,b.face,b.name,b.deptname,b.ranking,
         	a.signtime,a.address,a.content
			from hrt_kq_records  a
			left join hrt_admin  b  on a.uid=b.id
		    where a.day='$date' and a.type=$type and a.exceptiontype=$result and a.aid=$aid";
			$r=$this->db->query($sql);
			$num=mysqli_num_rows($r);
			if($num>0){
				foreach($r as $v){
					$arr['list'][]=array(
						'id'=>$v['id'],
						'userid'=>$v['uid'],
						'headiconurl'=>FACE.$v['face'],
						'truename'=>$v['name'],
						'departmentname'=>$v['deptname'],
						'positionname'=>$v['ranking'],
						'signtime'=>$v['signtime'],
						'address'=>$v['address'],
						'content'=>$v['content'],
					);
				}
			}else{
				$arr['list']=null;
			}
		}
		$this->returnjson($arr);
	}

//11.考勤不在范围的统计
	public function kqUnrangeCountAction(){
		$data=json_decode($this->post('data'),true);
		$date=$data['date'];
		$type=$data['attendanceType'];
		$aid=$data['attendanceId'];

		// $date=$this->post('date');
		// $type=$this->post('attendanceType');


		$sql_1="SELECT count(1) as count1 from hrt_kq_records where type=$type  and day='$date'  and exceptiontype=2 and aid=$aid";
		$r_1=$this->db->query($sql_1);
		$row_1=mysqli_fetch_array($r_1);
		// echo $row1['count1'];


		$sql_2="SELECT count(1) as count2 from hrt_kq_records where type=$type and day='$date'  and exceptiontype=3 and aid=$aid";
		$r_2=$this->db->query($sql_2);
		$row_2=mysqli_fetch_array($r_2);
		// echo $row1['count2'];

		$sql_3="SELECT count(1) as count3 from hrt_kq_records where type=$type and day='$date'  and exceptiontype=4 and aid=$aid";
		$r_3=$this->db->query($sql_3);
		$row_3=mysqli_fetch_array($r_3);
		// echo $row3['count3'];

		$arr=array(
			'code'=>200,
			'msg'=>'成功',
			'waiqinnum'=>$row_1['count1'],
			'chuchainum'=>$row_2['count2'],
			'othernum'=>$row_3['count3'],
		);

		$this->returnjson($arr);
	}
    //删除考勤
    public function kqdeleteAction(){
        $data=json_decode($this->post('data'),true);
        $id=$data['attendanceId'];
        $arr=array(
            'id'=>$id,
        );
        $result=$this->db->delete('[Q]kq_data',$arr);
        if($result){
            $this->returnjson($arr=array(
                'code'=>200,
                'msg'=>'成功',
            ));
        }else{
            $this->showreturn('','删除考勤失败',201);
        }
    }


/*------------------------递归查询部门--------------------------*/
	function getDeptid($id,&$arr){
	    $sql='select id,pid from hrt_dept where pid='.$id;
	    $result=$this->db->query($sql);
	    if($result){
	        while($rows=mysqli_fetch_array($result)){ //循环记录集
	            $arr[] = $rows['id']; //组合数组
	            $this->getDeptid($rows['id'],$arr); //调用函数，传入参数，继续查询下级
	        }
	    }
	    return $arr;
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


/*---------------------------------------------------------------------------*/

//根据经纬度计算直线距离
	function getDistance($lat1, $lng1, $lat2, $lng2)
	{
		$earthRadius = 6367000; //approximate radius of earth in meters

		/*
          Convert these degrees to radians
          to work with the formula
        */

		$lat1 = ($lat1 * pi() ) / 180;
		$lng1 = ($lng1 * pi() ) / 180;

		$lat2 = ($lat2 * pi() ) / 180;
		$lng2 = ($lng2 * pi() ) / 180;

		/*
          Using the
          Haversine formula

          http://en.wikipedia.org/wiki/Haversine_formula

          calculate the distance
        */

		$calcLongitude = $lng2 - $lng1;
		$calcLatitude = $lat2 - $lat1;
		$stepOne = pow(sin($calcLatitude / 2), 2) + cos($lat1) * cos($lat2) * pow(sin($calcLongitude / 2), 2);  $stepTwo = 2 * asin(min(1, sqrt($stepOne)));
		$calculatedDistance = $earthRadius * $stepTwo;

		return round($calculatedDistance);
	}















}