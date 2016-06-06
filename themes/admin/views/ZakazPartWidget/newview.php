<?php

/*$js = <<<JS



JS;

Yii::app()->clientScript->registerScript('loading', $js, CClientScript::POS_READY); 
*/
?>

<div class="row zero-edge">
    <div class="panel-group" id="accordion">
        <div class="panel panel-default">
            <div class="panel-heading stage">
                <div class="partStatus">
					<?= $this->select; ?><?=Tools::hint($this->hints['Zakaz_part_status'], 'hint-block __part_status')?>
					<?php
					$url=Yii::app()->createUrl('/project/zakazParts/apiEditPart');
					$this->widget('ext.juidatetimepicker.EJuiDateTimePicker', array(
						'model' => ZakazParts::model()->findByPk($data['id']),
						'id'=>'partDate'.$data['id'],
						'attribute' => 'dbdate',
						'options'=>array(
							'onSelect'=> "js:function(dateText,inst){
								jQuery.post('$url',JSON.stringify({dbdate:dateText,id:$(this).attr('id').substr(8)}));
							}",
						),
					));
					?>
					<?=Tools::hint($this->hints['Zakaz_part_time'], 'hint-block __part_time')?>
				</div>
                <div class="title-name panel-title">
					<?php 
					if ($data['status_id']==2){
						$class = 'bg-green';
						$disabled = '';
					} else {
						$class = 'bg-gray';
						$disabled = ' disabled';
					}
					?>
					<button id="stage-<?=$data['id']?>-approve-button" class="btn instant-send-buttons <?=$class?>"<?=$disabled?> title="<?=ProjectModule::t('Approve')?>" onclick="stage_change_status(<?=$data['id']?>,3); return false;"><img src="<?=Yii::app()->theme->baseUrl?>\images\ok.png"></button>
					<a data-toggle="collapse" data-parent="#accordion"
					   href="#collapseOne<?php echo $data['id']; ?>" id="part_title_<?=$data['id']?>">
					</a>
					<input type="text" value="<?php echo $data['title']; ?>" onkeyup="change_title(this.value,<?=$data['id']?>);"/>
					<?=Tools::hint($this->hints['Zakaz_part_title'], 'hint-block __part_title')?>
					<?=Tools::hint($this->hints['Zakaz_part_delete'], 'hint-block __part_delete float-right')?>
					<div class="btn instant-send-buttons bg-red float-right deletePart" onclick="delete_part(<?php echo $data['id']; ?>);" title="<?=ProjectModule::t('Remove stage')?>"><img src="<?=Yii::app()->theme->baseUrl?>\images\del.png"></div>
					
                </div>
            </div>
            <div id="collapseOne<?=$data['id'];?>" class="panel-collapse collapse">
                <div class="panel-body">

                    <p>
                        <textarea onkeyup="change_comment(this.value,<?php echo $data['id']; ?>);" class="col-xs-12"><?php echo $data['comment']; ?></textarea>

                        <?php 
						$tmp = '';
						foreach ($data['files'] as $k => $v){
                            $tmp .= '<li>';
                            $tmp .= '<button class="zakaz_part_approve_file on right btn instant-send-buttons bg-green'.(($v['approved'])?' hidden':'').'" ';
							$tmp .= 'data-id="'.$v['id'].'" ';
                            $tmp .= ' onclick="stageFileApprove(this)"><img src="'.Yii::app()->theme->baseUrl.'\images\ok.png" title="'.ProjectModule::t('Approve').'"></button>';
                            $tmp .= '<button class="zakaz_part_approve_file off right btn instant-send-buttons bg-gray'.(!($v['approved'])?' hidden':'').'" ';
							$tmp .= 'data-id="'.$v['id'].'" ';
                            $tmp .= ' onclick="stageFileApprove(this)"><img src="'.Yii::app()->theme->baseUrl.'\images\ok.png" title="'. Yii::t('site', 'Reset') .'"></button>';
                            $tmp .= '<span class="deletefile" style="color: #FF0000; display: inline; right: -10px; top: 10px; cursor: pointer;" id="'.$v['id'].'">x</span>';
							$tmp .= '<a target="_blank" href="'.ZakazPartsFiles::model()->folder(). $v['part_id'] . '/' . $v['file_name'] . '" id="parts_file">' . $v['orig_name'] . '</a></li>';
                        }

                        $this->widget('ext.EAjaxUpload.EAjaxUpload',
                            array(
                                'id' => 'EAjaxUpload'.$data['id'],
                                'config' => array(
                                    'action' => Yii::app()->createUrl('/project/zakazParts/upload?id='.$data['id']),
                                    'template' => '<div class="qq-uploader"><div class="qq-upload-drop-area"><ul class="qq-upload-list">'.$tmp.'</ul><span>'. ProjectModule::t('Drag and drop files here') .'</span><div class="qq-upload-button">'. ProjectModule::t('Upload material'). '</div></div></div>',
                                    'disAllowedExtensions' => array('exe','scr'),
                                    'sizeLimit' => Tools::maxFileSize(), //200 * 1024 * 1024,// maximum file size in bytes
                                    'minSizeLimit' => 1,// minimum file size in bytes
                                    'onComplete' => "js:function(id, fileName, responseJSON){
                                        $('.qq-upload-list').append(responseJSON.data.html);
                                    }"
                                )
                            )
                        ); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
