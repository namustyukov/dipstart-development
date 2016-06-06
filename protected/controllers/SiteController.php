<?php

class SiteController extends Controller {
	/**
	 * Declares class-based actions.
	 */
	public function actions() {
		return array(
			// captcha action renders the CAPTCHA image displayed on the contact page
			/*'captcha'=>array(
				'class'=>'CCaptchaAction',
				'backColor'=>0xFFFFFF,
			),*/
			// page action renders "static" pages stored under 'protected/views/site/pages'
			// They can be accessed via: index.php?r=site/page&view=FileName
			'page'=>array(
				'class'=>'CViewAction',
			),
            'yiichat'=>array('class'=>'YiiChatAction'),
			/*'yiifilemanagerfilepicker'=>array(
				'class'=>
					'ext.yiifilemanagerfilepicker.YiiFileManagerFilePickerAction'
			),*/
		);
	}

	/**
	 * This is the default 'index' action that is invoked
	 * when an action is not explicitly requested by users.
	 */
	public function actionIndex()
	{
        if (Yii::app()->user->isGuest) {
//			Yii::app()->theme='client';
			if ($this->getViewFile('index')) {
				$this->render('index', array(
					'role' => 'stranger'
				));
			} elseif (Company::getFrontPage()) {
				$this->redirect(Company::getFrontPage());
			} else {
				$this->redirect('/user/login');
			}
		} elseif (User::model()->isAuthor()){
			$this->redirect('/project/zakaz/ownList');
		} elseif (User::model()->isCustomer()){
			$this->redirect('/project/zakaz/customerOrderList');
		} elseif (User::model()->getUserRole()=='Webmaster'){
			$this->redirect('/partner/stats');
        } elseif (User::model()->getUserRole()=='root') {
			$this->redirect('/company/list');
		} else {
			$this->redirect('/project/event');
        }
	}

	/**
	 * This is the action to handle external exceptions.
	 */
	public function actionError()
	{
		if($error=Yii::app()->errorHandler->error)
		{
			if(Yii::app()->request->isAjaxRequest)
				echo $error['message'];
			else
				$this->render('error', $error);
		}
	}

	public function actionAgreement()
	{
		$agreement = Company::getAgreement();
		$this->render('agreement',array('agreement'=>$agreement));
	}
	
	/**
	 * Displays the contact page
	 */
	public function actionContact()
	{
		$model=new ContactForm;
		if(isset($_POST['ContactForm']))
		{
			$model->attributes=$_POST['ContactForm'];
			if($model->validate())
			{
				$name='=?UTF-8?B?'.base64_encode($model->name).'?=';
				$subject='=?UTF-8?B?'.base64_encode($model->subject).'?=';
				$headers="From: $name <{$model->email}>\r\n".
					"Reply-To: {$model->email}\r\n".
					"MIME-Version: 1.0\r\n".
					"Content-Type: text/plain; charset=UTF-8";

				mail(Yii::app()->params['supportEmail'],$subject,$model->body,$headers);
				Yii::app()->user->setFlash('contact',UserModule::t('Thank you for contacting us. We will respond to you as soon as possible.'));
				$this->refresh();
			}
		}
		$this->render('contact',array('model'=>$model));
	}

	/**
	 * Logs out the current user and redirect to homepage.
	 */
	public function actionLogout()
	{
		Yii::app()->user->logout();
		$this->redirect(Yii::app()->homeUrl);
	}
	
	public function actionPage($view) {
		if ($view == 'order') {
			$this->processOrderPage();
		}
		Yii::app()->theme = explode('.',$_SERVER['SERVER_NAME'])[0];
		$this->render('page/'.$view, array(
			'role' => 'stranger',
		));
		//Yii::app()->end();
	}
	
