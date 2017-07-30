<?php
/* @var $this ScrapformController */
/* @var $model Scrapform */

$this->breadcrumbs=array(
	'Scrapforms'=>array('index'),
	$model->id,
);

$this->menu=array(
	array('label'=>'List Scrapform', 'url'=>array('index')),
	array('label'=>'Create Scrapform', 'url'=>array('create')),
	array('label'=>'Update Scrapform', 'url'=>array('update', 'id'=>$model->id)),
	array('label'=>'Delete Scrapform', 'url'=>'#', 'linkOptions'=>array('submit'=>array('delete','id'=>$model->id),'confirm'=>'Are you sure you want to delete this item?')),
	array('label'=>'Manage Scrapform', 'url'=>array('admin')),
);
?>

<h1>View Scrapform #<?php echo $model->id; ?></h1>

<?php $this->widget('zii.widgets.CDetailView', array(
	'data'=>$model,
	'attributes'=>array(
		'id',
		'publisher_id',
		'url',
		'uri',
		'attribute',
	),
)); ?>
