<?php
    Yii::app()->clientScript->registerScriptFile('/js/event.js');
    Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/manager.css');?>
<?
/**
 * @author Emericanec
 */
?>
<div class="events">
    <h2><?=ProjectModule::t('Events')?></h2>
   <div class="row white-block">
        <div class="col-xs-12 events-list">
            <?php $this->renderPartial('list',array('events'=>$events));?>
        </div>
   </div>
</div>  

<audio id="is-new-event" src="/audio/new_event.wav"></audio>
<audio id="is-new-order" src="/audio/new_order.wav"></audio>
