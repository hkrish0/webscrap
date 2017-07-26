<?php

/**
 * This is the model class for the table "{{yiiscraper_log}}".
 * Columns in table "{{yiiscraper_log}}" available as properties of the model,
 * and there are no model relations.
 *
 * @property string $id
 * @property string $created
 * @property string $bytes_received
 * @property string $documents_received
 * @property string $html_documents_received
 * @property string $created_links_count
 * @property string $duration
 * @property string $result
 * @property string $details
 *
 */

class YiiScraperLog extends CActiveRecord {

	public static function model($className=__CLASS__) {
		return parent::model($className);
	}

	public function tableName() {
		return '{{yiiscraper_log}}';
	}

	public function rules() {
		return array(
			array('created, result', 'required'),
			array('bytes_received, documents_received, html_documents_received, created_links_count, duration', 'length', 'max'=>11),
			array('result', 'length', 'max'=>250),
			array('details', 'safe'),
			array('bytes_received, documents_received, html_documents_received, created_links_count, duration, details', 'default', 'setOnEmpty' => true, 'value' => null),
			array('id, created, bytes_received, documents_received, html_documents_received, created_links_count, duration, result, details', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		return array(
		);
	}

	public function attributeLabels() {
		return array(
			'id' => Yii::t('app', 'ID'),
			'created' => Yii::t('app', 'Created'),
			'bytes_received' => Yii::t('app', 'Bytes Received'),
			'documents_received' => Yii::t('app', 'Documents Received'),
			'html_documents_received' => Yii::t('app', 'Html Documents Received'),
			'created_links_count' => Yii::t('app', 'Created Links Count'),
			'updated_documents_count' => Yii::t('app', 'Updated Documents Count'),
			'duration' => Yii::t('app', 'Duration'),
			'result' => Yii::t('app', 'Result'),
			'details' => Yii::t('app', 'Details'),
		);
	}

	public function search() {
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
		$criteria->compare('created', $this->created, true);
		$criteria->compare('bytes_received', $this->bytes_received, true);
		$criteria->compare('documents_received', $this->documents_received, true);
		$criteria->compare('html_documents_received', $this->html_documents_received, true);
		$criteria->compare('created_links_count', $this->created_links_count, true);
		$criteria->compare('updated_documents_count', $this->updated_documents_count, true);
		$criteria->compare('duration', $this->duration, true);
		$criteria->compare('result', $this->result, true);
		$criteria->compare('details', $this->details, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}