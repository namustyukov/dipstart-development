<?php

class ProjectField extends CActiveRecord
{
	const VISIBLE_NO=0;
	const VISIBLE_ALL=1;
	const VISIBLE_ONLY_MANAGER=2;
	const VISIBLE_AUTHOR_AND_MANAGER=3;
	const VISIBLE_CUSTOMER_AND_MANAGER=4;
	
	
	const REQUIRED_NO = 0;
	const REQUIRED_YES_SHOW_REG = 1;
	const REQUIRED_NO_SHOW_REG = 2;
	const REQUIRED_YES_REG_SPAM = 3;
    public $forAuthor;
	/**
	 * The followings are the available columns in table 'profiles_fields':
	 * @var integer $id
	 * @var string $varname
	 * @var string $title
	 * @var string $field_type
	 * @var integer $field_size
	 * @var integer $required
	 * @var string $error_message
	 * @var string $default
	 * @var integer $position
	 * @var integer $visible
	 * @var string $work_types
	 */
	
	public function tableName() {
		return Company::getId().'_ProjectFields';
	}
	 
	/**
	 * Returns the static model of the specified AR class.
	 * @return CActiveRecord the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules() {
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('varname, title, field_type', 'required'),
			array('varname', 'match', 'pattern' => '/^[A-Za-z_0-9]+$/u','message' => UserModule::t("Variable name may consist of A-z, 0-9, underscores, begin with a letter.")),
			array('varname', 'unique', 'message' => UserModule::t("This field already exists.")),
			array('varname, field_type', 'length', 'max'=>50),
			array('required, position, visible', 'numerical', 'integerOnly'=>true),
			array('field_size', 'match', 'pattern' => '/^\s*[-+]?[0-9]*\,*\.?[0-9]+([eE][-+]?[0-9]+)?\s*$/'),
			array('title, error_message, default, work_types', 'length', 'max'=>255),
			array('id, varname, title, field_type, field_size, required, error_message, default, position, visible, work_types', 'safe', 'on'=>'search'),
			
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
			'id' => UserModule::t('Id'),
			'varname' => UserModule::t('Variable name'),
			'title' => UserModule::t('Title'),
			'field_type' => UserModule::t('Field Type'),
			'field_size' => UserModule::t('Field Size'),
			'required' => UserModule::t('Required'),
			'error_message' => UserModule::t('Error Message'),
			'default' => UserModule::t('Default'),
			'position' => UserModule::t('Position'),
			'visible' => UserModule::t('Visible'),
			//'editable' => UserModule::t('Editable'),
			'work_types' => ProjectModule::t('Work types'),
		);
	}
	
	public function scopes()
    {
        return array(
            'forAll'=>array(
                'condition'=>'visible='.self::VISIBLE_ALL,
                'order'=>'position',
            ),
            'forManager'=>array(
                'condition'=>'visible='.self::VISIBLE_ALL.' OR visible='.self::VISIBLE_ONLY_MANAGER.' OR visible='.self::VISIBLE_AUTHOR_AND_MANAGER.' OR visible='.self::VISIBLE_CUSTOMER_AND_MANAGER,
                'order'=>'position',
            ),
            'forCustomer'=>array(
                'condition'=>'visible='.self::VISIBLE_CUSTOMER_AND_MANAGER.' OR visible='.self::VISIBLE_ALL,
                'order'=>'position',
            ),
            'forAuthor'=>array(
                'condition'=>'visible='.self::VISIBLE_AUTHOR_AND_MANAGER.' OR visible='.self::VISIBLE_ALL,
                'order'=>'position',
            ),

            'sort'=>array(
                'order'=>'position',
            ),
        );
    }
    
    /**
     * @param $value
     * @return formated value (string)
     */
    
	public static function itemAlias($type,$code=NULL) {
		$_items = array(
			'field_type' => array(
				'INTEGER' => UserModule::t('INTEGER'),
				'VARCHAR' => UserModule::t('VARCHAR'),
				'TEXT'=> UserModule::t('TEXT'),
				'TIMESTAMP'=> UserModule::t('DATE'),
				'LIST'=> UserModule::t('LIST'),
				'FLOAT'=> UserModule::t('FLOAT'),
				'DECIMAL'=> UserModule::t('DECIMAL'),
				'BOOL'=> UserModule::t('BOOL'),
				'BLOB'=> UserModule::t('BLOB'),
				'BINARY'=> UserModule::t('BINARY'),
			),
			'required' => array(
				self::REQUIRED_NO => UserModule::t('No'),
				self::REQUIRED_NO_SHOW_REG => UserModule::t('No, but show on registration form'),
				self::REQUIRED_YES_SHOW_REG => UserModule::t('Yes and show on registration form'),
				self::REQUIRED_YES_REG_SPAM => UserModule::t('Yes, show on registration and spam'),
			),
			'visible' => array(
				self::VISIBLE_ALL => UserModule::t('For all'),
				self::VISIBLE_NO => UserModule::t('Hidden'),
				self::VISIBLE_ONLY_MANAGER => UserModule::t('Manager only'),
				self::VISIBLE_AUTHOR_AND_MANAGER => UserModule::t('Author and manager'),
				self::VISIBLE_CUSTOMER_AND_MANAGER => UserModule::t('Customer and manager'),
			),
		);
		if (isset($code))
			return isset($_items[$type][$code]) ? $_items[$type][$code] : false;
		else
			return isset($_items[$type]) ? $_items[$type] : false;
	}
	
	/**
     * Retrieves a list of models based on the current search/filter conditions.
     * @return CActiveDataProvider the data provider that can return the models based on the search/filter conditions.
     */
    public function search()
    {
        // Warning: Please modify the following code to remove attributes that
        // should not be searched.

        $criteria=new CDbCriteria;

        $criteria->compare('id',$this->id);
        $criteria->compare('varname',$this->varname,true);
        $criteria->compare('title',$this->title,true);
        $criteria->compare('field_type',$this->field_type,true);
        $criteria->compare('field_size',$this->field_size);
        $criteria->compare('required',$this->required);
        $criteria->compare('error_message',$this->error_message,true);
        $criteria->compare('default',$this->default,true);
        $criteria->compare('position',$this->position);
        $criteria->compare('visible',$this->visible);

        return new CActiveDataProvider(get_class($this), array(
            'criteria'=>$criteria,
			'pagination'=>array(
				'pageSize'=>Yii::app()->controller->module->fields_page_size,
			),
			'sort'=>array(
				'defaultOrder'=>'position',
			),
        ));
    }
	
	public function inTableByVarname($varname){
		if (self::model()->findByAttributes(array('varname' => $varname)) != null) {
			return true;
		}
		else {
			return false;
		}
	}
}