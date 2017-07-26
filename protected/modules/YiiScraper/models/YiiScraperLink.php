<?php

/**
 * This is the model class for the table "{{yiiscraper_link}}".
 *
 * Columns in table "{{yiiscraper_link}}" available as properties of the model,
 * followed by relations of table "{{yiiscraper_link}}" available as properties of the model.
 *
 * @property string $id
 * @property string $url
 * @property string $depth
 * @property integer $active
 * @property string $content
 *
 * @property YiiscraperRelatedData[] $yiiscraperRelatedDatas
 * @property YiiscraperRelatedData[] $yiiscraperRelatedDatas1
 */

class YiiScraperLink extends CActiveRecord
{
	public static function model($className=__CLASS__) {
		return parent::model($className);
	}
	
	/**
	 * Analog of parse_url() function for URL in utf-8 charset
	 * @param string $url URL to parse
	 * @return array parsed URL, false if parsing fails
	 */
	public static function parse_url_utf8($url) 
	{ 
		static $keys = array(
			'scheme' => 0,
			'user' => 0,
			'pass' => 0,
			'host' => 0,
			'port' => 0,
			'path' => 0,
			'query' => 0,
			'fragment' => 0
		); 
		if (is_string($url) && preg_match( 
				'~^((?P<scheme>[^:/?#]+):(//))?((\\3|//)?(?:(?P<user>[^:]+):(?P<pass>[^@]+)@)?(?P<host>[^/?:#]*))(:(?P<port>\\d+))?' . 
				'(?P<path>[^?#]*)(\\?(?P<query>[^#]*))?(#(?P<fragment>.*))?~u', $url, $matches)) 
		{ 
			foreach ($matches as $key => $value) 
				if (!isset($keys[$key]) || empty($value)) 
					unset($matches[$key]); 
			return $matches; 
		} 
		return false; 
	}
	
	/**
	 * Merges two arrays of parsed URLs to one URL
	 * @param array $currLinkInfo parsed current URL
	 * @param array $newLinkInfo  parsed new URL
	 * @param array $type what form to return: string or array
	 * @return array|string merged URL
	 */
	public static function mergeUrls($currLinkInfo, $newLinkInfo, $type = 'string')
	{
		if (!is_array($currLinkInfo) || !is_array($newLinkInfo))
			return false;
		
		$elements = array('scheme', 'user', 'pass', 'host');
		foreach($elements as $elem)
			if (empty($newLinkInfo[$elem]))
				$newLinkInfo[$elem] = (!empty($currLinkInfo[$elem])) ? $currLinkInfo[$elem] : '';
		
		if (
				   ((empty($currLinkInfo['scheme']) && empty($newLinkInfo['scheme'])) || $currLinkInfo['scheme'] == $newLinkInfo['scheme'])
				&& ((empty($currLinkInfo['user']) && empty($newLinkInfo['user'])) || $currLinkInfo['user'] == $newLinkInfo['user'])
				&& ((empty($currLinkInfo['pass']) && empty($newLinkInfo['pass'])) || $currLinkInfo['pass'] == $newLinkInfo['pass'])
				&& ((empty($currLinkInfo['host']) && empty($newLinkInfo['host'])) || $currLinkInfo['host'] == $newLinkInfo['host']))
		{
			if (empty($newLinkInfo['path']))
				$newLinkInfo['path'] = (!empty($currLinkInfo['path'])) ? $currLinkInfo['path'] : '';
			elseif ($newLinkInfo['path'][0] != '/')
			{
				$lastSlash = strrpos($currLinkInfo['path'], '/');
				$newLinkInfo['path'] = substr($currLinkInfo['path'], 0, $lastSlash + 1) . $newLinkInfo['path'];
			}
		}
		
		if ($type == 'array')
			return $newLinkInfo;
		
		$link = (!empty($newLinkInfo['scheme']) ? $newLinkInfo['scheme'] . '://' : '') . 
						(!empty($newLinkInfo['user']) && !empty($newLinkInfo['pass']) ? 
							$newLinkInfo['user'] . ':' . $newLinkInfo['pass']  . '@' : '') . 
						(!empty($newLinkInfo['host']) ? $newLinkInfo['host'] : '') . 
						(!empty($newLinkInfo['path']) ? $newLinkInfo['path'] : '') . 
						(!empty($newLinkInfo['query']) ? '?' . $newLinkInfo['query'] : '') . 
						(!empty($newLinkInfo['fragment']) ? '#' . $newLinkInfo['fragment'] : ''); 

		return $link;
	}
	
