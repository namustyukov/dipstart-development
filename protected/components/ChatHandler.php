<?php
class ChatHandler extends YiiChatDbHandlerBase {
	//
	// IMPORTANT:
	// in any time here you can use this available methods:
	//  getData(), getIdentity(), getChatId()
	//
	protected function getTableName(){
		return Company::getId().'_ProjectMessages';
	}
	protected function getDb(){
		// the application database
		return Yii::app()->db;
	}
	protected function createPostUniqueId(){
		// generates a unique id. 40 char.
		return hash('sha1',$this->getChatId().time().rand(1000,9999));
	}
	protected function getIdentityName(){
		// find the identity name here
		// example:
		//  $model = MyPeople::model()->findByPk($this->getIdentity());
		//  return $model->userFullName();
		return User::model()->findByPk($this->getIdentity())->username;
	}
	protected function getDateFormatted($value){
		// format the date numeric $value
		return Yii::app()->format->formatDateTime($value);
	}
	protected function acceptMessage($message){
		// return false to reject it, elsewere return $message
		return $message;
	}
	public function yiichat_list_posts($chat_id, $identity, $last_id, $data){
        $res=parent::yiichat_list_posts($chat_id, $identity, $last_id, $data);
        if (count($res)>0)
            $order=Zakaz::model()->findByPk($chat_id);
	
        foreach ($res as $k=>$v) {
            $res1[$k]=$v->attributes;
            $res1[$k]['sender']=array();
            $res1[$k]['sender']['fullusername']=$res[$k]->senderObject->email;
            if($res[$k]->senderObject) $res1[$k]['sender']['superuser']=$res[$k]->senderObject->getRelated('AuthAssignment')->attributes; // При удалённом пользователе необходима проверка
            $res1[$k]['sender']['rating'] = (int)$res[$k]->senderObject->profile->rating;
            
            switch($res1[$k]['sender']['superuser']['itemname']){
                case 'Admin':
                    $res1[$k]['sender']['username']=ProjectModule::t('Admin');//'Админ';
                    break;
                case 'Manager':
                    $res1[$k]['sender']['username']=ProjectModule::t('Manager');//'Менеджер';
                    break;
                case 'Author':
                    if (ProjectMessages::model()->getRole($res1[$k]['sender_role']) == 'Corrector')
                        $res1[$k]['sender']['username']=ProjectModule::t('Corrector');//'Тех.рук';
                    else
                        $res1[$k]['sender']['username']=ProjectModule::t('Executor');//'Автор';
                    break;
                case 'Customer':
                    $res1[$k]['sender']['username']=ProjectModule::t('Customer');//'Заказчик';
                    break;
            }
            
            $res1[$k]['sender']['username']=$res1[$k]['sender']['fullusername'];
			if ($res[$k]->recipient > 0) {
                $res1[$k]['recipient'] = array();
                $res1[$k]['recipient']['fullusername']=$res[$k]->recipientObject->email;
                $res1[$k]['recipient']['superuser']=$res[$k]->recipientObject->getRelated('AuthAssignment')->attributes;
                switch ($res1[$k]['recipient']['superuser']['itemname']){
                    case 'Admin':
                        $res1[$k]['recipient']['username']=ProjectModule::t('to admin');//'админу';
                        break;
                    case 'Manager':
                        $res1[$k]['recipient']['username']=ProjectModule::t('to manager');//'менеджеру';
                        break;
                    case 'Author':
                        if (ProjectMessages::model()->getRole($res1[$k]['recipient_role']) == 'Corrector')
                            $res1[$k]['recipient']['username']=ProjectModule::t('to corrector');//'тех.руку';
                        else
                            $res1[$k]['recipient']['username']=ProjectModule::t('to executor');//'автору';
                        break;
                    case 'Customer':
                        $res1[$k]['recipient']['username']=ProjectModule::t('to customer');//'заказчику';
                        break;
                }
                //$res1[$k]['recipient']['username']=$res1[$k]['recipient']['fullusername'];
            }

            if ($res1[$k]['sender_role'])
                $res1[$k]['sender_role'] = ProjectMessages::model()->getRole($res1[$k]['sender_role']);
		}
		return $res1;
	}
}