	public function processOrderPage(){
		$message = false;
		$login_ok = false;
		if (!(Yii::app()->user->isGuest) && User::model()->isCustomer()) $login_ok = true;
		if (!$login_ok && isset($_POST['Login'])) {
			if (Yii::app()->user->isGuest) {
				$model=new UserLogin;
				//$this->performAjaxValidation($model);
				// collect user input data
				$model->attributes=$_POST['Login'];
				// validate user input and redirect to previous page if valid
				if($model->validate()) {
					//$this->lastViset();
					$login_ok = true;
				} else {
					$message = 'Incorrect login or password';
					//Yii::app()->end();
				}
			}
		}
		if (!$login_ok && isset($_POST['User'])) {
			$model = new User();
			$attributes = $_POST['User'];
			//$attributes['full_name'] =  $_POST['User']['first_name'].' '.$_POST['User']['last_name'];
			$pos = strpos( $attributes['email'], '@');
			$attributes['username'] = str_replace(array('@','.'), '_', $attributes['email']);// substr( $attributes['email'], 0, $pos);
			$attributes['full_name'] =  $_POST['User']['first_name'].' '.$_POST['User']['last_name'];
			unset($attributes['first_name']);
			unset($attributes['last_name']);
			$p = $_POST['Profile'];
			$country = $p['country'];
			$countryCodes = $this->getCountryCodes();
			$code = $countryCodes[$country];
			$attributes['phone_number'] = '+'.$code.$attributes['phone_number'];
			Yii::import('user.controllers.RegistrationController');
			if (RegistrationController::register($model, $attributes)){
				$login_ok = true;
				$profile = new Profile;
				$profile->user_id = $model->id;
				$profile->country = $country;
				$profile->save();
			} else {
				if($attributes['email']!='') {
					$message = 'Sorry, registration faild...<br>';
					foreach($model->errors as $err => $descr) {
						$message .= $descr[0].'<br>';
					}
				}
			}
		}

		$model = new Zakaz();
		$model->attributes	= $_POST['Zakaz']; // (unixtime)
		
		if ($login_ok) {
			Yii::import('project.controllers.ZakazController');
			if (ZakazController::createProject($model,$_POST['Project'])) {
				$cost = $this->calculateCost($model);
				$payment = new ProjectPayments;
				$payment->order_id = $model->id;
				$payment->project_price = $cost;
				$payment->received = 0;
				$payment->to_receive = $cost;
				$payment->work_price = 0;
				$payment->payed = 0;
				$payment->to_pay = 0;
				$payment->save();
				if(Campaign::getPayment2Chekout()!=0) {
					$user = User::model()->with('profile')->findByPk($model->user_id);
					$data = array(
						'sid' => Campaign::getPayment2Chekout(),
						'mode' => '2CO',
						'li_0_type' => 'product',
						'li_0_name' => 'order'.$model->id,
						'li_0_price' => $cost,
						'li_0_product_id' => $model->id,
						'x_receipt_link_url' => 'http://'.$_SERVER["HTTP_HOST"].'/project/payment/affiliatePayment',
						
						'card_holder_name' => $user->full_name,
						'country' => $user->profile->country,
						'email' => $user->email,
						'phone' => $user->phone_number,
						/*'card_holder_name' => 'Checkout Shopper',
						'street_address' => '123 Test Address',
						'street_address2' => 'Suite 200',
						'city' => 'Columbus',
						'state' => 'OH',
						'zip' => '43228',
						'country' => 'USA',
						'email' => 'example@2co.com',
						'phone' => '614-921-2450',*/
					);
					$this->redirectWithPost('https://2checkout.com/checkout/purchase', $data);
				}
				echo 'Ok! Cost = '.$cost;
				Yii::app()->end();
			} else {
				//echo 'Project is not valid!!<br>';
				//print_r($_POST['Project']);
				//Yii::app()->end();
				$message = 'Please complete all required fields.';
			}
		}

		if (!isset($model->unixtime) or $model->unixtime=='' ) {
			$model->unixtime = time();
		}
		Yii::app()->theme = explode('.',$_SERVER['SERVER_NAME'])[0];
		$this->render('page/order', array(
			'logged' => $login_ok,
			'message' => $message,
			'project' => $model,
			'countryCodes' => $this->getCountryCodes()
		));
		Yii::app()->end();
	}
	
