<?php
class UserinfoAction extends BaseAction{
	public function index(){
		$agent = $_SERVER['HTTP_USER_AGENT']; 
		if(!strpos($agent,"icroMessenger")) {
			//echo '此功能只能在微信浏览器中使用';exit;
		}
		$cardid=intval($this->_get('cardid'));
		//$sql=D('Userinfo');
		$card = D('Member_card_create'); 
		$data['wecha_id']=$this->_get('wecha_id');
		$data['token']=$this->_get('token');
		//
		$cardInfoRow['wecha_id']=$this->_get('wecha_id');
		$cardInfoRow['token']=$this->_get('token');
		$cardInfoRow['cardid']=$this->_get('cardid');
		$cardinfo=$card->where($cardInfoRow)->find(); //是否领取过
		$this->assign('cardInfo',$cardinfo);
		//
		$member_card_set_db=M('Member_card_set');
		$thisCard=$member_card_set_db->where(array('token'=>$this->_get('token'),'wecha_id'=>$data['wecha_id']))->find();
		//dump($thisCard);exit;
		if (!$thisCard&&$cardid){
			exit('无法查询数据！');
		}
		//
		$sql=D('Userinfo');
		$userinfo=$sql->where($data)->find();
		if($thisCard['memberinfo']!=false){
			$img=$thisCard['memberinfo'];			
		}else{
			$img=RES.'/images/userinfo/fans.jpg';
		}
		$this->assign('cardnum',$cardinfo['number']);
		$this->assign('homepic',$img);
		$this->assign('info',$userinfo);
		$this->assign('token',$this->_get('token'));
		$this->assign('wecha_id',$this->_get('wecha_id'));
		$this->assign('cardid',$cardid);
		if(IS_POST){
			//如果有post提交，说明是修改
			$data['wechaname'] = $this->_post('wechaname');
			$data['tel']       = $this->_post('tel');
			if(empty($data['wechaname']) || empty($data['tel'])){
				$this->error("微信号或手机号必填。");exit;
			}

			$data['truename']  = $this->_post('truename');			
			$data['qq']  = $this->_post('qq');
			$data['sex'] = $this->_post('sex');
			$data['age'] = $this->_post('age');
			$data['portrait'] = $this->_post('portrait');
			
 			//如果会员卡不为空[更新]
 			//写入两个表 Userinfo Member_card_create 
			if ($cardid==0){ 		
				$infoWhere=array();
				$infoWhere['wecha_id']=$data['wecha_id'];
				$infoWhere['token']=$data['token'];
				$userInfoExist=M('Userinfo')->where($infoWhere)->find();
				if ($userInfoExist){
					M('Userinfo')->where($infoWhere)->save($data);
				}else {
					M('Userinfo')->add($data);
				}
				echo 1;exit;	
 			}else {
				if($cardinfo){ //如果Member_card_create 不为空，说明领过卡，但是可以修改会员信息
					$update['wecha_id']=$data['wecha_id'];
					$update['token']=$data['token'];
					unset($data['wecha_id']);
					unset($data['token']);
					if(M('Userinfo')->where($update)->save($data)){
						echo 1;exit;
					}else{
						echo 0;exit;
					}
 				}else{
				
 					$card=M('Member_card_create')->field('id,number')->where("token='".$this->_get('token')."' and cardid=".intval($_POST['cardid'])." and wecha_id = ''")->order('id ASC')->find();
 					//
 					$userinfo_db=M('Userinfo');
 					$userInfos=$userinfo_db->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id')))->select();
 					$userScore=0;
 					if ($userInfos){
 						$userScore=intval($userInfos[0]['total_score']);
 						$userInfo=$userInfos[0];
 					}
 					if(!$card){
 						echo 3;exit;
 					}else {
 						//
 						if (intval($thisCard['miniscore'])==0||$userScore>intval($thisCard['minscore'])){
 							M('Member_card_create')->where(array('token'=>$this->_get('token'),'wecha_id'=>$this->_get('wecha_id')))->delete();
 							$card_up=M('Member_card_create')->where(array('id'=>$card['id']))->save(array('wecha_id'=>$this->_get('wecha_id')));
 							$data['getcardtime']=time();
 							if ($userinfo){
 								$update['wecha_id']=$data['wecha_id'];
 								$update['token']=$data['token'];
 								M('Userinfo')->where($update)->save($data);
 							}else {
 								M('Userinfo')->data($data)->add();
 							}
 							echo 2;exit;
 						}else {
 							echo 4;exit;
 						}
 					}

 				} //post
 			}
		}else {
			$this->display();	
		}
    } //end function index
} // end class UserinfoAction

?>