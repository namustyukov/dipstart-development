<?php

class TipDone extends CActiveRecord {

	public function tableName() {
		return Campaign::getId().'_TipDone';
	}
    
    public function rules() {
        return array(
            array('message_id, status', 'required'),
            array('id, message_id, status', 'safe'),
        );
    }
    
    public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}
        
    public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'message_id' => 'Сообщение',
			'status' => 'Статус',
		);
	}
        
    public function search()
	{
		$criteria=new CDbCriteria;
		$criteria->compare('id',$this->id);
		$criteria->compare('message_id',$this->message_id);
		$criteria->compare('status',$this->status,true);
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
        
    public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
