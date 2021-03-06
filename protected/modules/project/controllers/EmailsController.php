<?php

class EmailsController extends Controller
{
	private	$subject = 'Notification';
    protected $_request;
    protected $_response;
	
    public function filters() {
		return array(
			'accessControl'
		);
	}
	public function accessRules()
	{
        return array(
            array('allow',
                'actions'=>array('index','send'),
                //'users'=>array('admin','manager'),
				'expression' => array('EmailsController', 'allowManagers'),
            ),
            array('deny',  // deny all users
                'users'=>array('*'),
            ),
        );
	}
	public static function allowManagers(){
		if(User::model()->isManager()) {
			return true;
		} else {
			return false;
		}
	}
	
    /*Вызов методов для работы с json*/
    protected function _prepairJson() {
        $this->_request = Yii::app()->jsonRequest;
        $this->_response = new JsonHttpResponse();
    }
	
	public function actionIndex()
	{
	}
    
    public function actionSend()
    {
		$email = new Emails;
		
		$this->_prepairJson();

		$orderId = $this->_request->getParam('orderId');
		$typeId = $this->_request->getParam('typeId');
		$back = $this->_request->getParam('back');
		$cost = $this->_request->getParam('cost');

		$order	 = Zakaz::model()->findByPk($orderId);
		
		$arr_type = array(
				Emails::TYPE_18,
				Emails::TYPE_19,
				Emails::TYPE_20,
				Emails::TYPE_21,
				Emails::TYPE_22,
				Emails::TYPE_23,
				Emails::TYPE_24,
				//Emails::TYPE_25,
		);
		if (in_array($typeId,$arr_type)) {
			$user_id = $order->executor;
		} else {
			$user_id = $order->user_id;
		};
		if (!$user_id) Yii::app()->end();
			
		$user = User::model()->findByPk($user_id);

		$email->to_id = $user_id;
		$profile = Profile::model()->findAll("`user_id`='$user_id'");
		
		$rec   = Templates::model()->findAll("`type_id`='$typeId'");
		
		$title = $rec[0]->title;
		$email->name = $profle->full_name;
		if (strlen($email->name) < 2) $email->name = $user->username;
		$email->login= $user->username;
		
		$email->num_order = $orderId;
		$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
		$email->message = $rec[0]->text;
		$email->price_order = $cost;
		$email->sum_order  = $cost;
		
		$specials = Catalog::model()->findByPk($order->specials);
		$email->specialization	= $specials->cat_name;
		$email->name_order		= $order->title;		
		$email->subject_order	= $order->title;		
		$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $typeId);
    }
}