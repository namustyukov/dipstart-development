<?php

class TipsController extends Controller {
    public function actionView($id) {
		$template = Templates::model()->findByPk($id);

		$this->render('view', array(
			'template'	=> $template,
		));
	}
}
