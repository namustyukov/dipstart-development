<?php
/* @var $this ZakazController */
/* @var $model Zakaz */
/* @var $form CActiveForm */
?>


<div class="col-xs-12 terms-block" style="padding-right: 0px;">
    <div class="form">

        <?php $form = $this->beginWidget('CActiveForm', array(
            'id' => 'zakaz-form',
            'enableAjaxValidation' => false,
        ));
        echo $form->errorSummary($model); ?>
    </div>
    <div class="row">
        <!--<div class="col-xs-6"><h4><?=ProjectModule::t('Deadlines')?></h4></div>
        <div class="col-xs-6" style="float:right;">
     		<?php //$attr = array ('class' => 'btn btn-primary terms-save-btn'); ?>
			<?php //if(Yii::app()->user->isGuest) $attr['disabled'] = 'disabled'; ?>
            <?php //echo CHtml::submitButton(ProjectModule::t('Save'), array('class' => 'btn btn-primary terms-save-btn')); ?>
        </div>-->
        <div class="col-xs-4 terms-columns terms-column-1">
            <span>
                <?php echo $form->labelEx($model, 'max_exec_date'); ?>
				<?=Tools::hint($hints['Zakaz_exec_date'], 'hint-block __exec_date')?>
            </span>
            <?php
            $this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
                'model' => $model,
                'attribute' => 'dbmax_exec_date',
            ));?>
        </div>
        <div class="col-xs-4 terms-columns terms-column-2">
            <span>
                <?php echo $form->labelEx($model, 'manager_informed'); ?>
				<?=Tools::hint($hints['Zakaz_manager_informed'], 'hint-block __manager_informed')?>
            </span>
            <?php
            $this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
                'model' => $model,
                'attribute' => 'dbmanager_informed',
            ));?>
        </div>
        <div class="col-xs-4 terms-columns terms-column-3 terms-columns-last">
            <span>
                <?php echo $form->labelEx($model, 'author_informed'); ?>
				<?=Tools::hint($hints['Zakaz_author_informed'], 'hint-block __author_informed')?>
            </span>
            <?php
            $this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
                'model' => $model,
                'attribute' => 'dbauthor_informed',
            ));?>
        </div>




        <?php $this->endWidget(); ?>
    </div>
    <!-- form -->
</div>

<div class="col-xs-12 chatBlockBg">
    <div class="chatBlock chtpl0-chatblock chtpl0-admin">
		<?=Tools::hint($hints['Zakaz_chat'], 'hint-block __chat')?>
		<div class="chtpl0-panel chtpl0-up">
			<button class="chtpl0-show"><?=ProjectModule::t('Show messages')?></button>
		</div>
        <div id="chatWindow" class="chtpl0-chat"></div>
		<div class="chtpl0-panel chtpl0-down chat-functions">
            <label>
                <select id="select_recipient" class="select_recipient">
                    <option value="" selected><?= ProjectModule::t('Not selected')?></option>
                    <option value="<?= Templates::TYPE_AUTHOR ?>"><?= ProjectModule::t('to executor')?></option>
                    <option value="<?= Templates::TYPE_CUSTOMER ?>"><?= ProjectModule::t('to customer')?></option>
                    <option value="<?= Templates::TYPE_CORRECTOR ?>"><?= ProjectModule::t('to corrector')?></option>
                </select>
				<?=Tools::hint($hints['Zakaz_recipient'], 'hint-block __recipient')?>
            </label>
            <input id="send_email" type="checkbox" checked="checked"><p><?=ProjectModule::t('Send to e-mail')?></p>
			<?=Tools::hint($hints['Zakaz_send_email'], 'hint-block __send_email')?>
            <input id="send_sms" type="checkbox"><p><?=ProjectModule::t('Send SMS')?></p>
			<?=Tools::hint($hints['Zakaz_send_sms'], 'hint-block __send_sms')?>
            <button class="chtpl0-template attach_template hidden" data-toggle="modal" data-target="#templates_modal"></button>
            <p class="attach_template hidden"><?=ProjectModule::t('Use template')?></p>
            <?php
                $this->widget('application.widgets.TemplateModal', [
                    'id' => 'templates_modal',
                    'types' => [
                        Templates::TYPE_CUSTOMER,
                        Templates::TYPE_AUTHOR,
                    ],
					'orderId' => $model->id
                ]);
            ?>
		</div>
        <?php
        Yii::app()->getClientScript()->registerScriptFile('/js/tinymce/tinymce.min.js');
        Yii::app()->getClientScript()->registerScriptFile('/js/chat_templates.js', CClientScript::POS_END);
        ?>

		<div class="chtpl0-form">
			<textarea></textarea>
			<div class="chtpl0-subm">
				<h5><?=ProjectModule::t('Send message')?></h5>
				<br>
				<button class="chtpl0-submit1"><?=ProjectModule::t('Send message')?></button>
			</div>
		</div>
        <?php
        $this->widget('YiiChatWidget', array(
            'chat_id' => $model->id,
            'executor' => $model->executor,
            'identity' => Yii::app()->user->id,
            'selector' => '#chatWindow',
            'minPostLen' => 1,
            'maxPostLen' => 5000,
            'model' => new ChatHandler(),
            'data' => 'any data',
            'onSuccess' => new CJavaScriptExpression(
                "function(code, text, post_id){   }"),
            'onError' => new CJavaScriptExpression(
                "function(errorcode, info){  }"),
        ));
        ?>
    </div>
</div>

<div class="col-xs-12 payment-block">
    <?php
    $this->widget('application.modules.project.widgets.payment.PaymentWidget', array(
        'projectId' => $model->id,
        'hints'=>$hints,
    ));
    ?>
</div>

<?php
$this->widget('application.modules.project.widgets.tips.TipsWidget', array(
    'project' => $model
));
?>
