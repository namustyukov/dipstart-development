<?php

/**
 * This is the model class for table "ProjectsParts".
 *
 * The followings are the available columns in table 'ProjectsParts':
 * @property string $id
 * @property string $proj_id
 * @property string $title
 * @property string $text
 * @property string $file
 * @property string $date
 * @property string $max_exec_date
 * @property string $date_finish
 * @property integer $pages
 * @property string $budget
 * @property string $add_demands
 */
class ZakazParts extends CActiveRecord
{
    public $dateTimeIncomeFormat = 'yyyy-MM-dd HH:mm:ss';
    public $dateTimeOutcomeFormat = 'dd.MM.yyyy HH:mm';
	
	public function tableName() {
		return Company::getId().'_ProjectsParts';
	}

    public function getDbdate()
    {
        return Yii::app()->dateFormatter->format($this->dateTimeOutcomeFormat, CDateTimeParser::parse($this->date, $this->dateTimeIncomeFormat));
    }

    public function setDbdate($datetime)
    {
        $this->date = Yii::app()->dateFormatter->format($this->dateTimeIncomeFormat, CDateTimeParser::parse($datetime, $this->dateTimeOutcomeFormat));
    }

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('proj_id, title', 'required'),
			array('proj_id', 'length', 'max'=>11),
			array('title, file', 'length', 'max'=>255),
			array('payment', 'length', 'max'=>100),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, proj_id, title, comment, file, dbdate, author_id, payment, show', 'safe', 'on'=>'search'),
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
			'status' => array(self::HAS_ONE, 'PartStatus', array('id'=>'status_id')),
            'files' => array(self::HAS_MANY, 'ZakazPartsFiles', array('part_id'=>'id')),
            'author' => array(self::HAS_ONE, 'User', array('id'=>'author_id'))
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels($user=true)
	{
        $res['title'] = 'Название';
        $res['file'] = 'Файл';
        $res['date'] = 'Дата';
        $res['payment'] = 'Оплачено';
        $res['comment'] = 'Комментарий';
        $res['author_id'] = 'Автор ID';
        $res['author'] = 'Автор';
        if ($user) {
            $res['id'] = 'ID';
            $res['proj_id'] = 'ID Проекта';
            $res['show'] = 'Отображение';
        }
        return $res;
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

		$criteria->compare('id',$this->id,true);
		$criteria->compare('proj_id',$this->proj_id,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('comment',$this->comment,true);
		$criteria->compare('file',$this->file,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('author_id',$this->author_id,true);
		$criteria->compare('show',$this->show);
                
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return ZakazParts the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function getDateLastUncompleted($id){
		$part = self::model()->findAllByAttributes(array('proj_id' => $id), array('order' => 'date asc', 'limit' => '1'));
		return $part[0]->date;
	}

	public function getForFilter(){
		return CHtml::listData(
			self::model()->with('status')->findAll(array(
				'select' => array('id', 'status_id'), 'condition' => 'status_id != :id', 'params' => array(':id' => PartStatus::COMPLETED)
			)), 'status_id', 'status.status'
		);
	}
}
