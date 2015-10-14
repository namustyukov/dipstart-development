<?php
/* @var $this SiteController */
/* @var $error array */

$this->pageTitle=Yii::app()->name . ' - Error';
$this->breadcrumbs=array(
	ProjectModule::t('Error'),
);
?>

<?php if(0):?>
<h2><?=ProjectModule::t('Error').' '.?><?php echo $code; ?></h2>
<?php endif; ?>

<div class="error">
<?php echo CHtml::encode($message); ?>
</div>