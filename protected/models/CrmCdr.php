<?php
/**
 * This is the model class for table "cdr".
 *
 * The followings are the available columns in table 'cdr':
 * @property string $id
 * @property integer $published
 * @property integer $answerDuration
 * @property string $source
 * @property string $destination
 * @property integer $duration
 * @property string $flow
 * @property string $result
 */
class CrmCdr extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return Company::getId().'_cdr';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
            ['id','unique'],
			array('id, published, source, destination, duration, flow, result', 'required'),
			array('published, answerDuration, duration', 'numerical', 'integerOnly'=>true),
			array('id', 'length', 'max'=>32),
			array('source, destination', 'length', 'max'=>255),
			array('flow', 'length', 'max'=>5),
			array('result', 'length', 'max'=>11),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, published, answerDuration, source, destination, duration, flow, result', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'published' => 'UnixTime',
            'publishedDate' => 'Дата начала',
            'publishedTime' => 'Время начала',
			'answerDuration' => 'Время ожидания ответа (сек)',
			'source' => 'Источник звонка',
			'destination' => 'Назначение звонка',
			'duration' => 'Длительность (сек)',
			'flow' => 'Направление',
			'result' => 'Результат',
		);
	}

    public function getPublishedDate() {
        return date('d M Y', $this->published);
    }
    
    public function getPublishedTime() {
        return date('H:i:s', $this->published);
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
		$criteria->compare('published',$this->published);
		$criteria->compare('answerDuration',$this->answerDuration);
		$criteria->compare('source',$this->source,true);
		$criteria->compare('destination',$this->destination,true);
		$criteria->compare('duration',$this->duration);
		$criteria->compare('flow',$this->flow,true);
		$criteria->compare('result',$this->result,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return CrmCdr the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
