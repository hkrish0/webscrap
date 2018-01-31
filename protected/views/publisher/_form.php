<?php
/* @var $this PublisherController */
/* @var $model Publisher */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'publisher-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'id'); ?>
		<?php echo $form->textField($model,'id'); ?>
		<?php echo $form->error($model,'id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'publisher_name'); ?>
		<?php echo $form->textField($model,'publisher_name',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'publisher_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'function_name'); ?>
		<?php echo $form->textField($model,'function_name',array('size'=>50,'maxlength'=>50)); ?>
		<?php echo $form->error($model,'function_name'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'function_name_other'); ?>
		<?php echo $form->textField($model,'function_name_other',array('size'=>40,'maxlength'=>40)); ?>
		<?php echo $form->error($model,'function_name_other'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'url'); ?>
		<?php echo $form->textArea($model,'url',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'url'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uri'); ?>
		<?php echo $form->textArea($model,'uri',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'uri'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->