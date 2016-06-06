<?php

class User extends CActiveRecord
{
	const STATUS_NOACTIVE=0;
	const STATUS_ACTIVE=1;
	const STATUS_BANNED=-1;
	public $PRIORITY_ROLES = ['Admin', 'Manager', 'Customer', 'Author'];

	/**
	 * The followings are the available columns in table 'users':
	 * @var integer $id
	 * @var string $username
	 * @var string $full_name
	 * @var string $password
	 * @var string $email
	 * @var string $activkey
	 * @var integer $createtime
	 * @var integer $lastvisit
	 * @var integer $superuser
	 * @var integer $status
	 * @var timestamp $create_at
	 * @var timestamp $lastvisit_at
	 */

	public function getAttributes($name=true)
	{
		return parent::getAttributes($name);
	}

	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return Company::getId().'_Users';
		//return Yii::app()->getModule('user')->tableUsers;
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.CConsoleApplication
		return ((get_class(Yii::app())=='CConsoleApplication' || (get_class(Yii::app())!='CConsoleApplication' && Yii::app()->getModule('user')->isAdmin()))?array(
			array('password', 'length', 'max'=>128, 'min' => 4,'message' => UserModule::t("Incorrect password (minimal length 4 symbols).")),
			array('email', 'email'),
			array('email', 'length', 'min' => 6,'message' => UserModule::t("Incorrect password (minimal length 6 symbols).")),
			array('email', 'unique','message' => UserModule::t("This email already exists.")),
			array('username', 'length', 'max'=>20, 'min' => 3,'message' => UserModule::t("Incorrect username (length between 3 and 20 characters).")),
			array('username', 'unique', 'message' => UserModule::t("This user's name already exists.")),
			array('username', 'match', 'pattern' => '/^[-A-Za-z0-9_]+$/u','message' => UserModule::t("Incorrect symbols (A-z0-9).")),
			array('full_name', 'match', 'pattern' => '/^[-а-яА-ЯёЁa-zA-Z_ ]+$/u','message' => UserModule::t("Incorrect symbols (A-z).")),
			array('status', 'in', 'range'=>array(self::STATUS_NOACTIVE,self::STATUS_ACTIVE,self::STATUS_BANNED)),
			array('create_at', 'default', 'value' => date('Y-m-d H:i:s'), 'setOnEmpty' => true, 'on' => 'insert'),
			array('lastvisit_at', 'default', 'value' => '0000-00-00 00:00:00', 'setOnEmpty' => true, 'on' => 'insert'),
			array('email, superuser, status', 'required'),
			array('superuser', 'in', 'range'=>array(0,1)),
			array('superuser, status', 'numerical', 'integerOnly'=>true),
			array('phone_number', 'match', 'pattern' => '/^[-+()0-9 ]+$/u','message' => UserModule::t("Incorrect symbols (0-9,+,-,(,)).")),
			array('id, username, password, email, activkey, create_at, lastvisit_at, superuser, status, phone_number, roles', 'safe', 'on'=>'search'),
		):((Yii::app()->user->id==$this->id)?array(
			array('email', 'required','except'=>'social_network'),
			array('phone_number', 'match', 'pattern' => '/^[-+()0-9 ]+$/u','message' => UserModule::t("Incorrect symbols (0-9,+,-,(,)).")),
			array('full_name', 'length', 'max'=>128, 'min' => 3,'message' => UserModule::t("Incorrect username (length between 3 and 20 characters).")),
			array('email', 'email'),
			array('email', 'length', 'min' => 6,'message' => UserModule::t("Incorrect password (minimal length 4 symbols).")),
			array('email', 'unique','message' => UserModule::t("This email already exists.")),
			//array('username', 'unique', 'message' => UserModule::t("This user's name already exists."),'except'=>'social_network'),
			//array('username', 'length', 'max'=>20, 'min' => 3,'message' => UserModule::t("Incorrect username (length between 3 and 20 characters).")),
			//array('username', 'match', 'pattern' => '/^[-A-Za-z0-9_]+$/u','message' => UserModule::t("Incorrect symbols (A-z0-9)."),'except'=>'social_network'),
			array('id, identity, network, email, full_name, state, pid, phone_number, roles', 'safe', 'on'=>'search')
		):array()));
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
        $relations['profile'] = array(self::HAS_ONE, 'Profile', 'user_id');
        $relations['zakaz'] = array(self::HAS_MANY, 'Zakaz', 'user_id');
        $relations['zakaz_executor'] = array(self::HAS_MANY, 'Zakaz', 'executor');
        //$relations['zakaz_stage'] = array(self::HAS_MANY, 'ZakazParts', 'author_id');
        $relations['AuthAssignment'] = array(self::HAS_ONE, 'AuthAssignment', 'userid');
		$relations['roles'] = array(self::HAS_MANY, 'AuthAssignment', 'userid');
		return $relations;
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => UserModule::t("Id"),
			'username'=>UserModule::t("username"),
			'full_name'=>UserModule::t("fullname"),
			'password'=>UserModule::t("password"),
			'verifyPassword'=>UserModule::t("Retype Password"),
			'email'=>UserModule::t("E-mail"),
			'verifyCode'=>UserModule::t("Verification Code"),
			'activkey' => UserModule::t("activation key"),
			'createtime' => UserModule::t("Registration date"),
			'create_at' => UserModule::t("Registration date"),
			'lastvisit_at' => UserModule::t("Last visit"),
			'superuser' => UserModule::t("Superuser"),
			'status' => UserModule::t("Status"),
			//'phone_number' => UserModule::t("Phone number"),
			'phone_number' => UserModule::t('Cell number'),
			'roles' => UserModule::t('Roles'),
		);
	}

	public function scopes()
	{
		return array(
			'active'=>array(
				'condition'=>'status='.self::STATUS_ACTIVE,
			),
			'notactive'=>array(
				'condition'=>'status='.self::STATUS_NOACTIVE,
			),
			'banned'=>array(
				'condition'=>'status='.self::STATUS_BANNED,
			),
			'superuser'=>array(
				'condition'=>'superuser=1',
			),
			'notsafe'=>array(
				'select' => 'id, username, full_name, password, email, activkey, create_at, lastvisit_at, superuser, status',
			),
		);
	}

	public function defaultScope()
	{
		return CMap::mergeArray(Yii::app()->getModule('user')->defaultScope,array(
			'alias'=>'user',
			'select' => 'user.id, user.phone_number, user.username, user.full_name, user.email, user.create_at, user.lastvisit_at, user.superuser, user.status, user.pid',
		));
	}

	public static function itemAlias($type,$code=NULL) {
		$_items = array(
			'UserStatus' => array(
				self::STATUS_NOACTIVE => UserModule::t('Not active'),
				self::STATUS_ACTIVE => UserModule::t('Active'),
				self::STATUS_BANNED => UserModule::t('Banned'),
			),
			'AdminStatus' => array(
				'0' => UserModule::t('No'),
				'1' => UserModule::t('Yes'),
			),
			'roles' => array(
				'Admin' => UserModule::t('Admin'),
				'Manager' => UserModule::t('Manager'),
				'Customer' => UserModule::t('Customer'),
				'Author' =>  UserModule::t('Executor'),
				'Corrector' =>  UserModule::t('Corrector'),
				'Webmaster'=>  UserModule::t('Webmaster'),
			),
		);
		if (isset($code))
			return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
		else
			return isset($_items[$type]) ? $_items[$type] : false;
	}

