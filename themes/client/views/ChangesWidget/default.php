<?php
/* @var $this CWidget */
/* @var $user User */
/* @var $changes ProjectChanges */
?>

<div class="row zero-edge">
    <div class="panel-group" id="accordion">
        <div class="panel panel-default">
            <div class="panel-heading">
                <div class="partStatus"></div>
                <h4 class="panel-title">
                    <a data-toggle="collapse" data-parent="#accordion"
                       href="#collapseEditChanges"><?php echo ProjectModule::t('Changes'); ?></a>
                </h4>
            </div>

            <div id="collapseEditChanges" class="panel-collapse collapse in">
                <div class="panel-body">
                    <div id="list_files">
                    <?php
                    $this->widget('zii.widgets.CListView', array(
                        'dataProvider' => $changes,
                        'itemView' => '_list',
                        'summaryCssClass' => 'hidden',
                        'emptyText' => '',
                        'enablePagination' => false,
                    ));?>
                    </div>
                    <?php
                    if ($user->isCustomer() || $isCorrector) { ?>
                        <div class="form" id="new-changes-block">
							<div id="new-changes-link"><a data-toggle="collapse" data-parent="#new-changes-block" href="#new-changes-collapse"><?=ProjectModule::t('The new revision')?></a></div>
							<div id="new-changes-collapse" class="collapse">
								<div id="errors-block"></div>
								<?php
								echo CHtml::form(Yii::app()->createUrl('/project/changes/add?ctr=' . $project->id), 'post', array('id' => 'up_file', 'enctype' => 'multipart/form-data'));
								?>
								<div class="row">
									<?php echo CHtml::label(ProjectModule::t('Attach file'), 'fileupload'); ?>
									<?php echo CHtml::fileField('ProjectChanges[fileupload]', $fileupload, array('class' => 'col-xs-12 btn btn-user')); ?>
								</div>

								<div class="row">
									<?php echo CHtml::label(ProjectModule::t('Comment'), 'comment'); ?>
									<?php echo CHtml::textArea('ProjectChanges[comment]', $comment, array('class' => 'col-xs-12')); ?>
								</div>

								<?php if (ProjectChanges::approveAllowed()) { ?>
									<div class="row">
										<label for="ProjectChanges_moderate"><?=ProjectModule::t('Moderation')?></label>
										<?php echo CHtml::dropDownList($changes,
											'moderate',
											array('1' => ProjectModule::t('Approved'), '0' => ProjectModule::t('Not approved')),
											array('style' => ''));
										?>
									</div>
								<?php } ?>
								<div class="row buttons">
									<?php
									echo CHtml::htmlButton(ProjectModule::t('Add changes'), array(
										'class' => 'col-xs-12 btn btn-user addPart',
										'onclick' => 'javascript: send();',
										'id' => 'post-submit-btn',
									)); ?>
								</div>

								<?php echo CHtml::endForm(); ?>
								<script type="text/javascript">
									function send() {
										var formData = new FormData($("#up_file")[0]);
										$.ajax({
											url: '<?php echo Yii::app()->createUrl("/project/changes/add",array('project'=>$project->id)); ?>',
											type: 'POST',
											data: formData,
											datatype: 'json',
											success: function (data) {
												if(data.error) alert(JSON.stringify(data.error));
												jQuery("#list_files").load("<?php echo Yii::app()->createUrl("/project/changes/list",array('project'=>$project->id)); ?>");
											},
											cache: false,
											contentType: false,
											processData: false
										});

										return false;
									}
								</script>
							</div>
                        </div><!-- form -->
                    <?php
                    }
                    ?>        </div>
            </div>
        </div>
    </div>
</div>
