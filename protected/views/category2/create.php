<?php
/* @var $this Category2Controller */
/* @var $model CategoryDetails */

$this->breadcrumbs=array(
	'Category Details'=>array('index'),
	'Create',
);

$this->menu=array(
	array('label'=>'List CategoryDetails', 'url'=>array('index')),
	array('label'=>'Manage CategoryDetails', 'url'=>array('admin')),
);
?>

<h1>Create CategoryDetails</h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>