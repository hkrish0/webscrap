<?php
/**
 * YiiScraperModule class file.
 *
 * @author Vitalii Tron <vittron85@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2013 Vitalii Tron
 * @license MIT license
 */

/**
 * YiiScraperModule is base scraper module to get information from Internet.
 * It parses received HTML pages using simple_html_dom parser component and retrieves URLs 
 * from HTML page. URLs are saved to DB table and will be requested later. By default, 
 * HTML content is saved to DB table as well. You may select target containers for parsed URLs
 * and stored content. You may write your own functions for URLs parsing and storing content.
 * 
 * NOTE: you need to install CURL and MBSTRING PHP extensions to use this module.
 * 
 * Module is designed to run from cron as well. You can use it for periodical scraping. If DB table for links is 
 * empty, it is filled with seeds. Each URL is fetched then, marked as non-active and requested. Received HTML 
 * content is parsed then and new URLs are saved to the same table. Scraper work is terminated, when anyone of 
 * limits is exhausted. You can see respective message in scraper logs table. If scraper work is terminated, 
 * scraper may start later and request first active URL, so its work is proceeded. If there are no one active URL 
 * in the table, scraper stops its work. When scraper runs next time, it cleans DB table for URLs, inserts seeds 
 * there and process will be repeated from the very beginning.
 * 
 * To use module, please, copy it to '.../protected/modules/' folder. Then add lines to your 
 * '.../protected/config/main.php' file, to 'modules' part:
 * 
 * ...
 * 'modules'=>array(
 *		'gii'=>array(	... 	),
 *		'yscraper' => array(
 *			'class' => 'application.modules.YiiScraper.YiiScraperModule',
 *			'installMode' => true,
 *			'seeds' => 'http://pravda.com.ua',
 *			'insideUrlsOnly' => 'pravda.com.ua',
 *			'maxDuration' => 1000,
 *			'contentSelector' => 'div#content',
 *		),
 *	),
 * ...
 * 
 * Note, that 'installMode' option must be true. Then you need to install scraper. Please, open this link on 
 * your machine:
 * http://your.domainname.com/index.php?r=yscraper/default/install
 * or for uninstall
 * http://your.domainname.com/index.php?r=yscraper/default/uninstall
 * Then you need to remove line 
 * 
 * 'installMode' => true, 
 * 
 * from config, adjust all other settings and run scraper with command:
 * 
 * Yii::app()->getModule('yscraper')->run();
 * 
 * If you would like to use callbacks, you may use static methods from previously imported classes in your 
 * config file:
 * 
 * 'modules'=>array(
 *		'yscraper' => array(
 *			...
 *			'contentCallback' => 'SomeModelClass::someCallbackFunction',
 *		),
 *	),
 * ...
 * 
 * Or you may set callbacks during runtime in your controller file:
 * 
 * 	public function actionIndex()
 *	{
 *		...
 *		Yii::app()->getModule('yscraper')->contentCallback = array($this, 'someCallbackFunction');
 *		Yii::app()->getModule('yscraper')->run();
 *		...
 *	}
 * 
 * ...
 * 
 *	public function someCallbackFunction($currentURL, $content)
 *	{
 *		// process content here
 *	}
 *	
 * Note, that {@link linkCallback} and {@link contentCallback} functions get two arguments: URL and content, 
 * received from that URL. And note, that {@link linkCallback} function should return array of URLs to 
 * scrape later.
 * 
 * @property boolean $installMode checks whether module is in install mode. It helps to avoid incidental 
 * (un)install of module.
 * @property array|string $seeds start point(s) for scraping.
 * @property array|string $insideUrlsOnly scrape only inside url(s), if specified. All outside urls 
 * will be ignored.
 * @property array|string $includeRedirections whether add redirections to {@link $includeUrlsOnly}
 * array. It is not used, if {@link insideUrlsOnly} value is empty.
 * @property float $timeInterval sets time interval between HTTP requests, to avoid scraped server 
 * overload and blocking of your IP. By default, disabled.
 * @property integer $maxDuration if positive, sets time limit in seconds for scraping. If '0', this option 
 * is disabled and scraper will run until it scrapes all target links. By default, 1800 seconds.
 * @property integer $maxBytesReceived if positive, sets limit in bytes for received data volume. By default, 
 * disabled.
 * @property integer $maxDocumentsReceived if positive, sets limit for received documents. By default, limit 
 * is disabled.
 * @property integer $maxHtmlDocumentsReceived if positive, sets limit for received HTML documents. By default, 
 * limit is disabled.
 * @property boolean $scrapeInDepth defines scrape strategy: scrape either in depth, or broadways. 
 * By default, scrapes broadways.
 * @property integer $maxDepth if positive, sets how deep from seed scraper may scrape and get pages. By default, 
 * depth is unlimited.
 * @property string $linkSelector defines CSS selector for target links. All links will be scraped, if empty. 
 * If {@link $linkCallback} is not empty, this value will be ignored.
 * @property string $contentSelector defines CSS selector for target content block(s) to store to DB table. 
 * Whole content will be stored, if empty. If {@link $contentCallback} is not empty, this value will be ignored.
 * @property string $linkCallback defines user custom function to parse links in received HTML. Custom 
 * function must return array of links. If not empty, {@link $linkSelector} value is ignored.
 * @property string $contentCallback defines user custom function to find target content in HTML and store it.
 * If not empty, {@link $contentSelector} value is ignored.
 * 
 * @author Vitalii Tron <vittron85@gmail.com>
 * @link http://www.yiiframework.com/
 * @copyright Copyright &copy; 2012 Vitalii Tron
 * @license New BSD License
 * @version 0.8 $Id: YiiScraperModule.php 1 2013-01-05 12:29:24Z vittron $
 * @package application.modules
 * @since 1.1.12
 * @access public	
 */
