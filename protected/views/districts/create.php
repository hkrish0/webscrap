<?php
/* @var $this DistrictsController */
/* @var $model Districts */

$this->breadcrumbs=array(
	'Districts'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List Districts', 'url'=>array('index')),
	array('label'=>'Manage Districts', 'url'=>array('admin')),
);
?>

<h1>Create Districts</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>