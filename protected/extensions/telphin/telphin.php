<?php
require_once('myRequest.php');
require_once('myResponse.php');



/**
 * Все запросы к Телфину здесь. Хелпер для запросов.
 *
 * @author Mendel
 */
class telphin
{
    const FLOW_LOCAL = 2; // локальный звонок
    const FLOW_OUT = 4; // исходящий внешний звонок
    const FLOW_IN = 8;// входящий внешний звонок
    
    const DISPOSITION_ANSWERED = 0 ; // звонки, на которые ответили
    const DISPOSITION_BUSY = 1 ; // звонки, при которых вызываемый номер был занят
    const DISPOSITION_FAILED = 2 ; // неудачные звонки
    const DISPOSITION_NO_ANSWER = 3 ; // звонки, на которые не был получен ответ
    const DISPOSITION_UNKNOWN = 4 ; // неизвестные звонки
    const DISPOSITION_NOT_ALLOWED = 5 ; // звонки, выполнение которых не было разрешено
    
    protected $host;
    public $token;
    protected $app_id;
    protected $app_secret;
    protected $extension;
    
    public function __construct($app_id, $app_secret, $extension, $host = 'hosted.telphin.ru') {
        $this->app_id = $app_id;
        $this->app_secret = $app_secret;
        $this->extension = $extension;
        $this->host = $host;
    }
    
    /**
     * Получим новый токен для доступа к телфину
     *
     * @return string token
     * @return boolean FALSE если ошибка
     */
    public function generateToken() {
        $reqUrl = 'https://'.$this->host.'/oauth/token.php';
        $request = new myRequest();
        $request->setMethod(myRequest::METHOD_POST);
        $request->setBody([
            'grant_type' => 'client_credentials',
            'redirect_uri' => $_SERVER['PHP_SELF'],
            'client_id' =>  urlencode($this->app_id),
            'client_secret' => urlencode($this->app_secret),
            'state' => '0',
        ]);
        $response = $request->sendRequest($reqUrl);
        $respBody = $response->getBody();
        if ($response->getStatus() == myResponse::STATUS_OK && isset($respBody['access_token'])) {
            $this->token = 'Bearer '.$respBody['access_token'];
            return 'Bearer '.$respBody['access_token'];
        }
        return false;
    }

    /**
     * Возвращает токен из сессии, или получает новый, если токена нет
     *
     * @return string token
     */
    public function getToken() {
        if (!is_null($this->token)) {
            $token = $this->token;
        } else {
            /* generate token */
            $token = $this->generateToken();
        }
        return $token;
    }

    protected function basicRequest($url,$method, $data=NULL) {
        $token = $this->getToken();
        if (!$token) {return false;}
        $reqUrl = 'https://'.$this->host.$url;
        $request = new myRequest();
        if($data) {
            if($method <> myRequest::METHOD_GET) $request->setBody(json_encode($data));
            else $request->setBody($data);
        }
        $request->setMethod($method);
        $request->setHeaders([
            'Content-type' => 'application/json',
            'Authorization' => $token,            
        ]);
        $response = $request->sendRequest($reqUrl);
        if ($response->getStatus() == myResponse::STATUS_FORBIDDEN) {
            // try to regenerate token
            $request->setHeaders([
                'Content-type' => 'application/json',
                'Authorization' => $this->generateToken(),
            ]);
            // retry request
            $response = $request->sendRequest($reqUrl);
        }
        return $response->getBody(true);
    }

    public function basicPost($url, $data=NULL) {
        $token = $this->getToken();
        if (!$token) {return false;}
        //
        $reqUrl = 'https://'.$this->host.$url;
        $request = new myRequest();
        $request->setMethod(myRequest::METHOD_POST);
        //
        if($data) {$request->setBody(json_encode($data));}
        //
        $request->setHeaders([
            'Content-type' => 'application/json',
            'Authorization' => $token,            
        ]);
        $response = $request->sendRequest($reqUrl);
        if ($response->getStatus() == myResponse::STATUS_FORBIDDEN) {
            // try to regenerate token
            $request->setHeaders([
                'Content-type' => 'application/json',
                'Authorization' => $this->generateToken(),
            ]);
            // retry request
            $response = $request->sendRequest($reqUrl);
        }
        return $response->getBody(true);
    }
    
    public function basicGet($url, $data=NULL) {
        $token = $this->getToken();
        if (!$token) {return false;}
        //
        $reqUrl = 'https://'.$this->host.$url;
        $request = new myRequest();
        $request->setMethod(myRequest::METHOD_GET);
        //
        if($data) {$request->setParameters($data);}
        //
        $request->setHeaders([
            'Content-type' => 'application/json',
            'Authorization' => $token,            
        ]);
        $response = $request->sendRequest($reqUrl);
        if ($response->getStatus() == myResponse::STATUS_FORBIDDEN) {
            // try to regenerate token
            $request->setHeaders([
                'Content-type' => 'application/json',
                'Authorization' => $this->generateToken(),
            ]);
            // retry request
            $response = $request->sendRequest($reqUrl);
        }
        return $response->getBody(true);
    }
    
    /**
     * Инициируем звонок
     * @param type $to
     * @param type $from
     * @return type
     */
    public function call($to, $from=NULL) {
        if(is_null($from)) $from = $this->extension;
        $url = '/unifiedapi/phoneCalls/@me/simple';
        $data = [
            'extension' => $this->extension,
            'phoneCallView' => [
                [
                    'source' => [$from],
                    'destination' => $to
                ]
            ]
        ];
        $result = $this->basicPost($url, $data);
        return json_decode($result);
    }

    protected function flow2text($code) {
        $text = [
            self::FLOW_LOCAL =>'LOCAL',
            self::FLOW_OUT =>'OUT',
            self::FLOW_IN =>'IN',
        ];
        return $text[$code];
    }
    
    protected function disposition2text($code) {
        $text = [
            self::DISPOSITION_ANSWERED =>'ANSWERED',
            self::DISPOSITION_BUSY =>'BUSY',
            self::DISPOSITION_FAILED =>'FAILED',
            self::DISPOSITION_NO_ANSWER =>'NO_ANSWER',
            self::DISPOSITION_UNKNOWN =>'UNKNOWN',
            self::DISPOSITION_NOT_ALLOWED =>'NOT_ALLOWED',
        ];
        return $text[$code];
    }


    public function cdr($data = NULL) {
        $url = '/uapi/cdr/';
        $result = $this->basicGet($url,$data);
        $result = json_decode($result);
		//print_r($result);
        //
        $call = [];
        foreach ($result->entry as $key => $record) {
            foreach($record->phoneCallView as $view) {
                $line = [];
                $published = strtotime($record->published);
                $line['published'] = $published;
                $answered = isset($view->answered)?$view->answered:false;
                if(!($answered)) {
                    $answered = strtotime($answered);
                    $line['answerDuration'] = $answered - $published;
                } else {
                    $line['answerDuration'] = NULL;
                }
                $line['source'] = $view->source;
                $line['destination'] = $view->destination;
                $line['duration'] = $view->duration;
                $line['flowCode'] = $view->flow;
                $line['dispositionCode'] = $view->disposition;
                //
                $uid = md5(serialize($line).$key);
                $line['uid'] = $uid;
                //
                $line['dateText'] = date('d M Y', $published);
                $line['timeText'] = date('H:i:s', $published);
                $line['flowText'] = $this->flow2text($view->flow);
                $line['dispositionText'] = $this->disposition2text($view->disposition);
                //
                $call[] = $line;
            }
        }
        return $call;
    }    
}
