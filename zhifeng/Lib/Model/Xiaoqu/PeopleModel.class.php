<?php
class PeopleModel extends Model{

	//自动验证
	protected $_validate=array(
			array('phone','require','手机号必须填写！',1,'',self::MODEL_BOTH),
			array('password','require','用户密码必须填写！',1,'',self::MODEL_BOTH),
			array('password_c','password','两次密码不一致',1,'confirm',self::MODEL_BOTH),
			array('phone','','该手机号已经注册！',1,'unique',self::MODEL_BOTH)
	);

	protected $_auto = array (
			array('name','getName',self::MODEL_INSERT,'callback'), 
			array('password','md5',self::MODEL_BOTH,'function'),
			array('password_c','md5',self::MODEL_BOTH,'function'),
			array('create_time','getdatatime',self::MODEL_INSERT,'callback'),
			array('lastlogin_time','getdatatime',self::MODEL_BOTH,'callback'), 
			array('status','1')
	);
	
	public function getName(){
		$name = trim($_POST['name']);
		if (empty($name)) return '手机用户'.mt_rand(100000,999999);
		return $name; 
	}
	
	Public function getdatatime(){
		return date("Y-m-d H:i:s",time());
	}
	
	/**
	 * 密码登录,返回people记录数组,失败返回FALSE
	 * @param unknown $phone
	 * @param unknown $password
	 * @param unknown $error
	 */
	public function login( $phone, $password, &$error ){
		$rs = $this->where(array('phone'=>$phone,'password'=>md5($password)))->find();
		if (empty($rs)) {
			$error = '手机号或者密码错误';
			return false;
		}else{
			if ($rs['status'] == 0){
				$error = '帐号已被冻结';
				return false;
			}else{
				//更新最后登录时间
				$this->where(array('phone'=>$phone,'password'=>md5($password)))->setField('lastlogin_time',$this->getdatatime());
				return $rs;
			}
		}
	}
	
	/**
	 * 找回密码
	 */
	public function retrieve($phone) {
		// 生成新的密码
		$new_pw = mt_rand(10000000,99999999);
		
		// 保存到数据库
		if ($this->where(array('phone'=>$phone))->setField('password',md5($new_pw))){
			return $new_pw;
		}else{
			return false;
		}
	}
	
	public function setPassword($id,$pw){
		return $this->where(array('id'=>$id))->setField('password',md5($pw));
	}
	
	public function setName($id,$name){
		return $this->where(array('id'=>$id))->setField('name',$name); 
	}
	
	/**
	 * 根据手机号码获取用户数据
	 * @param unknown $phone
	 */
	public function getPeople($phone) {
		$people = $this->where(array('phone'=>$phone))->find();
		return $people;
	}
}