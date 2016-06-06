<?php
/* @var $this ZakazController */
/* @var $model Zakaz */
/* @var $profile Profile */
/* @var $author User */
/* @var $customer User */

$user = User::model();
$author = $model->author;
$customer = $model->user;
?>

<?php Yii::app()->getClientScript()->registerCssFile(Yii::app()->theme->baseUrl.'/css/manager.css');?>

    <span id="order_number" style="visibility: hidden; position: absolute;"><?php echo $model->id; ?></span>

	<!--</h1>-->
	<div class="row before-panel-group left">
		<button id="close_order" class="btn btn-icon-40 btn-spam bg-blue" onclick="spam(<?php echo $model->id; ?>);" href="">
			<img src="<?=Yii::app()->theme->baseUrl?>\images\spam.png" title="<?=ProjectModule::t('Search executor')?>">
			<?=Tools::hint($hints['Zakaz_search'], 'hint-block __search')?></button>
	</div>
    <div class="row before-panel-group right">
		<?php if ($model->status < 5) { ?>
			<button id="close_order" class="btn btn-icon-40 btn-change-status bg-green z-index-2" onclick="js: window.location='<?php echo $this->createUrl('',array('id'=>$model->id,'close'=>'yes'));?>'; send_message(17,'Заказчику о завершении заказа');">
				<img src="<?=Yii::app()->theme->baseUrl?>\images\handshake.png" title="<?=ProjectModule::t('Complete the order')?>">
				<?php echo Tools::hint($hints['Zakaz_close'], (strlen($hints['Zakaz_close'])>50)?'hint-block __order 2x':'hint-block __order'); ?></button>
			<button id="refound_order" class="btn btn-icon-40 btn-change-status bg-red" onclick="js: window.location='<?php echo $this->createUrl('',array('id'=>$model->id,'refound'=>'yes'));?>';">
				<img src="<?=Yii::app()->theme->baseUrl?>\images\refound.png" title="<?=ProjectModule::t('Refound and close order')?>">
				<?=Tools::hint($hints['Zakaz_refound'], 'hint-block __order')?></button>
		<?php } else { ?>
			<button id="open_order" class="btn btn-icon-40 btn-change-status bg-red" onclick="js: window.location='<?php echo $this->createUrl('',array('id'=>$model->id,'open'=>'yes'));?>';">
			<img src="<?=Yii::app()->theme->baseUrl?>\images\handshake.png" title="<?=ProjectModule::t('Open order')?>">
			<?=Tools::hint($hints['Zakaz_open'], 'hint-block __order')?></button>
		<?php } ?>

        <?php
        //$this->renderPartial('_order_list_update');
        ?>
    </div>
    
    <div class="row">
        <div class="col-xs-12 info-block">
            <div class="panel-group" id="accordion">
                <div class="panel panel-default">
                    <div class="panel-heading panel-heading-white">
                        <h4 class="panel-title">
                            <a data-toggle="collapse" data-parent="#accordion" href="#infoZakaz">
                                <?=ProjectModule::t('Order information').' '.$model->id.': "'.$model->title.'"'?> <i class="fa fa-angle-down fa-lg"></i>
								<?php if ($hints['Zakaz_info']) { ?>
								<div class="hint-block __info">
									?
									<div class="hint-block_content">
										<?=$hints['Zakaz_info']?>
									</div>
								</div>
								<?php } ?>
                            </a>
                            <br/><!--<a data-toggle="collapse" data-parent="#accordion" href="#infoZakaz">-->
                            <!--<img onclick="this.style.transform+='rotate(180deg)'" src="http://crm.obshya.com/themes/admin/views/project/zakaz/line_2.jpg" id="str" />-->
                            <!--</a>-->
                        </h4>
                    </div>
                    <div id="infoZakaz" class="panel-collapse collapse">
                        <div class="panel-body">
                            <div class="col-xs-12 aboutZakaz">
                                <div class="form-container">

                                    <?php $form = $this->beginWidget('CActiveForm', array(
                                        'id' => 'zakaz-form',
                                        'enableAjaxValidation' => false,
                                    ));
                                    echo $form->errorSummary($model); ?>

									<div class="form-item notesBlockArea">
										<?php echo $form->labelEx($model, 'author_notes'); ?>
										<?php echo $form->textArea($model, 'author_notes', array('rows' => 3, 'class' => 'notesBlockTextarea')); ?>
									</div>

									<div class="form-item">
									<?php
									// --- company
									if(isset(Zakaz::$files_folder)){
										$url = Zakaz::$files_folder.$model->id.'/';
									} else {
										$url = '/uploads/'.$model->id.'/';
									}
									// ---
									$path = Yii::getPathOfAlias('webroot').$url;
									$tmp = '';
									if (file_exists($path)) {
										foreach (array_diff(scandir($path), array('..', '.')) as $k => $v) {
											$tmp .= '<li><a class="link-to-material" href="' . $url . rawurlencode($v) . '">' . str_replace('#pre#', '', $v) . '</a>';
											$tmp .= '<a href="#" data-id="' . $model->id . '" data-name="' . $v . '" onclick="removeFile(this); return false;"><i class="glyphicon glyphicon-remove" title="'. Yii::t('site', 'Delete') .'"></i></a>'; #remove file btn
											if (strstr($v, '#pre#'))
												$tmp .= '<button id="approveFile_file" data-id="' . $model->id . '" data-name="' . $v . '" class="right btn" onclick="approveFile(this); return false;">'. ProjectModule::t('Approve') .'</button>';
											$tmp .= '</li>';
										}
									}

									$this->widget('ext.EAjaxUpload.EAjaxUpload',
										array(
											'id' => 'justFileUpload',
											'config' => array(
												'action' => $this->createUrl('/project/zakaz/upload',array('id'=>$model->id)),
												'template' => '<div class="qq-uploader"><div class="qq-upload-drop-area"><span>'. ProjectModule::t('Drag and drop files here') .'</span><div class="qq-upload-button">'. ProjectModule::t('Upload material') .'</div><ul class="qq-upload-list">'.$tmp.'</ul></div></div>',
												'disAllowedExtensions'=>array('exe','scr'),
												'sizeLimit' => Tools::maxFileSize(), // maximum file size in bytes
												'minSizeLimit' => 1,// minimum file size in bytes
												'onComplete' => "js:function(id, fileName, responseJSON){}"
											)
										)
									);
									?>
									</div>
									<?php $this->renderPartial('_form', array('model' => $model, 'form' => $form)); ?>


									<div class="form-item">
										<h3> <?=ProjectModule::t('Deadlines')?> </h3>
										<table class="table table-striped" style="font-size: 12px;">
											<thead>
											<th><?=ProjectModule::t('Product name')?></th>
											<th><?=ProjectModule::t('Date/Time')?></th>
											</thead>
											<tr>
												<td>
													<?php echo $form->labelEx($model, 'date'); ?>
												</td>
												<td>
													<?php
													$this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
														'model' => $model,
														'attribute' => 'dbdate',
													));?>
												</td>
											</tr>
											<?php
												$projectFields = $model->getFields();
												if ($projectFields) foreach($projectFields as $field) {
													if ($field->field_type=="TIMESTAMP") {
													$varname = $field->varname;
													$model->timestampOutput($field);
											?><tr>
												<td>
													<?php echo $form->labelEx($model, $varname); ?>
												</td>
												<td><?php
														$this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
															'model' => $model,
															'attribute' => $varname,
														));?>
												</td>
											</tr><?php
													}
												}
											?>
										</table>
									</div>
                                    <!--<div class="form-save">
										<?php echo CHtml::submitButton(ProjectModule::t('Save'), array('class' => 'btn btn-primary')); ?>
                                    </div>-->

                                    <?php $this->endWidget(); ?>
                                </div>
                                <!-- form -->
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    
    <div class="row order-contacts">
        <div class="col-lg-6 col-xs-6 rightBorder">
            <div class="role"><b><a href="<?php echo Yii::app()->createUrl('/user/admin/update',array('id'=>$customer->id));?>"><?=ProjectModule::t('Customer')?></a></b></div>
            <?php if ($customer->full_name) { ?><div class="name"><p><?= $customer->full_name ?></p></div><?php } ?>
            <div class="mail"><p><?= $customer->email ?></p></div>
            <?php if ($customer->phone_number) { ?><div class="phone"><p><?php
				$this->widget('application.widgets.CallBtn', array(
					'to'=>$customer->phone_number,
				));
				echo $customer->phone_number; ?></p></div><?php } ?>
        </div>
        <div class="col-lg-6 col-xs-6 leftBorder">
			<?php if ($author){ ?>
            <div class="role"><b><a href="<?php echo Yii::app()->createUrl('/user/admin/update',array('id'=>$author->id));?>"><?=ProjectModule::t('Executor')?></a></b></div>
            <?php if ($author->full_name) { ?><div class="name"><p><?= $author->full_name ?></p></div><?php } ?>
			<div class="mail"><p><?= $author->email ?></p></div>
            <?php if ($author->phone_number) { ?><div class="phone"><p><?php
				$this->widget('application.widgets.CallBtn', array(
					'to'=>$author->phone_number,
				));
				echo $author->phone_number; ?></p></div><?php } ?>
			<?php } else { ?>
				<div class="name"><p><?=ProjectModule::t('Executor is not assigned')?></p></div>
			<? } ?>
        </div>
        
    </div>

    <div class="row">
       <!-- Left column -->

        <div class="col-xs-4 left-column">
            <div class="row zero-edge">
				<div class="col-xs-12 statusBlock">
                   <!--<span class="label label-warning"><b><?php //echo $message; ?></b></span>-->
				   <span class="block-title"><?php echo $form->labelEx($model, 'status'); ?>:&nbsp;</span>
				   <?=CHtml::dropDownList('Zakaz_status', $model->status, CHtml::listData(ProjectStatus::model()->findAll(), 'id', 'status'),
                            array('ajax' => array('url' => $this->createUrl('/project/zakaz/update'),
                                                  'data' => 'js:"id='.$model->id.'&sid="+this.value',
                                                  'cache' => false,
                                                  ),)); ?>

					<?=Tools::hint($hints['Zakaz_status'], 'hint-block __status')?>
					<!--<button class="btn btn-primary btn-spam" onclick="spam(<?php echo $model->id; ?>);" href=""><?=ProjectModule::t('Search author')?></button>-->
					<br><span class="last-delivery"><?=ProjectModule::t('Last delivery').': '.$model->last_spam ?></span>
				</div>

			    <hr>
			   
				<div class="col-xs-12 techspecBlock">
                    <input type="checkbox" name="technicalspec" id="technicalspec" data-id="<?=$model->id?>" <?=($model->technicalspec ? 'checked="checked"' : '')?> />
                    <label for="technicalspec"><?=ProjectModule::t('technicalspec')?></label>
				</div>

				<hr>

				<div class="col-xs-12 linkToAuthors">
					<span class="block-title"><?=ProjectModule::t('Link for freelancer')?>:</span><br>
					<?='http://'.$_SERVER["HTTP_HOST"].Yii::app()->createUrl('/project/chat/view',array('orderId'=>$model->id));?>
					<?=Tools::hint($hints['Zakaz_link'], 'hint-block __link')?>
				</div>
            </div>
			<hr>
            <?php if ($isModified) echo '<span><b>'. ProjectModule::t('Order moderation') .'</b></span>';?>

            <div class="row">
                <div class="col-xs-12">
                    <!--<h4><?=ProjectModule::t('Notes (on all orders)')?></h4>-->

                    <?php $form = $this->beginWidget('CActiveForm', array(
                        'id' => 'zakaz-form',
                        //'enableAjaxValidation' => true,
                        //'enableClientValidation'=>true,
                        //'action'=>Yii::app()->createAbsoluteUrl('/zakaz/update',array('id'=>$model->id)),
                        'clientOptions'=>array(
                            'validationDelay'=>2000,
                            'validateOnType'=>true,
                        ),
                        //'ajaxType'=>'POST',
                        /*'htmlOptions'=>array(
                            'onkeypress'=>'alert(event.keyCode);'
                        ),*/
                    ));
                    echo $form->errorSummary($model); ?>
                    <div class="col-xs-12 notesBlockArea">
                        <span class="block-title"><?php echo $form->labelEx($model, 'notes'); ?></span>
						<?=Tools::hint($hints['Zakaz_notes'], 'hint-block __notes')?>
                        <?php echo $form->textArea($model, 'notes', array('rows' => 3, 'class' => 'notesBlockTextarea')); ?>
                    </div>

                    <?php //echo CHtml::submitButton('Сохранить',''); ?>

                    <?php $this->endWidget(); ?>
                </div>
            </div>
			<hr>
			
			<?php
			if (Company::getCompany()->module_tree) $this->widget('application.modules.project.widgets.zakazTree.ZakazTreeWidget', array(
                'project'=>$model,
            ));
			?>

            <?php Yii::app()->getClientscript()->registerScriptFile(Yii::app()->theme->baseUrl.'/js/manager.js');?>
			<!-- Начало блока добавления этапов менеджера -->
			<h5 class="stages"><span class="block-title"><?=ProjectModule::t('Work stages')?>:</span><?=Tools::hint($hints['Zakaz_stages'], 'hint-block __stages')?></h5>
			<?php
            $this->widget('application.modules.project.widgets.zakazParts.ZakazPartWidget', array(
                'projectId'=>$model->id,
				'hints'=>$hints,
            ));
            ?>
            <div class="row zero-edge">
                <div class="col-xs-12 btn btn-primary addPart" onclick="add_part(<?php echo $model->id;?>,'<?=ProjectModule::t('New stage')?>');">
                	<?=ProjectModule::t('Add a stage')?>
					<?=Tools::hint($hints['Zakaz_add_part'], 'hint-block __add_part')?>
                </div>
            </div>
            <!-- Конец блока добавления этапов менеджера -->
			<br><hr>
            <!-- Начало блока правок (доработок) менеджера -->

            <?php
            $this->widget('application.modules.project.widgets.changes.ChangesWidget', array(
                'project' => $model,
                'hints'=>$hints,
            ))
            ?>
            <!-- Конец блока правок (доработок) менеджера -->

        </div>

         <!-- Right column -->
        <div class="col-xs-8">
           <div class="row">
                <?php
                    $this->renderPartial('_formUpdateManager', array(
                    	'model' => $model,
                    	'times' => $times,
                    	'hints' => $hints,
                    ));
                ?>

            </div>
        </div>
	</div>
