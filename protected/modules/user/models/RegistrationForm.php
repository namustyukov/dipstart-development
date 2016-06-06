<?php
/**
 * RegistrationForm class.
 * RegistrationForm is the data structure for keeping
 * user registration form data. It is used by the 'registration' action of 'UserController'.
 */
class RegistrationForm extends User {
	/*public $verifyPassword;
	public $verifyCode;*/
	
	public function rules() {
		$rules = array(
			array('email', 'required'),
			//array('username', 'length', 'max'=>20, 'min' => 3,'message' => UserModule::t("Incorrect username (length between 3 and 20 characters).")),
			//array('password', 'length', 'max'=>128, 'min' => 4,'message' => UserModule::t("Incorrect password (minimal length 4 symbols).")),
			array('email', 'email'),
			//array('username', 'unique', 'message' => UserModule::t("This user's name already exists.")),
			array('email', 'unique', 'message' => UserModule::t("This user's email address already exists.")),
			//array('verifyPassword', 'compare', 'compareAttribute'=>'password', 'message' => UserModule::t("Retype Password is incorrect.")),
			array('phone_number', 'match', 'pattern' => '/^[-+()0-9 ]+$/u','message' => UserModule::t("Incorrect symbols (0-9,+,-,(,)).")),
			array('pid', 'numerical', 'integerOnly'=>true),
		);
		/*if (!(isset($_POST['ajax']) && $_POST['ajax']==='registration-form')) {
			array_push($rules,array('verifyCode', 'captcha', 'allowEmpty'=>!UserModule::doCaptcha('registration')));
		}
		array_push($rules,array('verifyPassword', 'compare', 'compareAttribute'=>'password', 'message' => UserModule::t("Retype Password is incorrect.")));
        */
		return $rules;
	}
	
	public function beforeValidate() {
		$this->email = trim($this->email);
		return parent::beforeValidate();
	}
	public function beforeSave() {
		$this->email = trim($this->email);
		return parent::beforeSave();
	}
}