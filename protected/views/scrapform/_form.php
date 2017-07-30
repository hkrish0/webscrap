<?php
/* @var $this ScrapformController */
/* @var $model Scrapform */
/* @var $form CActiveForm */
?>

<div class="form">

<?php $form=$this->beginWidget('CActiveForm', array(
	'id'=>'scrapform-form',
	// Please note: When you enable ajax validation, make sure the corresponding
	// controller action is handling ajax validation correctly.
	// There is a call to performAjaxValidation() commented in generated controller code.
	// See class documentation of CActiveForm for details on this.
	'enableAjaxValidation'=>false,
)); ?>

	<p class="note">Fields with <span class="required">*</span> are required.</p>

	<?php echo $form->errorSummary($model); ?>

	<div class="row">
		<?php echo $form->labelEx($model,'publisher_id'); ?>
		<?php echo $form->dropDownList($model, 'publisher_id', CHtml::listData(Publisher::model()->findAll(), 'id', 'publisher_name'));?>
		<?php echo $form->error($model,'publisher_id'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'url'); ?>
		<?php echo $form->textArea($model,'url',array('rows'=>6, 'cols'=>50)); ?>
		<?php echo $form->error($model,'url'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'uri'); ?>
		<?php echo $form->textField($model,'uri',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'uri'); ?>
	</div>

	<div class="row">
		<?php echo $form->labelEx($model,'attribute'); ?>
		<?php echo $form->textField($model,'attribute',array('size'=>60,'maxlength'=>100)); ?>
		<?php echo $form->error($model,'attribute'); ?>
	</div>

	<div class="row buttons">
		<?php echo CHtml::submitButton($model->isNewRecord ? 'Create' : 'Save'); ?>
	</div>

<?php $this->endWidget(); ?>

</div><!-- form -->