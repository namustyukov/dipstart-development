<?php

class TipsWidget extends CWidget{

    public $project;
    public $parts;
    public $changes;
    public $payments;
    public $messages;

    protected $statusArr = [
        'is_just_enter', // Заказ только поступил
        'is_wait_customer_decision_without_cash', // Ждем решения заказчика (если нет предоплаты еще)
        'is_wait_customer_decision_about_stage', // Ждем решения заказчика (по утверждению этапа)
        'is_enter_prepayments', // Внесение предполаты заказчиком
        'is_no_author_after_mail', // Нет ни одного автора спустя сутки после рассылки
        'is_enter_cost', // Исполнитель написал свое предложение на новый заказ
        'is_sent_stage', // Исполнитель выслал этап работы
        'is_not_sent_stage_on_time', // Исполнитель не выслал этап в срок
        'is_not_sent_all_on_time', // Исполнитель не выслал в срок работу
        'is_new_message', // Сообщение в чате
        'is_new_changes', // Появились доработки к работе
        'is_time_passed', // Дата и время выполнения прошли (от заказчика)
    ];

    public function init() {
        $this->parts = ZakazParts::model()->findAllByAttributes(['proj_id'=>$this->project->id]);
        $this->payments = ProjectPayments::model()->findByAttributes(['order_id'=>$this->project->id]);
        $this->changes = ProjectChanges::model()->findAllByAttributes(['project_id'=>$this->project->id]);
        $this->messages = ProjectMessages::model()->findAllByAttributes(
            ['order'=>$this->project->id],
            ['order'=>'id DESC']
        );
    }

    public function checkStatus($statusId) {
        switch ($statusId) {
            case 'is_just_enter':
                return $this->project->status == 1 && $this->project->is_active == 1;
                break;

            case 'is_wait_customer_decision_without_cash':
                return $this->project->status == 2 && !count($this->project->images);
                break;

            case 'is_wait_customer_decision_about_stage':
                foreach ($this->parts as $part) {
                    if ($part->status_id == 3)
                        return true;
                }
                return false;
                break;

            case 'is_enter_prepayments':
                return (!$this->payments->received || $this->payments->received == 0) && count($this->project->images);
                break;

            case 'is_no_author_after_mail':
                $currentDate = date_create();
                $lastSpamDate = date_create($this->project->last_spam);
                $lastSpamDate->modify('+1 day');
                return $this->project->status == 3 && $currentDate > $lastSpamDate;
                break;

            case 'is_enter_cost':
                if ($this->project->status == 3 && !$this->project->executor)
                {
                    $isNew = false;
                    foreach ($this->messages as $message)
                    {
                        if (User::model()->getUserRole($message->sender) == 'Author')
                        {
                            $tip = TipDone::model()->findByAttributes([
                                'message_id' => $message->id,
                                'status' => 'is_enter_cost',
                            ]);
                            if (!$tip)
                            {
                                $isNew = true;
                                $tip = new TipDone;
                                $tip->message_id = $message->id;
                                $tip->status = 'is_enter_cost';
                                $tip->save();
                            }
                        }
                    }
                    return $isNew;
                }
                return false;
                break;

            case 'is_sent_stage':
                foreach ($this->parts as $part) {
                    if ($part->status_id == 2)
                        return true;
                }
                return false;
                break;

            case 'is_not_sent_stage_on_time':
                $currentDate = date_create();
                foreach ($this->parts as $part) {
                    if ($part->status_id == 1 && $currentDate > date_create($part->date))
                        return true;
                }
                return false;
                break;

            case 'is_not_sent_all_on_time':
                $currentDate = date_create();
                return $this->project->status == 4 && $currentDate > date_create($this->project->author_informed);
                break;

            case 'is_new_message':
                $isNew = false;
                foreach ($this->messages as $message)
                {
                    $current_role = User::model()->getUserRole($message->sender);
                    if ($current_role != 'Manager' && $current_role != 'Admin')
                    {
                        $tip = TipDone::model()->findByAttributes([
                            'message_id' => $message->id,
                            'status' => 'is_new_message',
                        ]);
                        if (!$tip)
                        {
                            $isNew = true;
                            $tip = new TipDone;
                            $tip->message_id = $message->id;
                            $tip->status = 'is_new_message';
                            $tip->save();
                        }
                    }
                }
                return $isNew;
                break;

            case 'is_new_changes':
                foreach ($this->changes as $change) {
                    if (!$change->date_moderate)
                        return true;
                }
                return false;
                break;

            case 'is_time_passed':
                $currentDate = date_create();
                return ($this->project->status == 4 || $this->project->status == 3) && $currentDate > date_create($this->project->max_exec_date);
                break;
        }
    }

    public function getStatus() {
        $currentStatus = array();
        foreach ($this->statusArr as $item)
        {
            if ($this->checkStatus($item))
                $currentStatus[] = $item;
        }
        return $currentStatus;
    }

    public function run() {
    	$statuses = $this->getStatus();
        $tips = array();
        foreach ($statuses as $item)
        {
            $tip = Templates::model()->findByAttributes([
                'type_id' => 5,
                'name' => $item,
            ]);
            if ($tip)
                $tips[] = $tip;
        }

    	$this->render('view', array(
            'tips' => $tips
        ));
    }
}
