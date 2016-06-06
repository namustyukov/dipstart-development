<?php
Yii::import('application.extensions.telphin.telphin');
/**
 * Description of TelfinCdrComponent
 *
 * @author Admin
 */
class TelphinCdrComponent  extends CComponent
{
    private $telfin;
    public $app_id;
    public $app_secret;
    public $extension;
    public $host='hosted.telphin.ru';
    public $count = 4999;
    
	/**
	 * Initializes the component.
	 */
	public function init($who=null) {
		if (get_class(Yii::app())!='CConsoleApplication' || $who == 'cron') {
			$company = Company::getCompany();
			if($company->telfin_id) $this->app_id = $company->telfin_id;
			if($company->telfin_secret) $this->app_secret = $company->telfin_secret;
			if($this->app_id && $this->app_secret) $this->telfin = new telphin(
				$this->app_id,
				$this->app_secret,
				$this->extension,
				$this->host
			);
		}
    }

    
    public function cookie2token() {
        // Проверим нет ли сохраненного токена в сессии
        // Работу с сессией вынес в прикладной код, потому что врапер может вызываться из командной строки
        if (isset($_SESSION['telphin']['token']) && $_SESSION['telphin']['token']) {
            $this->telphin->token = $_SESSION['telphin']['token'];
        }        
    }
    
    public function token2cookie() {
        // Сохраним токен в сессии есть есть что сохранять
        if (!is_null($telphin->token)) {
            $_SESSION['telphin']['token'] = $this->telphin->token;
        }
    }

    
    public function call($to, $from=NULL) {
        return $this->telfin->call($to,$from);
    }
    
    public function refresh() {
		//echo '!!';
        $result = $this->telfin->cdr([
            'count'=>$this->count
        ]);
        foreach($result as $line) {
            $cdr = new CrmCdr();
            $cdr->id = $line['uid'];
            $cdr->published = $line['published'];
            $cdr->source = $line['source'];
            $cdr->destination = $line['destination'];
            $cdr->duration = $line['duration'];
            $cdr->answerDuration = $line['answerDuration'];
            $cdr->flow = $line['flowText'];
            $cdr->result = $line['dispositionText'];
            $cdr->save();
        }
    }
}
