<?php

class ProjectMessages extends CActiveRecord
{
	public $roles = [
		1 => 'Admin',
        2 => 'Author',
        3 => 'Corrector',
        4 => 'Customer',
        5 => 'Manager',
        6 => 'Executor',
	];

	//public static $table_prefix;
	
	public function tableName() {
		return Company::getId().'_ProjectMessages';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('message, sender, moderated, date, order', 'required'),
			array('sender, recipient, moderated, order, sender_role, recipient_role', 'numerical', 'integerOnly'=>true),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('cost', 'safe'),
			array('id, message, sender, recipient, moderated, date, order, cost, sender_role, recipient_role', 'safe', 'on'=>'search'),
		);
	}

	public function getRole($id)
    {
        return $this->roles[$id];
    }

    public function getRoleId($name)
    {
        foreach ($this->roles as $key => $value)
        	if ($value == $name) return $key;
    }

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		return array(
            'senderObject'=>array(self::HAS_ONE, 'User', array('id'=>'sender')),
            'recipientObject'=>array(self::HAS_ONE, 'User', array('id'=>'recipient')),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'message' => 'Сообщение',
			'sender' => 'Sender',
			'recipient' => 'Recipient',
			'moderated' => 'Moderated',
			'date' => 'Date',
			'order' => 'Order',
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
		$criteria->compare('message',$this->message,true);
		$criteria->compare('sender',$this->sender);
		$criteria->compare('recipient',$this->recipient);
		$criteria->compare('moderated',$this->moderated);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('order',$this->order);
		$criteria->compare('cost',$this->order);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ProjectMessages the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