/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
	 */
	public function search()
	{
		// Warning: Please modify the following code to remove attributes that
		// should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('identity',$this->identity,true);
		$criteria->compare('network',$this->network,true);
		$criteria->compare('email',$this->email,true);
		$criteria->compare('full_name',$this->full_name,true);
		$criteria->compare('phone_number',$this->phone_number,true);
		$criteria->compare('state',$this->state);
		
		$criteria->compare('username',$this->username,true);
		$criteria->compare('password',$this->password);
		//$criteria->compare('activkey',$this->activkey);
		$criteria->compare('create_at',$this->create_at);
		$criteria->compare('lastvisit_at',$this->lastvisit_at);
		$criteria->compare('superuser',$this->superuser);
		$criteria->compare('status',$this->status);
		$criteria->with = 'AuthAssignment';
		$criteria->compare('AuthAssignment.itemname',$this->roles,true);

		return new CActiveDataProvider(get_class($this), array(
			'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>Yii::app()->getModule('user')->user_page_size,
			),
		));
	}
	public function getUserRole($userId = false) {
		$authorizer = Rights::module()->getAuthorizer();
		if($userId) {
			$roles = $authorizer->getAuthItems(2, $userId);
		}
		elseif (Yii::app()->user->id == 0) {
			return 'root';
		} else {
			$roles = $authorizer->getAuthItems(2, Yii::app()->user->id);
		}
		$role =  array_keys($roles);
		foreach ($role as $item)
			if (in_array($item, $this->PRIORITY_ROLES)) $priority = $item;
		return  $priority ? $priority : $role[0];
	}
	public function getUserRoleArr($userId = false) {
		$authorizer = Rights::module()->getAuthorizer();
		if($userId) {
			$roles = $authorizer->getAuthItems(2, $userId);
		}
		else {
			$roles = $authorizer->getAuthItems(2, Yii::app()->user->id);
		}
		$role = array_keys($roles);
		return $role;
	}
	public function isAdmin(){
		 if (Yii::app()->user->id && $this->getUserRole()=='Admin')
			return TRUE;
		 else    return FALSE;
	 }
	public function isManager(){
		 if (Yii::app()->user->id && $this->getUserRole()=='Manager' || $this->getUserRole()=='Admin')
			return TRUE;
		 else    return FALSE;
	 }
	public function isCustomer(){
	   if (Yii::app()->user->id && $this->getUserRole()=='Customer')
		return TRUE;
	   else    return FALSE;
	}

	public function isAuthor(){
		if (Yii::app()->user->id && $this->getUserRole()=='Author') return true;
		else    return FALSE;
	}
	public function isCorrector(){
		$roles = $this->getUserRoleArr();
		if (Yii::app()->user->id && in_array('Author', $roles) && in_array('Corrector', $roles)) return true;
		else    return FALSE;
	}
	public function isExecutor($project_id){
		if (Yii::app()->user->id && $this->getUserRole()=='Author') {
			$zakaz = Zakaz::model()->findByPk($project_id);
			if(Yii::app()->user->id == $zakaz->executor)
				return true;
			else return false;
		} else return FALSE;
	}
	public function isOwner($project_id){
		if (Yii::app()->user->id && $this->getUserRole()=='Customer') {
			$zakaz = Zakaz::model()->findByPk($project_id);
			if(Yii::app()->user->id == $zakaz->user_id)
				return true;
			else return false;
		} else return FALSE;
	}

	public function getCreatetime() {
		return strtotime($this->create_at);
	}

	public function setCreatetime($value) {
		$this->create_at=date('Y-m-d H:i:s',$value);
	}

	public function getLastvisit() {
		return strtotime($this->lastvisit_at);
	}

	public function setLastvisit($value) {
		$this->lastvisit_at=date('Y-m-d H:i:s',$value);
	}

	public function findAllAuthors(){
		$sql = ('SELECT DISTINCT `id`, `email` FROM '.$this->tableName().' WHERE `id` IN (SELECT `userid` FROM '.Company::getId().'_AuthAssignment WHERE `itemname` = "Author")');
	   return $this->findAllBySql($sql);
	}
	public function findAllCustomers(){
		$sql = ('SELECT DISTINCT `id`, `email` FROM '.$this->tableName().' WHERE `id` IN (SELECT `userid` FROM '.Company::getId().'_AuthAssignment WHERE `itemname` = "Customer")');
	   return $this->findAllBySql($sql);
	}
	
	public function findAllNotificationExecutors() {
		return User::model()->with(
			array(
				'AuthAssignment'=>array('select'=>false, 'joinType'=>'INNER JOIN', 'condition'=>'AuthAssignment.itemname="Author"'),
				'profile'=>array('select'=>'profile.notification_time', 'joinType'=>'INNER JOIN', 'condition'=>'profile.notification="1"')
			)
		)->findAll();
	}

	public function printRoles(){
		$roles = $this->roles;
		$answer = '';
		foreach($roles as $role)
			$answer .= self::itemAlias('roles',$role->itemname).' ';
		return $answer;
	}
}
