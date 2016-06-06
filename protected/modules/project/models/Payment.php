<?php

/**
 * This is the model class for table "Payment".
 *
 * The followings are the available columns in table 'Payment':
 * @property integer $id
 * @property integer $order_id
 * @property string $receive_date
 * @property string $pay_date
 * @property string $theme
 * @property string $manager
 * @property string $user
 * @property double $summ
 * @property integer $details_type
 * @property string $details_number
 * @property integer $payment_type
 * @property integer $approve
 * @property string $method
 */
class Payment extends CActiveRecord {

	// Payment types
	const INCOMING_CUSTOMER   = 0;
	const OUTCOMING_EXECUTOR  = 1;
	const OUTCOMING_WEBMASTER = 2;
	const OUTCOMING_CUSTOMER  = 3; // Refound
	
	// Payment statuses
	const FREE = 0;
	const APPROVED = 1;
	const REJECTED = 2;
	
	public function tableName() {
		return Company::getId().'_Payment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, order_id, payment_type, approve', 'numerical', 'integerOnly'=>true),
			array('summ, details_type', 'numerical'),
			array('theme, , details_number', 'length', 'max'=>255),
			array('manager, user, method', 'length', 'max'=>100),
                        array('user', 'email'),
			array('receive_date, pay_date, details_number, payment_type', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, order_id, receive_date, pay_date, theme, manager, user, summ, details_type, details_number, payment_type, approve, method', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
			'zakaz' => array(self::BELONGS_TO, 'Zakaz', 'order_id'),
			'profileManager' => array(self::BELONGS_TO, 'User', array('manager'=>'email')),
			'profileUser' => array(self::BELONGS_TO, 'User', array('user'=>'email')),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'order_id' => ProjectModule::t('Order Num'),
			'receive_date' => ProjectModule::t('Create Date'),
			'pay_date' => ProjectModule::t('Approve Date'),
			'theme' => ProjectModule::t('Topic'),
			'manager' => ProjectModule::t('Manager'),
			'user' => ProjectModule::t('_User'),
			'summ' => ProjectModule::t('Summa'),
			'details_type' => ProjectModule::t('Payment Type'),
			'details_number' => ProjectModule::t('Payment Number'),
			'payment_type' => ProjectModule::t('Payment Type'),
			'approve' => ProjectModule::t('Approved'),
			'method' => ProjectModule::t('Method of payment'),
		);
	}

	public static function types() {
		return array(
			self::INCOMING_CUSTOMER   => ProjectModule::t('Incoming from customer'),
			self::OUTCOMING_EXECUTOR  => ProjectModule::t('Outcoming to executor'),
			self::OUTCOMING_WEBMASTER => ProjectModule::t('Outcoming to webmaster'),
			self::OUTCOMING_CUSTOMER  => ProjectModule::t('Outcoming refound'),
		);
	}
    public function performType() {
		$types = self::types();
		return $types[$this->payment_type];
    }
	
	public function approveFromBookkeeper($method, $type, $number)
	{
		if($this->approve != 1){
			$tran = Yii::app()->db->beginTransaction();
			try {
				
				$this->method = $method;
				$this->approve = 1;
				$this->pay_date = date("Y-m-d H:i:s");
				$this->details_type = $type;
				$this->details_number = $number;
				$this->save(false);
				if($this->payment_type == self::OUTCOMING_EXECUTOR) {
					$payment = ProjectPayments::model()->findByAttributes(['order_id' => $this->order_id]);
					$payment->payed += $this->summ;
					$payment->to_pay -= $this->summ;
					$payment->save(false);
				}
				$tran->commit();
				
			} catch (Exception $ex) {
				$tran->rollback();
				return false;
			}
			return true;
		} else return false;
	}
	
	public function rejectFromBookkeeper($method)
	{   
		if($this->approve != self::REJECTED) {
		
			$this->method = $method;
			$this->approve = self::REJECTED;
			$this->pay_date = NULL;
			$this->save(false);
			
			return true;
		} else return false;
	}
	
	public function cancelPayment($method)
	{
		$this->method = $method;
		$this->approve = self::FREE;
		$this->pay_date = NULL;
		$this->save(false);
		return true;
	}
	
	public function getTotalData() { // Is it need ?
		return Yii::app()->db->createCommand("SELECT payment_type, SUM(summ) AS s, COUNT(*) AS ctn FROM `" . self::tableName() . "` GROUP BY payment_type ORDER BY payment_type")->queryAll();
	}
	
	public function pageTotal($provider) {
		$total=0;
		foreach($provider->data as $item)
			$total+=$item->summ;
		return $total;
	}
        
	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search($type)
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;
		
		$criteria->condition = 'payment_type IN (:type)';
		$criteria->params = array(':type'=>$type);

		$criteria->compare('id',$this->id);
		$criteria->compare('order_id',$this->order_id);
		$criteria->compare('receive_date',$this->receive_date,true);
		$criteria->compare('pay_date',$this->pay_date,true);
		$criteria->compare('theme',$this->theme,true);
		$criteria->compare('manager',$this->manager,true);
		$criteria->compare('user',$this->user,true);
		$criteria->compare('summ',$this->summ);
		$criteria->compare('details_type',$this->details_type,true);
		$criteria->compare('details_number',$this->details_number,true);
		$criteria->compare('payment_type',$this->payment_type);
		$criteria->compare('approve',$this->approve);
		$criteria->compare('method',$this->method,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
			'pagination'=>false,
		));
	}
	
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Payment the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
