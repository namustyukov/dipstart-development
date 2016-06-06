<?php
/* @var $this CrmCdrController */
/* @var $dataProvider CActiveDataProvider */

$this->breadcrumbs=array(
	'Crm Cdrs',
);
?>

<h1>Список звонков</h1>
<?php echo CHtml::button(
        'Обновить данные',
        ['submit'=>['/call/refresh']]
        );
?>
<br>
<?php /*$this->widget('application.widgets.CallBtn', array(
	'to'=>'2203*03',
));*/
?>

<?php $this->widget('zii.widgets.grid.CGridView', array(
	'dataProvider'=>$dataProvider,
    'columns'=>array(
        'published',
        'publishedDate',
        'publishedTime',
        'flow',
        'source',
        'destination',
        'result',
        'duration',
        'answerDuration',
    ),    
)); ?>

<b>LOCAL</b> = локальный звонок<br>
<b>OUT</b> = исходящий внешний звонок<br>
<b>IN</b> = входящий внешний звонок<br>
<br>
<b>ANSWERED</b> = звонки, на которые ответили<br>
<b>BUSY</b> = звонки, при которых вызываемый номер был занят<br>
<b>NO_ANSWER</b> = звонки, на которые не был получен ответ<br>
<b>FAILED</b> = неудачные звонки. Абонент не подключен к сети, неизвестный номер и прочие сбои.<br>
<b>UNKNOWN</b> = неизвестные звонки. Обычно означают что что-то пошло не так. Как правило проблемы на стороне оператора (Телфин).<br>
<b>NOT_ALLOWED</b> = звонки, выполнение которых не было разрешено. Не разрешено оператором (Телфином). Например нет денег, нет прав на это направление и т..<br>
<br><br>
<b>Время ожидания ответа (сек)</b> = время прошедшее с момента начала звонка и до момента ответа на звонок.<br>
<b>NULL</b> в поле длительности ответа означает, что для данного результата звонка данное поле неприменимо.
Например если на звонок не ответили, то время ответа неизвестно (не имеет смысла).