	public function calculateCost($project){
		$result = false;
		$campaign = Campaign::getId();
		if($campaign == 2) { // Perfect paper
			$High_school = array(
				'128' => 7, // twoMonths
				'127' => 7, // thirtyDays
				'126' => 8, // twentyDays
				'125' => 9, // tenDays
				'124' => 10, // sevenDays
				'123' => 11, // fiveDays
				'122' => 12, // fourDays
				'121' => 13, // threeDays
				'120' => 14, // twoDays
				'119' => 17, // oneDay
				'118' => 20, // twelveHours
				//'' => 23, // eightHours
				'117' => 26, // sixHours
				'116' => 30, // threeHours
				'slide' => 3,
			);
			$Undergraduate = array(
				'128' => 8, // twoMonths
				'127' => 8, // thirtyDays
				'126' => 9, // twentyDays
				'125' => 11, // tenDays
				'124' => 12, // sevenDays
				'123' => 13, // fiveDays
				'122' => 14, // fourDays
				'121' => 15, // threeDays
				'120' => 17, // twoDays
				'119' => 20, // oneDay
				'118' => 23, // twelveHours
				//'' => 26, // eightHours
				'117' => 28, // sixHours
				'116' => 37, // threeHours
				'slide' => 4,
			);
			$Bachelor = array(
				'128' => 13, // twoMonths
				'127' => 13, // thirtyDays
				'126' => 14, // twentyDays
				'125' => 15, // tenDays
				'124' => 16, // sevenDays
				'123' => 17, // fiveDays
				'122' => 18, // fourDays
				'121' => 19, // threeDays
				'120' => 20, // twoDays
				'119' => 22, // oneDay
				'118' => 24, // twelveHours
				//'' => 27, // eightHours
				'117' => 29, // sixHours
				'116' => 40, // threeHours
				'slide' => 5,
			);
			$Professional = array(
				'128' => 15, // twoMonths
				'127' => 15, // thirtyDays
				'126' => 16, // twentyDays
				'125' => 17, // tenDays
				'124' => 19, // sevenDays
				'123' => 20, // fiveDays
				'122' => 22, // fourDays
				'121' => 23, // threeDays
				'120' => 25, // twoDays
				'119' => 29, // oneDay
				'118' => 33, // twelveHours
				//'' => 36, // eightHours
				'117' => 39, // sixHours
				'116' => 47, // threeHours
				'slide' => 7,
			);
			$c = array(
				'112' => $High_school,
				'113' => $Undergraduate,
				'114' => $Bachelor,
				'115' => $Professional,
			);
			$b = $c[$project->AcademicLevel];
			$s = $b['slide']*$project->Numberofslides;
			$a = $b[$project->FirstDraftDeadline];
			$spaced = array(
				'135' => 1.7,
				'136' => 1,
			);
			$d = $spaced[$project->spaced];
			$tos = array(
				'110' => 1,
				'111' => 2,
			);
			$e = $tos[$project->Typeofservice];
			$result = ($a * $project->Numberofpages * $d / $e) + $s;
		}
		return $result;
	}
	public function redirectWithPost($action, $data = array()) {
		?><html xmlns="http://www.w3.org/1999/xhtml">
			<head>
				<script type="text/javascript">
					function closethisasap (){
						document.forms["redirectpost"].submit();
					}
				</script>
				<body onload="closethisasap();">
					<form name="redirectpost" method="post" action="<?php echo $action; ?>" >
						<?php
						if (!is_null($data)) {
							foreach ($data as $k => $v) {
								?><input type="hidden" name="<?php echo $k; ?>" value="<?php echo $v; ?>" /><?php
							}
						}
						?>
					</form>
				</body>
			</head>
		</html>
		<?php Yii::app()->end();
	}
	
