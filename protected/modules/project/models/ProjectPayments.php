<?php

class ProjectPayments extends CActiveRecord {

	public function tableName() {
		return Company::getId().'_ProjectPayments';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, order_id', 'numerical', 'integerOnly'=>true),
			array('project_price, work_price, received, approved_in, approved_out, to_receive, to_pay, payed', 'numerical'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, order_id, project_price, work_price, received, approved_in, approved_out, to_receive, to_pay, payed', 'safe', 'on'=>'search'),
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
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'order_id' => 'Номер заказа',
			'project_price' => 'Стоимость',
			'work_price' => 'Цена',
			'received' => 'Получено',
			'approved_in' => 'Подтверждено входящий',
            'approved_out' => 'Подтверждено исходящий',
			'to_receive' => 'На получение',
			'to_pay' => 'На оплату',
			'payed' => 'Оплачено',
		);
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
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('order_id',$this->order_id);
		$criteria->compare('project_price',$this->project_price);
		$criteria->compare('work_price',$this->work_price);
		$criteria->compare('received',$this->received);
		$criteria->compare('approved_in',$this->approved_in);
                $criteria->compare('approved_out',$this->approved_out);
		$criteria->compare('to_receive',$this->to_receive);
		$criteria->compare('to_pay',$this->to_pay);
		$criteria->compare('payed',$this->payed);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
        
        public function prepair($orderId) {
            $model = self::model()->find('order_id = :ORDER_ID', array(
                ':ORDER_ID' => $orderId
            ));
            $this->id = $model->id;
            $this->order_id = $model->order_id;
            $this->project_price = $model->project_price;
            $this->work_price = $model->work_price;
            $this->received = $model->received;
            $this->approved_in = $model->approved_in;
            $this->approved_out = $model->approved_out;
            $this->to_receive = $model->to_receive;
            $this->to_pay = $model->to_pay;
            $this->payed = $model->payed;
        }
        
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ProjectPayments the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
