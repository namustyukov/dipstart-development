<?php

/**
 * This is the model class for table "Templates".
 *
 * The followings are the available columns in table 'Templates':
 * @property string $id
 * @property string $name
 * @property string $title
 * @property string $text
 * @property string $type
 */
class Templates extends CActiveRecord
{
	const TYPE_AUTHOR = 2;
	const TYPE_CORRECTOR = 3;
	const TYPE_CUSTOMER = 1;
	const TYPE_AUTHOR_RESPONSE_PROJECT = 25;
	// 26 - мыло для техрука
	const TYPE_FOR_CUSTOMER_AGREEMENT_NOT_ACCEPTED = 27;
	const TYPE_FOR_MANAGER_AGREEMENT_NOT_ACCEPTED = 28;
	//31 - всплывающая подсказка при отправлении сообщения заказчиком

	/**
	 * @return string the associated database table name
	 */
	public function tableName() {
		return Company::getId().'_Templates';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('name, title, text, type_id', 'required'),
			array('name, title', 'length', 'max'=>255),
			array('type_id', 'length', 'max'=>18),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, name, title, text, type_id', 'safe', 'on'=>'search'),
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
			'id' => Yii::t('site','ID'),
			'name' => Yii::t('site','Question category'), //Yii::t('site','Name'),
			'title' => Yii::t('site','Question'), //Yii::t('site','Title'),
			'text' => Yii::t('site','Answer text'), //Yii::t('site','Text'),
			'type_id' => Yii::t('site','Type'),
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
		$criteria->compare('name',$this->name,true);
		$criteria->compare('title',$this->title,true);
		$criteria->compare('text',$this->text,true);
		$criteria->compare('type_id',$this->type_id,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}
	public function types()
	{
		return array(
			1 => Yii::t('site','Customer'),
			2 => Yii::t('site','Author'),
			3 => Yii::t('site','Service'),
			4 => Yii::t('site','Hint for manager'),
			5 => Yii::t('site','Tip'),
			10 => Yii::t('site','Service mail: Password recovery'), //Восстановление пароля
			11 => Yii::t('site','Service mail: Registration'), //Успешная регистрация
			12 => Yii::t('site','Service mail: Project accepted'), //Регистрация проекта
			13 => Yii::t('site','Service mail: payment awayting'), //Оплата заказа
			14 => Yii::t('site','Service mail: part done'), //По Вашему заказу появилась готовая часть
			15 => Yii::t('site','Service mail: work done'), //Работа готова
			16 => Yii::t('site','Service mail: new message for customer'), //Сообщение в чате
			17 => Yii::t('site','Service mail: order closed'), //Заказ завершен
			18 => Yii::t('site','Service mail: new project'), //Появился новый заказ по Вашей специальности
			19 => Yii::t('site','Service mail: executor assigment'), //О назначении Исполнителем
			20 => Yii::t('site','Service mail: new message for author'), //Сообщение в чате
			21 => Yii::t('site','Service mail: ouster'), //О съеме с заказа
			22 => Yii::t('site','Service mail: deadline arrived'), //Срок сдачи части наступил
			23 => Yii::t('site','Service mail: new revision'), //О новой доработке
			24 => Yii::t('site','Service mail: your salary'), //Об оплате заказа
			25 => Yii::t('site','Message for an author in response to project'), //Сообщение для автора при отклике на проект
			26 => Yii::t('site','Notification for correctors'), // Уведомление для технических руководителей
			27 => Yii::t('site','Message for a customer when the agreement was not accepted'), //Сообщение для клиента, когда он не согласен с условиями соглашения
			28 => Yii::t('site','Message for a manager that the agreement was not accepted by the customer'), //Сообщение для менеджера, что соглашение не было принято заказчиком
			29 => Yii::t('site','Button in chat for author'), // Шаблоны для кнопок в чате исполнителя
			30 => Yii::t('site','Button in chat for guest'), // Шаблон для кнопок в заказе гостя
			31 => Yii::t('site','Hint for customer when he sending a message'), // Шаблон для кнопок в заказе гостя
			32 => Yii::t('site','Notification for executor about the occurrence timing of the completion of the order'), // Шаблон уведовления о сроках завершения заказа
			33 => Yii::t('site','Notification for executor about the occurrence timing of the completion of the completion point'), // Шаблон уведовления о сроках завершения этапа заказа
		);
	}
	
    public function performType($type_id) {
		$types = $this->types();
		return $types[$type_id];
    }
	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Templates the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
	
	public function getTemplate($type_id){
		$template = $this->findByAttributes(array('type_id'=>$type_id));
		if($template) return  $template->text;
		else return null;
	}

	public function getTemplateList($type_id){
		$templates = $this->findAllByAttributes(array('type_id'=>$type_id));
		$custom_arr = array();
		foreach ($templates as $item)
			$custom_arr[$item->name] = $item->text;
		return $custom_arr;
	}
	
    protected function replaceBBCode($text_post) {
        $str_search = array(
            "#\\\n#is",
            "#\[b\](.+?)\[\/b\]#is",
            "#\[i\](.+?)\[\/i\]#is",
            "#\[u\](.+?)\[\/u\]#is",
            "#\[code\](.+?)\[\/code\]#is",
            "#\[quote\](.+?)\[\/quote\]#is",
            "#\[url=(.+?)\](.+?)\[\/url\]#is",
            "#\[url\](.+?)\[\/url\]#is",
            "#\[img\](.+?)\[\/img\]#is",
            "#\[size=(.+?)\](.+?)\[\/size\]#is",
            "#\[color=(.+?)\](.+?)\[\/color\]#is",
            "#\[list\](.+?)\[\/list\]#is",
            "#\[listn](.+?)\[\/listn\]#is",
            "#\[\*\](.+?)\[\/\*\]#"
        );
        $str_replace = array(
            "<br />",
            "<b>\\1</b>",
            "<i>\\1</i>",
            "<span style='text-decoration:underline'>\\1</span>",
            "<code class='code'>\\1</code>",
            "<table width = '95%'><tr><td>Цитата</td></tr><tr><td class='quote'>\\1</td></tr></table>",
            "<a href='\\1'>\\2</a>",
            "<a href='\\1'>\\1</a>",
            "<img src='\\1' alt = 'Изображение' />",
            "<span style='font-size:\\1%'>\\2</span>",
            "<span style='color:\\1'>\\2</span>",
            "<ul>\\1</ul>",
            "<ol>\\1</ol>",
            "<li>\\1</li>"
        );
        return preg_replace($str_search, $str_replace, $text_post);
    }

    public function insertVariables($in, $orderId){
        $in = $this->replaceBBCode($in);
        preg_match_all("/\{(.+?)\}/is",$in,$out);
        foreach ($out[1] as $k=>$o) {
            $var = explode('_', $o);
            $model_name = ucfirst(array_shift($var));
			if(@class_exists($model_name)){
				if($model_name == 'Projectpayments') $model = ProjectPayments::model()->find('order_id = :ORDER_ID', array( ':ORDER_ID'=>$orderId ));
				elseif($model_name == 'Company') $model = Company::getCompany();
				else $model = $model_name::model()->findByPk($orderId);
				$var = implode('_', $var);
				if($model && in_array($var,$model->attributeNames()))
					$fields[$k] = $model->$var;
				elseif($model) 
					$fields[$k] = '#wrong property#';
				else
					$fields[$k] = '#cant find model#';
			} else {
				$fields[$k] = '#wrong model#';
			}
        }
        return str_replace($out[0],$fields,$in);
	}
}
