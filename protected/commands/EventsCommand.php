<?php
class EventsCommand extends CConsoleCommand {
	
	const INTERVAL = 5; // »нтервал запуска скрипта в минутах
	
    public function run($args) {
		$companies = Company::model()->findAll('frozen=:p',array(':p'=>'0'));
		
		foreach($companies as $company) {
			Company::setActive($company);
			Yii::app()->language = Company::getLanguage();
			User::model()->refreshMetaData();
			AuthAssignment::model()->refreshMetaData();
			ProfileField::model()->refreshMetaData();
			Profile::model()->refreshMetaData();
			Zakaz::model()->refreshMetaData();
			ZakazParts::model()->refreshMetaData();
			Events::model()->refreshMetaData();
			Templates::model()->refreshMetaData();
			self::executor();
			self::manager();
		}
    }
			
	// —обытие у исполнител€ - отправл€ет шаблон на емаил уведомление об этом (берем из справочника)
	public function executor() {
		$usersModel = User::model()->findAllNotificationExecutors();
		if (is_array($usersModel))
			foreach ($usersModel as $user) {
				foreach ($user->zakaz_executor as $zakaz) {
					if(isset($user->profile)) $time = explode(':', $user->profile->notification_time); // врем€ X, за которое надо уведомл€ть (количество часов и минут), формат "5;48"
					else $time[0] = 0;
					if (count($time)<2) $time[1] = 0;
					$date = date('Y-m-d H:i',strtotime($zakaz->author_informed));
					$date = strtotime($date)-(int)$time[0]*60*60-(int)$time[1]*60;
					$dateStart = strtotime(date('Y-m-d H:i',$date)) - (self::INTERVAL * 60);
					if (time() > $dateStart && time() <= $date ) {
						echo 'Email zakaz #'.$zakaz->id."\n";
						$templatesModel = Templates::model()->findByAttributes(array('type_id'=>'32'));
						if($templatesModel) {
							$email = new Emails;
							$email->from_id	= 1;
							$email->to_id 	= $user->id;
							$email->name 	= $user->full_name;
							$email->sendTo($user->email, $templatesModel->title, $templatesModel->text);
						}
					}
					
					// Send message executor, when completion of the point
					foreach ($zakaz->parts as $stage) {
						if(isset($user->profile)) $time = explode(':', $user->profile->notification_time); // врем€ X, за которое надо уведомл€ть (количество часов и минут), формат "5;48"
						else $time[0] = 0;
						if (count($time)<2) $time[1] = 0;
						$date = date('Y-m-d H:i',strtotime($stage->date));
						$date = strtotime($date)-(int)$time[0]*60*60-(int)$time[1]*60;
						$dateStart = strtotime(date('Y-m-d H:i',$date)) - (self::INTERVAL * 60);
						if (time() > $dateStart && time() <= $date ) {
							echo 'Email stage zakaz #'.$stage->id."\n";
							$templatesModel = Templates::model()->findByAttributes(array('type_id'=>'33'));
							if($templatesModel) {
								$email = new Emails;
								$email->from_id	= 1;
								$email->to_id 	= $user->id;
								$email->name 	= $user->full_name;
								$email->sendTo($user->email, $templatesModel->title, $templatesModel->text);
							}
						}
					}
				}
				
			}
	}
	
	//—оздает событие у менеджера когда наступило врем€
	public function manager() {
		// ƒата информировани€ менеджера
		$projectsModel = Zakaz::model()->findAll();
		foreach ($projectsModel as $project) {
			$dateStart = strtotime(date('Y-m-d H:i',time())) - (self::INTERVAL * 60);
			//echo 'order #'.$project->id.' '.$project->title.': '.$project->manager_informed."\n";
			if (strtotime(date('Y-m-d H:i',strtotime($project->manager_informed))) >= $dateStart && strtotime(date('Y-m-d H:i',strtotime($project->manager_informed))) < strtotime(date('Y-m-d H:i',time()))) {
				//echo Company::getId().' #'.$project->id.' manager informed'."\n";
				Yii::import('application.modules.project.components.EventHelper');
				EventHelper::managerInformed($project->id);
			}
		}
		
		// ” части заказа незавершенного заказа
		$projectsPartsModel = ZakazParts::model()->findAllByAttributes(array('status_id'=>'1'));
		foreach ($projectsPartsModel as $projectStage) {
			$dateStart = strtotime(date('Y-m-d H:i',time())) - (self::INTERVAL * 60);
			if (strtotime(date('Y-m-d H:i',strtotime($projectStage->date))) >= $dateStart && strtotime(date('Y-m-d H:i',strtotime($projectStage->date))) < strtotime(date('Y-m-d H:i',time()))) {
				Yii::import('application.modules.project.components.EventHelper');
				EventHelper::stageExpired($projectStage->proj_id);
			}
		}
	}
}