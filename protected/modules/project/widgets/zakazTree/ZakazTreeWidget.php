<?php

class ZakazTreeWidget extends CWidget {
	public $project;
	public $childs;
	
    public function init() {
        $this->childs = Zakaz::model()->findAllByAttributes(['parent_id'=>$this->project->id]);
	}
	
    public function run() {
    	/*$statuses = $this->getStatus();
        $tips = array();
        foreach ($statuses as $item)
        {
            $tip = Templates::model()->findByAttributes([
                'type_id' => 5,
                'name' => $item,
            ]);
            if ($tip)
                $tips[] = $tip;
        }*/

    	$this->render('view', array(
			'model' => $this->project,
            'childs' => $this->childs
        ));
    }
}