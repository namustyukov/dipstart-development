<?php
/**
 * YiiChatDbHandlerBase (YiiChat A Software Interface for YiiChat Source Providers)
 *	serve this widget using a database.
 *	required: post.sql
 *
 *	this class is invoked because you specify it in your YiiChatWidget
 *	arguments passed to the widget.
 *
 *	this is the object fields required: (as an indexed array)
 *
 *		'id'				the post unique id
 *		'text'				the post text
 *		'time'				the time stamp
 *		'owner'				the name of the person who make this post
 *		'post_identity'		the ID of the person who make this post
 *
 *	the both methods in this handler receive:
 *
 *		$chat_id			the id provided in the widget, to discrimine
 *							between various chats.
 *
 *		$identity			the identity (ID) of the person who is in chat
 *							(the post_identity field is the same as $identity
 *							only when we are creating a post: yiichat_post
 *
 *		$data				a user-defined value passed from the widget
 *
 * @uses CWidget
 * @version 1.0
 * @author Christian Salazar <christiansalazarh@gmail.com>
 * @license FREE BSD
 */
abstract class YiiChatDbHandlerBase extends CComponent implements IYiiChat {

	protected $_identity;
	protected $_chat_id;
	protected $_data;

	protected function getIdentity(){ return $this->_identity; }
	protected function getChatId(){ return $this->_chat_id; }
	protected function getData(){ return $this->_data; }

	// abstract optional
	protected function getTableName(){
		return Company::getId().'_ProjectMessages';
		/*$campaign = Company::search_by_domain($_SERVER['SERVER_NAME']);
		if ($campaign->id) {
			return '`'.$campaign->id.'_ProjectMessages`';
		} else {
			return "`ProjectMessages`";
		}*/
	}

	// abstract strict
	protected function getDb(){}
	protected function getIdentityName(){}
	protected function getDateFormatted($value){}
	protected function createPostUniqueId(){}
	protected function acceptMessage($message){}

