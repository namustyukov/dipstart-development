<?php

/**
 * поведение для сохранения изменений у сущности
 */
class ModerateBehavior extends CActiveRecordBehavior
{
    private $old_attributes;
    
    public function afterFind($event)
    {
        $this->old_attributes = $this->owner->attributes;
    }
    
    public function beforeSave($event)
    {
        
        $tmp_event_id = 0;
        $is_change = false;
        
        $role = User::model()->getUserRole();
        
        if (!$this->owner->isNewRecord && $role != 'Manager' && $role != 'Admin') {
        
            $tmp_event_id = time();
            
            foreach ($this->old_attributes as $key => $value) {
                
                if ($key == 'max_exec_date') {
                    
                    $old_value = Yii::app()->dateFormatter->format($this->owner->dateTimeOutcomeFormat, strtotime($value));
                    $new_value = Yii::app()->dateFormatter->format($this->owner->dateTimeOutcomeFormat, strtotime($this->owner->max_exec_date));
                    
                    if ($old_value === $new_value) {
                        continue;
                    }
                    
                } else {
                    $old_value = $value;
                    $new_value = $this->owner->$key;
                }

                if ($old_value != $new_value && !(User::model()->isCorrector() && $key == 'technicalspec') && $key != 'executor_event' && $key != 'customer_event') {
                    
                    $is_change = true;

                    $moderate = new Moderate;
                    $moderate->event_id = $tmp_event_id;
                    $moderate->class_name = get_class($this->owner);
                    $moderate->id_record = $this->owner->primaryKey;
                    $moderate->attribute = $key;
                    $moderate->old_value = $old_value;
                    $moderate->new_value = $new_value;
                    $moderate->save(false);

                }

            }
            
            if ($is_change) {
                
                Yii::import('application.modules.project.components.EventHelper');
                
                switch (get_class($this->owner))
                {
                    case 'Profile':
                        $event_id = EventHelper::updateProfile();
                        break;
                    case 'Zakaz':
                        $event_id = EventHelper::editOrder($this->owner->primaryKey);
                        break;
                }
                
                Moderate::model()->updateAll(['event_id'=>$event_id], 'event_id=:event_id', [':event_id'=>$tmp_event_id]);
            }

            if (User::model()->isCorrector() && ($this->owner->attributes['technicalspec'] != $this->old_attributes['technicalspec']))
                $this->old_attributes['technicalspec'] = $this->owner->attributes['technicalspec'];

            $this->old_attributes['executor_event'] = $this->owner->attributes['executor_event'];
            $this->old_attributes['customer_event'] = $this->owner->attributes['customer_event'];
            $this->owner->attributes = $this->old_attributes;
        }
        else
        {
            if (!$this->owner->isNewRecord && get_class($this->owner) == 'Zakaz')
            {
                $authorInformedUpdated = false;
                foreach ($this->old_attributes as $key => $value) {
                    $old_value = $value;
                    $new_value = $this->owner->$key;
                    if ($old_value != $new_value && $key != 'executor_event' && $key != 'customer_event')
                        $is_change = true;
                    if ($old_value != $new_value && $key == 'author_informed')
                        $authorInformedUpdated = true;
                }
                if ($authorInformedUpdated) {
                    if ($this->owner->executor_event)
                    {
                        $events = explode(",", $this->owner->executor_event);
                        if (!in_array(3, $events))
                        {
                            $events[] = 3;
                            $this->owner->executor_event = implode(",", $events);
                        }
                    }
                    else
                        $this->owner->executor_event = 3;
                }
                elseif ($is_change) {
                    if ($this->owner->executor_event)
                    {
                        $events = explode(",", $this->owner->executor_event);
                        if (!in_array(1, $events))
                        {
                            $events[] = 1;
                            $this->owner->executor_event = implode(",", $events);
                        }
                    }
                    else
                        $this->owner->executor_event = 1;
                }
            }
        }
    }
}