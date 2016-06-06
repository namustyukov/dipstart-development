<?php

class AuthAssignment extends CActiveRecord
{
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
        return Company::getId().'_AuthAssignment';
		//return 'AuthAssignment';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
        return array(
            array('itemname, userid', 'required'),
            array('itemname, userid', 'length', 'max'=>64),
            array('bizrule, data', 'safe'),
            // The following rule is used by search().
            // @todo Please remove those attributes that should not be searched.
            array('itemname, userid, bizrule, data', 'safe', 'on'=>'search'),
        );
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
        return array(
            'AuthItem' => array(self::BELONGS_TO, 'AuthItem', 'itemname'),
        );
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
            'itemname' => UserModule::t("itemname"),
            'userid' => UserModule::t("id"),
        );
	}
    public function search()
    {
        $criteria=new CDbCriteria;
        $criteria->compare('itemname',$this->itemname,true);
        $criteria->compare('userid',$this->userid,true);
        $criteria->compare('bizrule',$this->bizrule,true);
        $criteria->compare('data',$this->data,true);

        return new CActiveDataProvider($this, array(
            'criteria'=>$criteria,
        ));
    }
}