	public function getCountryCodes(){
		return array(
			'Kenya'											=> 254,
			'United Kingdom'								=> 44,
			'United States'									=> 1,
			'Canada'										=> 1,
			'United Arab Emirates'							=> 971,
			'Australia'										=> 61,
			'India'											=> 91,
			'Philippines'									=> 63,
			'Pakistan'										=> 92,
			'Ukraine'										=> 380,
			'Russia'										=> 7,
			'Kazakhstan'									=> 7,
			'Belarus'										=> 375,
			'Austria'										=> 43,
			'Azerbaijan'									=> 994,
			'Albania'										=> 355,
			'Algeria'										=> 213,
			'American Samoa'								=> 685,
			'Anguilla'										=> 1264,
			'Angola'										=> 244,
			'Andorra'										=> 376,
			'Antarctica'									=> 1,
			'Antigua and Barbuda'							=> 1268,
			'Argentina'										=> 54,
			'Armenia'										=> 374,
			'Aruba'											=> 297,
			'Afghanistan'									=> 93,
			'The Bahamas'									=> 1242,
			'Bangladesh'									=> 880,
			'Barbados'										=> 1246,
			'Bahrain'										=> 973,
			'Belize'										=> 501,
			'Belgium'										=> 32,
			'Benin'											=> 229,
			'Bermuda'										=> 1441,
			'Bulgaria'										=> 359,
			'Bolivia'										=> 591,
			'Bosnia and Herzegovina'						=> 387,
			'Botswana'										=> 267,
			'Brazil'										=> 55,
			'British Indian Ocean Territory'				=> 1,
			'British Virgin Islands'						=> 1284,
			'Brunei'										=> 673,
			'Burkina Faso'									=> 226,
			'Burundi'										=> 257,
			'Bhutan'										=> 975,
			'Vanuatu'										=> 678,
			'Holy See (Vatican City)'						=> 39,
			'Hungary'										=> 36,
			'Venezuela'										=> 58,
			'Virgin Islands'								=> 1340,
			'Vietnam'										=> 84,
			'Gabon'											=> 241,
			'Haiti'											=> 509,
			'Guyana'										=> 592,
			'The Gambia'									=> 220,
			'Ghana'											=> 233,
			'Guadeloupe'									=> 590,
			'Guatemala'										=> 502,
			'Guinea'										=> 224,
			'Guinea-Bissau'									=> 245,
			'Germany'										=> 49,
			'Guernsey'										=> 1,
			'Gibraltar'										=> 350,
			'Honduras'										=> 504,
			'Hong Kong'										=> 852,
			'Grenada'										=> 1473,
			'Greenland'										=> 299,
			'Greece'										=> 30,
			'Georgia'										=> 995,
			'Guam'											=> 1671,
			'Denmark'										=> 45,
			'Democratic Republic of the Congo'				=> 243,
			'Jersey'										=> 1,
			'Djibouti'										=> 253,
			'Dominica'										=> 1767,
			'Dominican Republic'							=> 1829,
			'Egypt'											=> 20,
			'Zambia'										=> 260,
			'Western Sahara'								=> 1,
			'Zimbabwe'										=> 263,
			'Israel'										=> 972,
			'Indonesia'										=> 62,
			'Jordan'										=> 962,
			'Iraq'											=> 964,
			'Iran'											=> 98,
			'Ireland'										=> 353,
			'Iceland'										=> 354,
			'Spain'											=> 34,
			'Italy'											=> 39,
			'Yemen'											=> 967,
			'Cape Verde'									=> 238,
			'Cayman Islands'								=> 1345,
			'Cambodia'										=> 855,
			'Cameroon'										=> 237,
			'Qatar'											=> 974,
			'Cyprus'										=> 357,
			'Kiribati'										=> 686,
			'China'											=> 86,
			'Cocos (Keeling) Islands'						=> 1,
			'Colombia'										=> 57,
			'Comoros'										=> 269,
			'Republic of the Congo'							=> 242,
			'Kosovo'										=> 381,
			'Costa Rica'									=> 506,
			'Cote d’Ivoire'									=> 225,
			'Cuba'											=> 53,
			'Kuwait'										=> 965,
			'Laos'											=> 856,
			'Latvia'										=> 371,
			'Lesotho'										=> 266,
			'Liberia'										=> 231,
			'Lebanon'										=> 961,
			'Libya'											=> 218,
			'Lithuania'										=> 370,
			'Liechtenstein'									=> 423,
			'Luxembourg'									=> 352,
			'Mauritius'										=> 230,
			'Mauritania'									=> 222,
			'Madagascar'									=> 261,
			'Mayotte'										=> 1,
			'Macau'											=> 853,
			'Malawi'										=> 265,
			'Malaysia'										=> 60,
			'Mali'											=> 223,
			'United States Pacific Island Wildlife Refuges'	=> 1,
			'Maldives'										=> 960,
			'Malta'											=> 356,
			'Morocco'										=> 212,
			'Martinique'									=> 596,
			'Marshall Islands'								=> 692,
			'Mexico'										=> 52,
			'Micronesia, Federated States of'				=> 691,
			'Mozambique'									=> 258,
			'Moldavia'										=> 37,
			'Monaco'										=> 377,
			'Mongolia'										=> 976,
			'Montserrat'									=> 1664,
			'Burma'											=> 95,
			'Namibia'										=> 264,
			'Nauru'											=> 674,
			'Nepal'											=> 977,
			'Niger'											=> 227,
			'Nigeria'										=> 234,
			'Netherlands Antilles'							=> 1,
			'Nicaragua'										=> 505,
			'Niue'											=> 683,
			'New Zealand'									=> 64,
			'New Caledonia'									=> 687,
			'Norway'										=> 47,
			'Oman'											=> 968,
			'Bouvet Island'									=> 1,
			'Clipperton Island'								=> 1,
			'Isle of Man'									=> 1,
			'Norfolk Island'								=> 672,
			'Christmas Island'								=> 1,
			'Saint Martin'									=> 1,
			'Heard Island and McDonald Islands'				=> 1,
			'Cook Islands'									=> 682,
			'Turks and Caicos Islands'						=> 1649,
			'Palau'											=> 680,
			'Panama'										=> 507,
			'Papua New Guinea'								=> 675,
			'Paraguay'										=> 595,
			'Peru'											=> 51,
			'Pitcairn'										=> 1,
			'Poland'										=> 48,
			'Portugal'										=> 351,
			'Puerto Rico'									=> 1787,
			'Macedonia'										=> 389,
			'Reunion'										=> 262,
			'Rwanda'										=> 250,
			'Romania'										=> 40,
			'Samoa'											=> 685,
			'San Marino'									=> 378,
			'Sao Tome and Principe'							=> 239,
			'Saudi Arabia'									=> 966,
			'Swaziland'										=> 268,
			'Saint Helena'									=> 1,
			'Korea, North'									=> 850,
			'Northern Mariana Islands'						=> 1670,
			'Seychelles'									=> 248,
			'Saint Barthélemy'								=> 1,
			'Saint Pierre and Miquelon'						=> 508,
			'Senegal'										=> 221,
			'Saint Vincent and the Grenadines'				=> 1784,
			'Saint Kitts and Nevis'							=> 1869,
			'Saint Lucia'									=> 1758,
			'Serbia'										=> 381,
			'Singapore'										=> 65,
			'Syria'											=> 963,
			'Slovakia'										=> 421,
			'Solomon Islands'								=> 677,
			'Somalia'										=> 252,
			'Sudan'											=> 249,
			'Suriname'										=> 597,
			'Sierra Leone'									=> 232,
			'Tajikistan'									=> 992,
			'Thailand'										=> 66,
			'Taiwan'										=> 886,
			'Tanzania'										=> 255,
			'Timor-Leste'									=> 670,
			'Togo'											=> 228,
			'Tokelau'										=> 690,
			'Tonga'											=> 676,
			'Trinidad and Tobago'							=> 1868,
			'Tuvalu'										=> 688,
			'Tunisia'										=> 216,
			'Turkmenistan'									=> 993,
			'Turkey'										=> 90,
			'Uganda'										=> 256,
			'Uzbekistan'									=> 998,
			'Wallis and Futuna'								=> 681,
			'Uruguay'										=> 598,
			'Faroe Islands'									=> 298,
			'Fiji'											=> 679,
			'Falkland Islands (Islas Malvinas)'				=> 500,
			'France'										=> 33,
			'French Guiana'									=> 594,
			'French Polynesia'								=> 689,
			'French Southern Lands'							=> 1,
			'Croatia'										=> 385,
			'Central African Republic'						=> 236,
			'Chad'											=> 235,
			'Montenegro'									=> 382,
			'Czech Rep.'									=> 420,
			'Chile'											=> 56,
			'Switzerland'									=> 41,
			'Svalbard and Jan Mayen'						=> 1,
			'Sri Lanka'										=> 94,
			'Ecuador'										=> 593,
			'Equatorial Guinea'								=> 240,
			'Åland Islands'									=> 1,
			'El Salvador'									=> 503,
			'Eritrea'										=> 291,
			'Estonia'										=> 372,
			'Ethiopia'										=> 251,
			'South Africa'									=> 27,
			'South Georgia and the South Sandwich Islands'	=> 1,
			'Korea, South'									=> 82,
			'Jamaica'										=> 1876,
			'Japan'											=> 81,
		);
	}
}