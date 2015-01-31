<?php

/**
 * This is the model class for table "Projects".
 *
 * The followings are the available columns in table 'Projects':
 * @property string $id
 * @property integer $order_id
 * @property string $user_id
 * @property integer $category_id
 * @property integer $job_id
 * @property string $title
 * @property string $text
 * @property string $date
 * @property string $max_exec_date
 * @property string $date_finish
 * @property integer $pages
 * @property string $add_demands
 * @property integer $status
 * @property string $executor
 * @property integer $event_creator_id
 * @property integer $timestamp
 */
class Moderation extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'ZakazModeration';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('order_id, category_id, job_id, pages, status', 'numerical', 'integerOnly'=>true),
			array('title', 'length', 'max'=>255),
			array('executor', 'length', 'max'=>10),
			array('order_id, max_exec_date, date_finish, author_informed, manager_informed, date, add_demands, notes, author_notes, user_notes, user_notes_show', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, user_id, category_id, job_id, title, text, date, max_exec_date, date_finish, author_informed, manager_informed, pages, add_demands, status, executor, event_creator_id, timestamp', 'safe', 'on'=>'search'),
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
                    'order_id' => 'Order ID',
                    'user_id' => ProjectModule::t('User'),
                    'category_id' => ProjectModule::t('Category'),
                    'job_id' => ProjectModule::t('Job'),
                    'title' => ProjectModule::t('Title'),
                    'text' => ProjectModule::t('Text'),
                    'date' => ProjectModule::t('Date'),
                    'max_exec_date' => ProjectModule::t('Max Date'),
                    'date_finish' => ProjectModule::t('Date Finish'),
                    'pages' => ProjectModule::t('Pages'),
                    'add_demands' => ProjectModule::t('Add Demands'),
                    'status' => ProjectModule::t('Status'),
                    'executor' => ProjectModule::t('Executor'),
                    'manager_informed' => ProjectModule::t('Manager Informed'),
                    'author_informed' => ProjectModule::t('Author Informed'),
                    'notes' => ProjectModule::t('Notes'),
                    'author_notes' => ProjectModule::t('author_notes'),
                    'event_creator_id' => 'Event Creator',
                    'timestamp' => 'timestamp'
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

		$criteria->compare('id',$this->id,true);
                $criteria->compare('order_id',$this->order_id,true);
		$criteria->compare('user_id',$this->user_id,true);
		$criteria->compare('category_id',$this->category_id);
		$criteria->compare('job_id',$this->job_id);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('file',$this->file,true);
		$criteria->compare('date',$this->date,true);
		$criteria->compare('max_exec_date',$this->max_exec_date,true);
		$criteria->compare('date_finish',$this->date_finish,true);
		$criteria->compare('pages',$this->pages);
		$criteria->compare('budget',$this->budget,true);
		$criteria->compare('add_demands',$this->add_demands,true);
		$criteria->compare('is_payed',$this->is_payed);
		$criteria->compare('with_prepayment',$this->with_prepayment);
		$criteria->compare('status',$this->status);
		$criteria->compare('executor',$this->executor,true);
                $criteria->compare('event_creator_id',$this->event_creator_id,true);
                $criteria->compare('timestamp',$this->timestamp,true);
                
		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

     protected function beforeSave()
     {
        if(parent::beforeSave())
        {
            if($this->isNewRecord)
            {
                $this->date=new CDbExpression('NOW()');
                $this->user_id=Yii::app()->user->id;
            }
//            else
//                $this->date = date('Y-m-d', strtotime($this->date));
//                $this->date_finish = date('Y-m-d', strtotime($this->date_finish));
//                $this->max_exec_date = date('Y-m-d', strtotime($this->max_exec_date));
//                $this->informed = date('Y-m-d', strtotime($this->informed));
        }
        return parent::beforeSave();
    }

    	public static function getExecutor($orderId) {
    		return self::model()->findByPk($orderId)->executor;
    	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Zakaz the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}