	/**
	 * Defines original content charset and converts it to utf-8
	 * @param string $content received content
	 * @return string content, converted to utf-8 charset
	 */
	public static function convertToUTF($content)
	{
		// first of all, define original charset...
		$accept = array(
			'type' => array('application/rss+xml', 'application/xml', 'application/rdf+xml', 'text/xml', 'text/html'),
			'charset' => array_diff(mb_list_encodings(), array('pass', 'auto', 'wchar', 'byte2be', 'byte2le', 'byte4be', 'byte4le', 'BASE64', 'UUENCODE', 'HTML-ENTITIES', 'Quoted-Printable', '7bit', '8bit'))
		);
		$header = array(
			'Accept: '.implode(', ', $accept['type']),
			'Accept-Charset: '.implode(', ', $accept['charset']),
		);
		$encoding = null;
		$offset = strpos($content, "\r\n\r\n");
		$header = substr($content, 0, $offset);
	
		if ($header && preg_match('/^Content-Type:\s+([^;]+)(?:;\s*charset=(.*?))?\s*$/im', $header, $match)) 
		{
			if (in_array(strtolower($match[1]), array_map('strtolower', $accept['type'])) && isset($match[2]))
				$encoding = trim($match[2], '"\'');
		}
		$encoding = null;
		if (!$encoding) 
		{
			$body = substr($content, $offset + 4);
			if (preg_match('/^<\?xml\s+version=(?:"[^"]*"|\'[^\']*\')\s+encoding=("[^"]*"|\'[^\']*\')/s', $body, $match)
					|| preg_match('/^<meta http-equiv="content-type".+content=".*charset=([^"]*)"/m', $body, $match))
				$encoding = trim($match[1], '"\'');
		}
		if (!$encoding)
			$encoding = 'utf-8';
		else 
		{
			if (in_array($encoding, array_map('strtolower', $accept['charset'])) && $encoding != 'utf-8')
				$content = mb_convert_encoding($content, 'utf-8', $encoding);
		}
		return $content;
	}
	
	public function tableName() {
		return '{{yiiscraper_link}}';
	}

	public function rules() {
		return array(
//			array('parent_id', 'numeric', 'min'=>0),
			array('url', 'required'),
			array('active', 'numerical', 'integerOnly'=>true),
			array('url', 'length', 'max'=>255),
			array('depth', 'length', 'max'=>11),
			array('content', 'safe'),
			array('depth, active, content', 'default', 'setOnEmpty' => true, 'value' => null),
			array('id, url, depth, active, content', 'safe', 'on'=>'search'),
		);
	}

	public function relations() {
		return array(
			'yiiscraperRelatedDatas' => array(self::HAS_MANY, 'YiiscraperRelatedData', 'link_id_from'),
			'yiiscraperRelatedDatas1' => array(self::HAS_MANY, 'YiiscraperRelatedData', 'link_id_to'),
		);
	}

	public function attributeLabels() {
		return array(
			'id' => Yii::t('app', 'ID'),
			'parent_id' => Yii::t('app', 'Parent ID'),
			'url' => Yii::t('app', 'Url'),
			'depth' => Yii::t('app', 'Depth'),
			'active' => Yii::t('app', 'Active'),
			'content' => Yii::t('app', 'Content'),
			'yiiscraperRelatedDatas' => null,
			'yiiscraperRelatedDatas1' => null,
		);
	}

	public function search() {
		$criteria = new CDbCriteria;

		$criteria->compare('id', $this->id, true);
//		$criteria->compare('parent_id', $this->parent_id, true);
		$criteria->compare('url', $this->url, true);
		$criteria->compare('depth', $this->depth, true);
		$criteria->compare('active', $this->active);
		$criteria->compare('content', $this->content, true);

		return new CActiveDataProvider($this, array(
			'criteria' => $criteria,
		));
	}
}