	/**
	 	post a message into your database.
	 */
	public function yiichat_dapprove($post) {
		$model = ProjectMessages::model()->findByPk($post['id']);
		$model->moderated = 1;
		
		$orderId = $model->order;

		/*if($model->recipient > 0) {
			$user = User::model()->findByPk($model->recipient);
			//$profile = Profile::model()->findAll("`user_id`='$model->recipient'");
			
			$role = $user->getUserRole($user->id);
			if($role == 'Customer') $type_id = Emails::TYPE_16; // Заказчику о сообщении в чате
			if($role == 'Author') $type_id = Emails::TYPE_20; // Исполнителю о сообщении в чате
			
			$email = new Emails;
			$rec   = Templates::model()->findAll("`type_id`='$type_id'");
			$email->name = $user->full_name;
			if (strlen($email->name) < 2) $email->name = $user->username;
			$email->num_order = $orderId;
			$email->message = $model->message;
			$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
			$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $type_id);
		}*/

		if($user = User::model()->findByPk($model->recipient)) {
			$role = $user->getUserRole($user->id);
			if($role == 'Customer') {
				$type_id = Emails::TYPE_16; // Заказчику о сообщении в чате
				$orderModel = Zakaz::model()->findByPk($orderId);
				$orderModel->setCustomerEvents(1);
			}
			if($role == 'Author') {
				$type_id = Emails::TYPE_20; // Исполнителю о сообщении в чате
				$orderModel = Zakaz::model()->findByPk($orderId);
				$orderModel->setExecutorEvents(2);
			}
			
			$email = new Emails;
			$rec   = Templates::model()->findAll("`type_id`='$type_id'");
			$email->name = $user->full_name;
			if (strlen($email->name) < 2) $email->name = $user->username;
			$email->num_order = $orderId;
			$email->message = $model->message;
			$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$orderId;
			$email->sendTo( $user->email, $rec[0]->title, $rec[0]->text, $type_id);
		}
		return $model->save();
	}
	public function yiichat_dpost($post){
		$model = ProjectMessages::model()->findByPk($post['id']);
		$model->delete();
	}
	public function yiichat_dtoggle($post){
		
		$model = Zakaz::model()->findByPk($post['chat_id']);
        
		if ($_GET['data']=='unset') {
			$model->executor = $post['ex'];
			$model->status = 4;
		}
		else {
			$model->executor = 0;
			$model->status = 3;
		}
		return $model->save();
	}
	public function yiichat_post($chat_id, $identity, $message, $postdata, $data){
		$this->_chat_id = $chat_id;
		$this->_identity = $identity;
		$this->_data = $data;
		$message_filtered = trim($this->acceptMessage($message));
		if($message_filtered != ""){
			$obj = array(
				"order"=>$chat_id,
				"sender"=>$identity,
				"sender_role"=>ProjectMessages::model()->getRoleId(User::model()->getUserRole($identity)),
				"date"=>date('Y-m-d H:i:s'),
				"message"=>$message_filtered,
			);
			$order = Zakaz::model()->findByPk($chat_id);
			if (isset($postdata['index']) && ($postdata['index']==0)){
				if ($postdata['recipient']=='Author'){
					$obj['recipient'] = $order->attributes['executor'];
					$obj['recipient_role'] = ProjectMessages::model()->getRoleId('Author');
                    if ($obj['recipient']==0) $obj['recipient']=-1;
				} elseif ($postdata['recipient']=='Customer') {
					$obj['recipient'] = $order->attributes['user_id'];
					$obj['recipient_role'] = ProjectMessages::model()->getRoleId('Customer');
				/*} elseif (isset($postdata['recipient'])){
					$obj['recipient'] = $postdata['recipient'];*/
				} elseif ($postdata['recipient']=='Corrector') {
					$obj['recipient_role'] = ProjectMessages::model()->getRoleId('Corrector');
					$obj['recipient']=-2;
				} else $obj['recipient']=0;
				$newid=$this->getDb()->createCommand()->insert($this->getTableName(),$obj);
			}
			else {
				if ($postdata['recipient']=='no') {
					$obj['recipient']=0;
                    $newid=$this->getDb()->createCommand()->update($this->getTableName(), $obj, 'id=:id', array('id' => $postdata['index']));
				}
				else if($postdata['recipient']=='redir'){
                    $sender = ProjectMessages::model()->findByPk($postdata['index'])->senderObject->AuthAssignment['itemname'];
                    $obj['sender'] = ProjectMessages::model()->findByPk($postdata['index'])->attributes['sender'];
                    $obj['moderated']=1;
                    if ($sender=='Customer') $obj['recipient']=$order->attributes['executor'];
                    if ($sender=='Author') $obj['recipient']=$order->attributes['user'];
                    $newid=$this->getDb()->createCommand()->insert($this->getTableName(),$obj);

					}
				else {
                    if ($postdata['recipient']=='Customer') {
                    	$obj['recipient']=$order->attributes['user_id'];
                    	$obj['recipient_role'] = ProjectMessages::model()->getRoleId('Customer');
                    }
                    if ($postdata['recipient']=='Author') {
                    	$obj['recipient']=$order->attributes['executor'];
                    	$obj['recipient_role'] = ProjectMessages::model()->getRoleId('Author');
                    }
                    if ($postdata['recipient']=='Corrector') {
                    	$obj['recipient']=-2;
                    	$obj['recipient_role'] = ProjectMessages::model()->getRoleId('Corrector');
                    }
                    if (is_numeric($postdata['recipient'])) {
                    	$obj['recipient']=$postdata['recipient'];
                    	$obj['recipient_role'] = ProjectMessages::model()->findByPk($postdata['index'])->attributes['sender_role'];
                    }
					$newid=$this->getDb()->createCommand()->insert($this->getTableName(),$obj);
				}
			}
			// now retrieve the post
            $obj['recipient']=User::model()->findByPk($obj['recipient']);
            if($obj['recipient']) $obj['recipient']->superuser = $obj['recipient']->getRelated('AuthAssignment');
            $obj['sender']=User::model()->findByPk($obj['sender']);
            $obj['sender']->superuser=$obj['sender']->getRelated('AuthAssignment');
			$specials = isset($order->specials)?$order->specials:$order->specials2;
			$title = Catalog::model()->findByPk($specials)->cat_name . '. ' . $order->attributes['title'] . '. '.Yii::t('site','You have received a new message.');
			$message = CHtml::link(Yii::t('site','Link to order page'), Yii::app()->createAbsoluteUrl('/project/chat', array('orderId' => $chat_id))).'<br />'.$obj['message'];
			$headers = 'From: no-reply@'.$_SERVER['SERVER_NAME'] . "\r\n" .
				'Reply-To: no-reply@'.$_SERVER['SERVER_NAME'] . "\r\n" .
				'Content-Type: text/html; charset=utf-8;' .
				'X-Mailer: PHP/' . phpversion();
			//mail($obj['recipient']->attributes['email'],$title,$message,$headers);
			
			/*if (User::model()->getUserRole($obj['recipient']->id)=='Customer') {
				$type_id = Emails::TYPE_16;
				$order->setCustomerEvents(1);
			} else if (User::model()->getUserRole($obj['recipient']->id)=='Author') {
				$type_id = Emails::TYPE_20;
				$order->setExecutorEvents(2);
			}
			$email = new Emails;
			$rec   = Templates::model()->findAll("`type_id`='$type_id'");
			$email->name = $obj['recipient']->full_name;
			if (strlen($email->name) < 2) $email->name = $obj['recipient']->username;
			$email->num_order = $chat_id;
			$email->message = strip_tags($obj['message']);
			$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$chat_id;
			$email->sendTo( $obj['recipient']->email, $rec[0]->title, $rec[0]->text, $type_id);*/
			
            if ($postdata['flags'])
                foreach($postdata['flags'] as $v)
                    switch($v){
                        case 'send_sms':
                            include_once "smsc_api.php";
                            list($sms_id, $sms_cnt, $cost, $balance) = send_sms(str_replace(['+','-'],'',$obj['recipient']->attributes['phone_number']), $obj['message']);
//                            list($sms_id, $sms_cnt, $cost, $balance) = send_sms(str_replace(['+','-'],'',$obj['recipient']->profile->attributes['mob_tel']), $obj['message']);
                            break;
                        case 'send_email':
							if (User::model()->getUserRole($obj['recipient']->id)=='Customer') {
								$type_id = Emails::TYPE_16;
							} else if (User::model()->getUserRole($obj['recipient']->id)=='Author') {
								$type_id = Emails::TYPE_20;
							}
							$email = new Emails;
							$rec   = Templates::model()->findAll("`type_id`='$type_id'");
							$email->name = $obj['recipient']->full_name;
							if (strlen($email->name) < 2) $email->name = $obj['recipient']->username;
							$email->num_order = $chat_id;
							$email->message = strip_tags($obj['message']);
							$email->page_order = 'http://'.$_SERVER['SERVER_NAME'].'/project/chat?orderId='.$chat_id;
							$email->sendTo( $obj['recipient']->email, $rec[0]->title, $rec[0]->text, $type_id);
                            break;
                    }
			$obj['time'] = date_format(date_create($obj['date']), 'd.m.Y H:i:s');
			$obj['owner']=substr($this->getIdentityName(),0,20);
			return $obj;
		}
		else return array();
	}
	public function yiichat_list_posts($chat_id, $identity, $last_id, $data){
		$this->_chat_id = $chat_id;
		$this->_identity = $identity;
		$this->_data = $data;
		$messages=ProjectMessages::model()->findAll('`t`.`order` = :chat_id AND `t`.`id` > :last_id',array(':chat_id'=>$chat_id,':last_id'=>$last_id));
		foreach ($messages as $m) {
			$m->date = date_format(date_create($m->date), 'd.m.Y H:i:s');
		}
		return $messages;
	}

	/**
	 	retrieve the last posts since the last_id, must be used
		only when the records has been filtered (case timer).
	 */
	private function getLastPosts($rows, $limit, $last_id){
		if(count($rows)==0)
			return array();
		$n=-1;
		for($i=0;$i<count($rows);$i++)
			if($rows[$i]['id']==$last_id){
				$n=$i;
				break;
			}
		if($last_id=='' || $last_id==null){
			if($n==-1)
				$n = $i-1;
			if($n==0){
				// TEST CASE: 7
				return $rows;
			}else{
				// TEST CASES: 6 and 8
				$cnk2 = array_chunk($rows, $limit);
				return array_reverse($cnk2[0]);
			}
		}
		if($n > 0){
			$cnk = array_chunk($rows,$n);
			$cnk2 = array_chunk($cnk[0], $limit);
			return array_reverse($cnk2[0]);
		}else
		{
			return array();
		}
	}
}
?>