class YiiScraperModule extends CWebModule
{
	public $installMode = false;
	
	public $seeds;
	
	public $insideUrlsOnly = array();
	
	public $includeRedirections = true;
	
	public $timeInterval = 0;
	
	public $maxDuration = 1800; // 30 minutes
	
	public $maxBytesReceived = 0;
	
	public $maxDocumentsReceived = 0;
	
	public $maxHtmlDocumentsReceived = 0;
	
	public $scrapeInDepth = false;
	
	public $maxDepth = 0;
	
	public $linkSelector;
	
	public $contentSelector;
	
	public $linkCallback;
	
	public $contentCallback;
	
	protected $log;										// logging component
	
	protected $currLink;								// current scraped link component
	
	protected $startTime;								// when scraper starts
	
	protected $curl;									// CURL component
	
	/**
	 * Initialization
	 */
	public function init()
	{
		parent::init();
		
		// import the module-level models and components
		Yii::setPathOfAlias('YiiScraper',Yii::app()->getModulePath() . DIRECTORY_SEPARATOR . 'YiiScraper');
		$this->setImport(array(
			'YiiScraper.models.*',
			'YiiScraper.components.*',
		));
		
		if ($this->installMode)
			return true;
		
		if (empty($this->seeds))
			throw new CException(Yii::t(get_class($this), 'Please, define seeds (start URLs).'));
		else if (is_string($this->seeds))
			$this->seeds = preg_split('#\s*,\s*#', $this->seeds, 0, PREG_SPLIT_NO_EMPTY);
		else if (!is_array($this->seeds))
			throw new CException(Yii::t(get_class($this), 'Seeds must be string or array of strings.'));
		
		if (is_string($this->insideUrlsOnly))
			$this->insideUrlsOnly = preg_split('#\s*,\s*#', $this->insideUrlsOnly, 0, PREG_SPLIT_NO_EMPTY);
		else if (!is_array($this->insideUrlsOnly))
			throw new CException(Yii::t(get_class($this), 'InsideUrlsOnly property must be string or array of strings.'));
		
		// normalize URLs here
		foreach($this->insideUrlsOnly as $key => $link)
		{
			if (mb_strpos($link, '?') === false)
			{
				$lastSlash = mb_strrpos($link, '/');
				if ($lastSlash !== mb_strlen($link) - 1)
					$this->insideUrlsOnly[$key] .= '/';
			}
			if (mb_strpos($link, '://') === false)
				$this->insideUrlsOnly[$key] = 'http://' . $link;
		}
		// convert it to microseconds because of 'usleep()' function used in code, for better precision
		$this->timeInterval = $this->timeInterval * 1000000;
		// Yii::trace("Initialized scraper properties: <pre>" . var_export(get_object_vars($this), true) . "</pre>");
		
		if (!is_int($this->maxDuration))
			throw new CException(Yii::t(get_class($this), 'maxDuration must be integer.'));
		if ($this->maxDuration === 0)
			Yii::log(Yii::t(get_class($this), 'Max duration is 0, what may cause too long scraping process'), 'warning');
		
		if (empty($this->linkSelector) && empty($this->linkCallback))
			$this->linkSelector = 'a'; // all links in content
		if (empty($this->contentSelector) && empty($this->contentCallback))
			$this->contentSelector = '*'; // all content
		
		ini_set('mbstring.internal_encoding', 'UTF-8');
		
		$this->curl = curl_init();
		curl_setopt ($this->curl, CURLOPT_HEADER, 1);
		curl_setopt ($this->curl, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt ($this->curl, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($this->curl, CURLOPT_USERAGENT,'Mozilla/5.0 (Windows NT 5.1; rv:2.0.1) Gecko/20100101 Firefox/4.0.1');
		curl_setopt ($this->curl, CURLOPT_ENCODING, 'gzip,deflate');
		curl_setopt ($this->curl, CURLOPT_COOKIESESSION, 1);
		curl_setopt ($this->curl, CURLOPT_TIMEOUT, 30);
		curl_setopt ($this->curl, CURLOPT_ENCODING, 1);
		curl_setopt ($this->curl, CURLOPT_COOKIEJAR,  Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'cookies.txt');
		curl_setopt ($this->curl, CURLOPT_COOKIEFILE, Yii::app()->getRuntimePath() . DIRECTORY_SEPARATOR . 'cookies.txt');
		curl_setopt ($this->curl, CURLOPT_HTTPHEADER, array(
			"Accept: text/xml,application/xml,application/xhtml+xml,text/html;q=0.9,text/plain;q=0.8,image/png,*/*;q=0.5",
			"Connection: keep-alive",
			"Keep-Alive: 115",
			"Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7",
			"Accept-Language: en-us,en;q=0.5",
			"Pragma: ",
		));
		
		$this->log = new YiiScraperLog;
		$this->log->created = date('Y-m-d H:i:s');
		$this->log->bytes_received = 
			$this->log->documents_received = 
			$this->log->html_documents_received = 
			$this->log->created_links_count = 
			$this->log->duration = 0;
		$this->log->result = 'Error: Abnormal termination';
		$this->log->details = '';
		if (!$this->log->save())
			Yii::trace("Error during log initialization!");
	}

	public function beforeControllerAction($controller, $action)
	{
		if(parent::beforeControllerAction($controller, $action))
		{
			// this method is called before any module controller action is performed
			// you may place customized code here
			return true;
		}
		else
			return false;
	}
	
	/**
	 * Installs module
	 */
	public function install()
	{
		Installer::install($this->installMode);
	}
	
	/**
	 * @api
	 * Main function to run scraping process
	 */
	public function run()
	{
		if ($this->installMode)
			throw new CException(Yii::t('yiiscraper', 'Please, remove or comment module option "\'installMode\' => true" from your config file'));

		$this->start();
		$this->saveSeeds();
		
		while ($this->getNextLink())
			$this->processLink();
		
		$this->end();
	}
	
	/**
	 * Procedures before scraping process start
	 */
	public function start()
	{
		$this->startTime = time();
		include_once('simple_html_dom.php');
		set_time_limit(60 * 60 * 24); // one day
	}
	
	/**
	 * Procedures after scraping process finish
	 */
	public function end()
	{
		curl_close($this->curl);
		if (!$this->isReachedLimit())
			$this->log->result = 'OK: finished';
			
		$this->log->save();
	}
	
	/**
	 * Saves seeds, if there are no active links in DB table
	 */
	public function saveSeeds()
	{
		$db = Yii::app()->db;
		
		$sql = 'Select count(*) from {{yiiscraper_link}} where active = 1';
		$activeCount = $db->createCommand($sql)->queryScalar();
		
		// if there are no active links, we begin next cycle of scraping from the very beginning
		// else previous cycle was interrupted and we proceed it
		if (!$activeCount)
		{
			$db->createCommand("set foreign_key_checks = 0")->execute();
			$sql = 'truncate table {{yiiscraper_related_data}}';
			$db->createCommand($sql)->execute();
			$sql = 'truncate table {{yiiscraper_link}}';
			$db->createCommand($sql)->execute();
			$db->createCommand("set foreign_key_checks = 1")->execute();
			$this->saveRows($this->filterLinks($this->seeds));
		}
	}
	
	/**
	 * Saves rows to DB table and info about related links as well
	 * @param array $links link to save
	 * @param string $table specified DB table
	 * @param boolean $insertIgnore whether use 'insert ignore' SQL construction
	 * @return boolean true, rows were saved, false otherwise
	 */
	public function saveRows($links, $table = 'yiiscraper_link', $insertIgnore = true)
	{
		$_recordKeys = array();
		$values = array();
		$db = Yii::app()->db;
		if (empty($links))
			return false;
		// implement maxDepth restriction here
		if ($this->maxDepth > 0 && isset($this->currLink->depth) && $this->currLink->depth >= $this->maxDepth)
			return false;
		
		if ($table == 'yiiscraper_link')
		{
			$criteria = new CDbCriteria();
			$criteria->index = 'url';
			$criteria->addInCondition('url', $links);
			$scraperLinks = YiiScraperLink::model()->findAll($criteria);
			$relatedData = array();
			foreach($links as $link)
			{
				$_link = is_array($link) ? $link['url'] : $link;
				if (empty($scraperLinks[$_link]))
				{
					$scraperLink = new YiiScraperLink;
					if (is_array($link))
						$scraperLink->attributes = $link;
					else
					{
						$scraperLink->url = $link;
						$scraperLink->depth = isset($this->currLink->depth) ? ($this->currLink->depth + 1) : 0;
						$scraperLink->active = 1;
					}
//					Yii::trace(var_export($scraperLink->attributes, true));
					if ($scraperLink->save())
						$this->log->created_links_count++;
					$scraperLinks[$_link] = $scraperLink;
				}
				else
					$scraperLink = $scraperLinks[$_link];
				if (isset($this->currLink) && isset($this->currLink->id) && isset($scraperLink->id))
					$relatedData[] = "(" . $this->currLink->id . ", " . $scraperLink->id . ')';
			}
			if (!empty($relatedData))
			{
				$sql = "insert ignore into {{yiiscraper_related_data}} (link_id_from, link_id_to) values " . 
						implode(', ', $relatedData);
				$db->createCommand($sql)->execute();
			}
			return true;
		}
		// built keys array
		$_recordKeys = array_keys($links);
		
		$quotedColumns = array();
		foreach($_recordKeys as $key => $value)
			$quotedColumns[$key] = $db->quoteColumnName($value);
		
		foreach($links as $link)
		{
			$row = array();
			foreach ($_recordKeys as $key)
				$row[$key] = isset($link[$key]) ? $db->quoteValue($link[$key]) : "NULL";
			
			$values[] = '(' . implode(', ', $row) . ')';
		}
		
		$sql = ($insertIgnore ? 'insert ignore' : 'insert') . " into {{" . $table . "}} " .
			'(' . implode(', ', $quotedColumns) . ') VALUES ' . implode(', ', $values);
		// echo $sql, "<br />";
		return $db->createCommand($sql)->execute();
	}
	
	/**
	 * Fetch new URL from DB table
	 * @return boolean true, if new URL is fetched, false otherwise
	 */
	public function getNextLink()
	{
		if ($this->isReachedLimit())
			return false;
			
		$db = Yii::app()->db;
		$type = $this->scrapeInDepth ? 'DESC' : '';
		$sql = 'select * from {{yiiscraper_link}} 
			where active = 1
			order by id ' . $type .
			' limit 1';
		$this->currLink = YiiScraperLink::model()->findBySql($sql);
		
		if (empty($this->currLink))
			return false;
		
		// echo "fetched ", $this->currLink->id, " ", $this->currLink->url, "<br />";
		$this->currLink->active = 0;
		$this->currLink->save();
		
		return true;
	}
	
	/**
	 * Checks whether any of limits is reached
	 * @param string $content received content
	 * @return boolean true, if any of limits is reached
	 */
	public function isReachedLimit($content = '')
	{
		$result = false;
		$this->log->duration = time() - $this->startTime;
		if (!empty($content))
		{
			$this->log->bytes_received += mb_strlen($content);
			++$this->log->documents_received;
			if (mb_strpos($content, 'Content-Type: text/html') !== false)
				++$this->log->html_documents_received;
		}
		if (($this->maxDuration > 0) && ($this->log->duration >= $this->maxDuration))
		{
			$this->log->result = 'Paused: duration limit exhausted';
			$result = true;
		}
		if (($this->maxBytesReceived > 0) && ($this->log->bytes_received >= $this->maxBytesReceived))
		{
			$this->log->result = 'Paused: received bytes limit exhausted';
			$result = true;
		}
		if (($this->maxDocumentsReceived > 0) && ($this->log->documents_received >= $this->maxDocumentsReceived))
		{
			$this->log->result = 'Paused: received documents limit exhausted';
			$result = true;
		}
		if (($this->maxHtmlDocumentsReceived > 0) && ($this->log->html_documents_received >= $this->maxHtmlDocumentsReceived))
		{
			$this->log->result = 'Paused: received HTML documents limit exhausted';
			$result = true;
		}
		// update row in DB table only if we have changes to $this->log
		if ($result || !empty($content))
			$this->log->save();
		
		return $result;
	}
	
	/**
	 * Receives content from URL, parses content and saves new URL to DB
	 * @return boolean false, if any limit is reached
	 */
	public function processLink()
	{
		$content = $this->makeRequest();
		$this->processLocation($content);
		$this->extractLinks($content);
		$this->saveContent($content);
		if ($this->isReachedLimit($content)) // just to increment log counters
			return false;
	}
	
	// processes redirections
	protected function processLocation($content)
	{
		$offset = strpos($content, "\r\n\r\n");
		$header = substr($content, 0, $offset);

		if (preg_match('@^\s*Location:\s*(\S+)@m', $header, $matches))
		{
			$newLinkInfo = YiiScraperLink::parse_url_utf8($matches[1]);
			$currLinkInfo = YiiScraperLink::parse_url_utf8($this->currLink->url);
			$link = YiiScraperLink::mergeUrls($currLinkInfo, $newLinkInfo);
			$temp = YiiScraperLink::model()->findByAttributes(array('url' => $link));
			if (empty($temp))
			{
				$this->currLink->url = $link;
				$this->currLink->save();
			}
			if ($this->includeRedirections 
					&& !empty($this->insideUrlsOnly)
					&& !in_array($link, $this->insideUrlsOnly))
				$this->insideUrlsOnly[] = $link;
		}
	}
	
	/**
	 * Call {@link linkCallback} function if specified or parses target elements, specified in 
	 * {@link linkSelector}. Then array of URLs are saved to DB.
	 * @param string $content received content
	 * @return callback result, if {@link contentCallback} specified
	 */
	public function extractLinks($content)
	{
		// is it wrong page or some error happend? 
		if (empty($content))
			return false;
		
		$allowedTags = array('a', 'area', 'base', 'link', 'img', 'frame', 'iframe', 'script', 'input');
		if (!empty($this->linkCallback))
		{
			$hrefs = call_user_func($this->linkCallback, $this->currLink->url, $content);
			if (!is_array($hrefs))
				$hrefs = array();
			$this->saveRows($hrefs);
		}
		elseif (!empty($this->linkSelector))
		{
			$dom = str_get_html($content);
			$links = $dom->find($this->linkSelector);
			$hrefs = array();
			foreach ($links as $link)
			{
				if (isset($link->href))
					$hrefs[] = $link->href;
				elseif (isset($link->src))
					$hrefs[] = $link->src;
				elseif (!in_array($link->tag, $allowedTags)) 
					throw new CException(Yii::t('yiiscraper', 'Please set correct selector for links. ' .
							'For example \'div#content a.active\''));
			}
			$this->saveRows($this->filterLinks($hrefs));
			unset($dom);
		}
	}
	
	/**
	 * Call {@link contentCallback} function if specified or saves content blocks, specified in 
	 * {@link contentSelector} to DB.
	 * @param string $content received content
	 * @return callback result, if {@link contentCallback} specified
	 */
	public function saveContent($content)
	{
		// is it wrong page or some error happend? 
		if (empty($content))
			return false;
		
		if (!empty($this->contentCallback))
			return call_user_func($this->contentCallback, $this->currLink->url, $content);
		elseif (!empty($this->contentSelector))
		{
			if ($this->contentSelector != '*')
			{
				$dom = str_get_html($content);
				$blocks = $dom->find($this->contentSelector);
				$content = '';
				foreach ($blocks as $block)
					$content .= $block->outertext;
			}
			$this->currLink->content = $content;
			$this->currLink->save();
			unset($dom);
		}
		else
			throw new CException('Please, set either contentCallback, or contentSelector option.');
	}
	
	/**
	 * Makes request to specified URL
	 * @return received content from URL
	 */
	public function makeRequest()
	{
		$result = false;
		$attempts = 0;
		curl_setopt ($this->curl, CURLOPT_URL, $this->currLink->url);
		while (empty($result) && !$this->isReachedLimit() && $attempts++ < 3)
		{
			// pause between requests
			usleep($this->timeInterval);
			// get content here
			$result = curl_exec($this->curl);
		}
		$result = YiiScraperLink::convertToUTF($result);
		return $result;
	}
	
	/**
	 * Filters links and change them to single form
	 * @param array $links links to process
	 * @return array of processed links
	 */
	public function filterLinks($links)
	{
		$result = array();
		if (isset($this->currLink))
		{
			$currPathInfo = YiiScraperLink::parse_url_utf8($this->currLink->url);
			foreach($links as $link)
			{
				$link = mb_strtolower($link);
				$parsedLinkInfo = YiiScraperLink::parse_url_utf8($link);

				if (empty($parsedLinkInfo) || 
						(!empty($parsedLinkInfo['user']) && empty($parsedLinkInfo['scheme']) && $parsedLinkInfo['user'] == 'mailto'))
					// avoid mailto:someone@example.com references
					continue;

				$link = YiiScraperLink::mergeUrls($currPathInfo, $parsedLinkInfo);
				if (!empty($this->insideUrlsOnly))
				{ 
					$i = 0; $length = count($this->insideUrlsOnly);
					while ($i < $length && mb_strpos($link, $this->insideUrlsOnly[$i]) !== 0)
						$i++;

					if ($i < $length)
						$result[] = $link;
				}
				else
					$result[] = $link;
			}
		}
		else
			$result = $links;
		// avoid different links, with last slash: 'http://google.com' and 'http://google.com/' for example
		foreach($result as $key => $link)
			if (mb_strpos($link, '?') === false)
			{
				$lastSlash = mb_strrpos($link, '/');
				if ($lastSlash !== mb_strlen($link) - 1)
					$result[$key] .= '/';
			}

		return $result;
	}
	
	/**
	 * Version info
	 * @return string version information
	 */
	public function getVersion()
	{
		return 'YiiScraper v0.8';
	}
	
	/**
	 * Uninstalls module
	 */
	public function uninstall()
	{
		Installer::uninstall($this->installMode);
	}
}
