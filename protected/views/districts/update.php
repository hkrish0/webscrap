<?php
/* @var $this DistrictsController */
/* @var $model Districts */

$this->breadcrumbs=array(
	'Districts'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List Districts', 'url'=>array('index')),
	array('label'=>'Create Districts', 'url'=>array('create')),
	array('label'=>'View Districts', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage Districts', 'url'=>array('admin')),
);
?>

<h1>Update Districts <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>