<?php
/* @var $this ScrapformController */
/* @var $model Scrapform */

$this->breadcrumbs=array(
	'Scrapforms'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Scrapform', 'url'=>array('index')),
	array('label'=>'Create Scrapform', 'url'=>array('create')),
	array('label'=>'View Scrapform', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Scrapform', 'url'=>array('admin')),
);
?>

<h1>Update Scrapform <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>