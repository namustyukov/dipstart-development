<?php
class TelfinCommand extends CConsoleCommand {
    public function run($args) {
		$companies = Company::model()->findAll('frozen=:p AND telfin_id<>:t',array(':p'=>'0',':t'=>''));
		
		foreach($companies as $company) {
			Company::setActive($company);
			Yii::app()->language = Company::getLanguage();
			//User::model()->refreshMetaData();
			/*AuthAssignment::model()->refreshMetaData();
			ProfileField::model()->refreshMetaData();
			Profile::model()->refreshMetaData();
			Zakaz::model()->refreshMetaData();
			ZakazParts::model()->refreshMetaData();
			Events::model()->refreshMetaData();*/
			
			//self::executor();
			//self::manager();
			//echo '=)';
			
			Yii::app()->cdr->init('cron');
			
			CrmCdr::model()->refreshMetaData();
			// Проверим нет ли сохраненного токена в сессии
			// Работу с сессией вынес в прикладной код, потому что врапер может вызываться из командной строки
			//Yii::app()->cdr->cookie2token();
			// Обновим данные из Телфина
			Yii::app()->cdr->refresh();
			// Сохраним токен в сессии есть есть что сохранять
			//Yii::app()->cdr->token2cookie();
			
			
		}
    }
}