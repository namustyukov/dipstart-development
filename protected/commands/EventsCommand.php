<?php
class EventsCommand extends CConsoleCommand {
	
    public function run($args) {
		//echo 'echo: '.get_class(Yii::app())."\n";
		$companies = Company::model()->findAll('frozen=:p',array(':p'=>'0'));

		foreach($companies as $company) {
			Company::setActive($company);
			self::executor();
			self::manager();
		}
    }
	
	// ������� � ����������� - ���������� ������ �� ����� ����������� �� ���� (����� �� �����������)
	public function executor() {
		$usersModel = User::model()->findAllNotificationExecutors();
		if (is_array($usersModel))
			foreach ($usersModel as $user) {
				foreach ($user->zakaz_executor as $zakaz) {
					$time = explode(';', $user->profile->notification_time); // ����� X, �� ������� ���� ���������� (���������� ����� � �����), ������ "5;48"
					$date = date('Y-m-d H:i',strtotime($zakaz->author_informed));
					$date = strtotime($date)-(int)$time[0]*60*60-(int)$time[1]*60;
					if (strtotime(date('Y-m-d H:i',time())) >= $date) {
						$templatesModel = Templates::model()->findByPk(21);
						
						$email = new Emails;
						$email->from_id	= 1;
						$email->to_id 	= $user->id;
						$email->name 	= $user->full_name;
						$email->sendTo($user->email, $templatesModel->title, $templatesModel->text);
					}
				}
			}
	}
	
	//������� ������� � ��������� ����� ��������� �����
	public function manager() {
		// ���� �������������� ���������
		$projectsModel = Zakaz::model()->findAll();
		foreach ($projectsModel as $project) 
			if (strtotime(date('Y-m-d H:i',strtotime($project->manager_informed))) <= strtotime(date('Y-m-d H:i',time()))) {
				Yii::import('project.components.EventHelper');
				EventHelper::notification('description', $project->id);
			}
		
		// � ����� ������ �������������� ������
		$projectsPartsModel = ZakazParts::model()->findAllByAttributes(array('status_id'=>'1'));
		foreach ($projectsPartsModel as $project) 
			if (strtotime(date('Y-m-d H:i',strtotime($project->date))) <= strtotime(date('Y-m-d H:i',time()))) {
				Yii::import('project.components.EventHelper');
				EventHelper::notification('description', $project->proj_id);
			}
	}
}