<?php

/**
 * This is the model class for table "publisher".
 *
 * The followings are the available columns in table 'publisher':
 * @property integer $id
 * @property string $publisher_name
 * @property string $function_name
 * @property string $function_name_other
 * @property string $url
 * @property string $uri
 */
class Publisher extends CActiveRecord
{
	/**
	 * @return string the associated database table name
	 */
	public function tableName()
	{
		return 'publisher';
	}

	/**
	 * @return array validation rules for model attributes.
	 */
	public function rules()
	{
		// NOTE: you should only define rules for those attributes that
		// will receive user inputs.
		return array(
			array('id, publisher_name, function_name, function_name_other', 'required'),
			array('id', 'numerical', 'integerOnly'=>true),
			array('publisher_name', 'length', 'max'=>100),
			array('function_name', 'length', 'max'=>50),
			array('function_name_other', 'length', 'max'=>40),
			array('url, uri', 'safe'),
			// The following rule is used by search().
			// @todo Please remove those attributes that should not be searched.
			array('id, publisher_name, function_name, function_name_other, url, uri', 'safe', 'on'=>'search'),
		);
	}

	/**
	 * @return array relational rules.
	 */
	public function relations()
	{
		// NOTE: you may need to adjust the relation name and the related
		// class name for the relations automatically generated below.
		return array(
		);
	}

	/**
	 * @return array customized attribute labels (name=>label)
	 */
	public function attributeLabels()
	{
		return array(
			'id' => 'ID',
			'publisher_name' => 'Publisher Name',
			'function_name' => 'Function Name',
			'function_name_other' => 'Function Name Other',
			'url' => 'Url',
			'uri' => 'Uri',
		);
	}

	/**
	 * Retrieves a list of models based on the current search/filter conditions.
	 *
	 * Typical usecase:
	 * - Initialize the model fields with values from filter form.
	 * - Execute this method to get CActiveDataProvider instance which will filter
	 * models according to data in model fields.
	 * - Pass data provider to CGridView, CListView or any similar widget.
	 *
	 * @return CActiveDataProvider the data provider that can return the models
	 * based on the search/filter conditions.
	 */
	public function search()
	{
		// @todo Please modify the following code to remove attributes that should not be searched.

		$criteria=new CDbCriteria;

		$criteria->compare('id',$this->id);
		$criteria->compare('publisher_name',$this->publisher_name,true);
		$criteria->compare('function_name',$this->function_name,true);
		$criteria->compare('function_name_other',$this->function_name_other,true);
		$criteria->compare('url',$this->url,true);
		$criteria->compare('uri',$this->uri,true);

		return new CActiveDataProvider($this, array(
			'criteria'=>$criteria,
		));
	}

	/**
	 * Returns the static model of the specified AR class.
	 * Please note that you should have this exact method in all your CActiveRecord descendants!
	 * @param string $className active record class name.
	 * @return Publisher the static model class
	 */
	public static function model($className=__CLASS__)
	{
		return parent::model($className);
	}
}
