<div id="list-changes-block" class="list-changes-block">
	<button class="btn change-approve instant-send-buttons<?=($data['moderate'])?' bg-gray':' bg-green'?>" onclick="<?='changes_approve('.$data['id'].',this)'?>" data-moderate="<?=$data['moderate']?>" data-s0="<?=ProjectModule::t('Not approved')?>" data-s1="<?=ProjectModule::t('Approved')?>" title="<?=($data['moderate'])?ProjectModule::t('Approved'):ProjectModule::t('Not approved')?>"><img src="<?=Yii::app()->theme->baseUrl?>\images\ok.png"></button>
    <div class="list-changes-filename">
        <a href="<?php echo $data['file'];?>"><?php echo $data['filename']; ?></a>
    </div>
	<button class="btn instant-send-buttons bg-red float-right" onclick="<?='changes_delete('.$data['id'].',this)'?>" title="<?=ProjectModule::t('Delete revision')?>"><img src="<?=Yii::app()->theme->baseUrl?>\images\del.png"></button>
    <div class="list-changes-comment">
        <?php echo $data['comment']; ?>
    </div>
</div>