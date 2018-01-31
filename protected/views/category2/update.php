<?php
/* @var $this Category2Controller */
/* @var $model CategoryDetails */

$this->breadcrumbs=array(
	'Category Details'=>array('index'),
	$model->id=>array('view','id'=>$model->id),
	'Update',
);

$this->menu=array(
	array('label'=>'List CategoryDetails', 'url'=>array('index')),
	array('label'=>'Create CategoryDetails', 'url'=>array('create')),
	array('label'=>'View CategoryDetails', 'url'=>array('view', 'id'=>$model->id)),
	array('label'=>'Manage CategoryDetails', 'url'=>array('admin')),
);
?>

<h1>Update CategoryDetails <?php echo $model->id; ?></h1>

<?php $this->renderPartial('_form', array('model'=>$model)); ?>