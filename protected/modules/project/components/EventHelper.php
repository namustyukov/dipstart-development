<?php

class EventHelper {
    
    const TYPE_CREATE_ORDER = 1;            // Пользователь %..% оформил заказ
    const TYPE_EDIT_ORDER = 2;              // Пользователь %..% изменил информацию о заказе
    const TYPE_ADD_CHANGES = 3;             // Пользователь %..% прикрепил замечания
    const TYPE_NOTIFICATION = 4;            //
    const TYPE_MESSAGE = 5;                 //
    const TYPE_UPDATE_PROFILE = 6;          // Пользователь %..% изменил информацию профиля
	const TYPE_CHEK_UPLOADED = 7;           // Пользователь %..% загрузил чек
	const TYPE_NEW_FILE_IN_STAGE = 8;       // Пользователь %..% загрузил часть работы
	const TYPE_MATERIALS_ADDED = 9;         // Пользователь %..% прикрепил файлы в заказ
	const TYPE_MATERIALS_DELETED = 10;      // Пользователь %..% удалил файл из заказа
	const TYPE_ORDER_PAYED = 11;            // Пользователь %..% оплатил заказ
	const TYPE_STAGE_DONE_BY_EXECUTOR = 12;
	const TYPE_STAGE_DONE_BY_CUSTOMER = 13;
	const TYPE_CUSTOMER_REGISTRED = 14;      // Пользователь %..% зарегистрировался
	const TYPE_ORDER_MANAGER_INFORMED = 15;  // Напоминание
	const TYPE_ORDER_STAGE_EXPIRED = 16;     // Срок сдачи этапа
	const TYPE_ACCEPTED_ORDER = 17;          // Заказ проверен тех. руком
    const STATUS_ACTIVE = 0;
    const STATUS_DONE = 1;

	protected static $moderate_types = array(
		TYPE_CREATE_ORDER,
		TYPE_EDIT_ORDER,
		TYPE_ADD_CHANGES,
		TYPE_UPDATE_PROFILE,
		TYPE_CHEK_UPLOADED,
		TYPE_MATERIALS_ADDED,
		TYPE_MATERIALS_DELETED,
	);
	
    public static function get_moderate_types_string() {
		$moderate_types_string = 
			self::TYPE_CREATE_ORDER.','.
			self::TYPE_EDIT_ORDER.','.
			self::TYPE_ADD_CHANGES.','.
			self::TYPE_UPDATE_PROFILE.','.
			self::TYPE_CHEK_UPLOADED.','.
			self::TYPE_MATERIALS_ADDED.','.
			self::TYPE_MATERIALS_DELETED;
		return	$moderate_types_string;
	}	
    
    protected static function sendEvent($event, $type, $description) {
        
        $message = new Events;
        if (!$type) {
            $type = 'Не передан тип';
        }
        if (!$description) {
            $description = 'Отсутствует описание';
        }
        $message->type = $type;
        $message->event_id = $event;
        $message->description = $description;
        $message->timestamp = time();
        $message->status = self::STATUS_ACTIVE;
        $message->save();

        return $message->id;
    }
    
    protected static function eventTemplate($type, $description, $id) {
        
    }

    public static function createOrder($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = Yii::t('site','User').' '.$userName." ".UserModule::t('created order');
        self::sendEvent($id, self::TYPE_CREATE_ORDER, $description);
    }

    public static function editOrder($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = Yii::t('site','User').' '.$userName." ".UserModule::t('edited order');
        return self::sendEvent($id, self::TYPE_EDIT_ORDER, $description);
    }
    
    public static function addChanges($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = Yii::t('site','User').' '.$userName." ".UserModule::t('attached comments');
        self::sendEvent($id, self::TYPE_ADD_CHANGES, $description);
    }
    public static function chekUploaded($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = Yii::t('site','User').' '.$userName." ".UserModule::t('uploaded check');
        self::sendEvent($id, self::TYPE_CHEK_UPLOADED, $description);
    }
    public static function newFileInStage($id, $title) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = Yii::t('site','User').' '.$userName." ".UserModule::t('have uploaded part of the job')." '$title'";
        self::sendEvent($id, self::TYPE_NEW_FILE_IN_STAGE, $description);
    }
    public static function materialsAdded($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = Yii::t('site','User').' '.$userName." ".UserModule::t('attached files in order');
        self::sendEvent($id, self::TYPE_MATERIALS_ADDED, $description);
    }
    public static function materialsDeleted($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = 'Пользователь '.$userName.' '.UserModule::t('deleted the file out of order');
        self::sendEvent($id, self::TYPE_MATERIALS_DELETED, $description);
    }

    public static function addMessage($id,$message='') {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
        $description = UserModule::t('User').' '.$userName.' '.UserModule::t('left a message').": \"$message.\"";
        self::sendEvent($id, self::TYPE_MESSAGE, $description);
    }
    
    public static function notification($text, $creator) {
        self::sendEvent($creator, self::TYPE_NOTIFICATION, $text);
    }

    public static function managerInformed($creator) {
		$text = UserModule::t('Order reminder');
        self::sendEvent($creator, self::TYPE_ORDER_MANAGER_INFORMED, $text);
    }
	public static function stageExpired($creator) {
		$text = UserModule::t('Stage expired');
        self::sendEvent($creator, self::TYPE_ORDER_STAGE_EXPIRED, $text);
    }
	
    public static function updateProfile() {
        $creator = Yii::app()->user->id;
        $userName = User::model()->findByPk($creator)->username;
		$text = UserModule::t('The user changed data in the profile')." $userName"; 
        return self::sendEvent($creator, self::TYPE_UPDATE_PROFILE, $text);
    }
	
    public static function payForOrder($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
		$description = Yii::t('site','User').' '.$userName." ".UserModule::t('payed for order');
        return self::sendEvent($id, self::TYPE_ORDER_PAYED, $description);
    }
	
    public static function stageDoneByExecutor($id, $title) {
		$description = ProjectModule::t('Executor finished the stage')." '$title'";
        self::sendEvent($id, self::TYPE_STAGE_DONE_BY_EXECUTOR, $description);
    }
	
    public static function stageDoneByCustomer($id, $title) {
		$description = ProjectModule::t('The customer accepted the stage')." '$title'";
        self::sendEvent($id, self::TYPE_STAGE_DONE_BY_CUSTOMER, $description);
    }

    public static function newCustomer() {
        $creator = Yii::app()->user->id;
        $user = User::model()->findByPk($creator);
		if($user->full_name) $name4link = $user->full_name;
		else $name4link = $user->email;
		$text = UserModule::t('New customer {link} have registred',array('{link}'=>'<a href="/user/admin/update/id/'.$creator.'">'.$name4link.'</a>')); 
        return self::sendEvent($creator, self::TYPE_CUSTOMER_REGISTRED, $text);
    }
	
    public static function correctorAccepted($id) {
        $userName = User::model()->findByPk(Yii::app()->user->id)->username;
        $description = Yii::t('site','User').' '.$userName." ".UserModule::t('accepted order');
        return self::sendEvent($id, self::TYPE_ACCEPTED_ORDER, $description);
	}
}
