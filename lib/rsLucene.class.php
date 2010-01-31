<?php

/**
 * symfony friendly zend lucene wrapper
 *
 * @package    rsLucenePlugin
 * @subpackage lib
 * @author     robert schoenthal
 */
class rsLucene {

	static protected $zendLoaded = false;

	/**
	 * the index name
	 * @var string
	 */
	static $indexName;

	/**
	 * models to index
	 * @var array
	 */
	static $classes;

	/**
	 * the search config
	 * @var array
	 */
	static $config;

	/**
	 * the current instance
	 * @var rsLucene
	 */
	static $instance;

	/**
	 * debug switch
	 * @var boolean
	 */
	static $debug;

	/**
	 * fetches the index for current env
	 * @return string
	 */
	private static function getLuceneIndexFile() {
		return sfConfig::get('sf_data_dir').'/'.self::$indexName.'.index';
	}

	public static function debug() {
		self::$debug = true;
	}

	/**
	 * prepares the search engine
	 */
	private static function prepareZendSearch() {

		Zend_Search_Lucene_Analysis_Analyzer::setDefault(new Zend_Search_Lucene_Analysis_Analyzer_Common_Utf8Num_CaseInsensitive());

		$stopWords = array();
		$stopWordsFilter = new Zend_Search_Lucene_Analysis_TokenFilter_StopWords($stopWords);
		Zend_Search_Lucene_Analysis_Analyzer::getDefault()->addFilter($stopWordsFilter);

		$shortWordsFilter = new Zend_Search_Lucene_Analysis_TokenFilter_ShortWords(3);
		Zend_Search_Lucene_Analysis_Analyzer::getDefault()->addFilter($shortWordsFilter);

		Zend_Search_Lucene_Storage_Directory_Filesystem::setDefaultFilePermissions(0777);
	}

	private static function getConfig() {

		$cfg = sfYaml::load(sfConfig::get('sf_config_dir').'/'.'search.yml');
		self::$config = $cfg["index"];

		self::$indexName = self::$config["meta"]["name"];
		self::$classes = self::$config["models"];

		self::loadZend();
	}

	private static function loadZend() {
		if(!self::$zendLoaded) {
			self::registerZend();
		}
	}

	private static function registerZend() {
		if(!class_exists('Zend_Loader_Autoloader')) {
			require_once ProjectConfiguration::getActive()->getZendPath();
		}

		Zend_Loader_Autoloader::getInstance();
		self::$zendLoaded = true;
	}

	/**
	 * returns the search instance
	 * @return rsLucene
	 */
	public static function getInstance() {
		if(!self::$instance) {
			self::$instance = self;
			self::getConfig();
			self::prepareZendSearch();
		}
		return self::$instance;
	}

	/**
	 * optimizes a lucene index
	 */
	public static function optimizeIndex() {

		$index = self::getIndex();
		$index->optimize();
	}

	/**
	 * returns index information
	 * @return array
	 */
	public static function getInfos() {
		$infos = array();
		$index = self::getIndex();
		$infos["documents"] = $index->numDocs();
		return $infos;
	}

	/**
	 * generates a lucene index
	 */
	public static function generateIndex() {
		$index = self::getIndex();

		foreach(self::$classes as $class=>$config) {
			$peer = isset($config["peer"]) ? $config["peer"] : $class."Peer";

			$models = call_user_func($peer."::doSelect",new Criteria());

			foreach($models as $model) {
				self::updateDocument($model,$config["fields"]);
			}
		}
	}

	/**
	 * dumps a lucene index
	 */
	public static function dumpIndex() {

		self::getConfig();

		if (file_exists($index = self::getLuceneIndexFile())) {
			sfToolkit::clearDirectory($index);
			rmdir($index);
		}
	}

	/**
	 * returns the index
	 * @return Zend_Search_Lucene
	 */
	public static function getIndex() {

		self::getConfig();
		self::prepareZendSearch();

		if (file_exists($index = self::getLuceneIndexFile())) {
			$lucene_index = Zend_Search_Lucene::open($index);
		}else {
			$lucene_index = Zend_Search_Lucene::create($index);
			chmod($index, 0777);
		}


		return $lucene_index;
	}

	/**
	 * prepares a query string for search
	 * @param string $query
	 * @return Zend_Search_Lucene_Search_Query
	 */
	private static function prepareQuery($query,$model) {

		if($model) {
			$query = "+".$query." +category:".$model;
		}else {
			$query = "+".$query;
		}

		$query = Zend_Search_Lucene_Search_QueryParser::parse($query); //search object

		return $query;

	}

	/**
	 * searches the index for a given query
	 * @param Zend_Search_Lucene_Search_Query $query
	 * @return array Zend_Search_Lucene_Search_QueryHit
	 */
	public static function search($query,$model=null) {
		self::getConfig();

		$query = self::prepareQuery($query,$model);
		if(!$query) {
			return false;
		}
		else {
			return self::getIndex()->find($query);
		}
	}

	/**
	 * updates a document
	 * @param sfBaseObject
	 */
	public static function updateDocument($object,$config=null) {
		$index = self::getIndex();

		if(!$config) {
			$config = self::$config["models"][get_class($object)]["fields"];
		}

		// remove existing entries
		foreach ($index->find(get_class($object).'_pk:'.$object->getId()) as $hit) {
			$index->delete($hit->id);
		}

		$doc = new Zend_Search_Lucene_Document();

		$doc->addField(Zend_Search_Lucene_Field::Keyword(get_class($object).'_pk', $object->getId()));

		foreach($config as $key=>$field) {
			if($key == "pk") {
				$doc->addField(Zend_Search_Lucene_Field::unIndexed('pk', $object->getId() , 'utf-8'));
			}elseif($key == "category") {
				$doc->addField(Zend_Search_Lucene_Field::Text('category', $field , 'utf-8'));
			}else {
				if(!$field) {
					$method = "get".$key;
				}
				elseif(is_array($field)) {
					$method = $field["method"] ? $field["method"] : "get".$key;
				}
				else {
					$method = "get".$field;
				}

				$value = $object->$method();

				$value = strip_tags($value);
				$objField = Zend_Search_Lucene_Field::Text($key, $value, 'utf-8');
				if(is_array($field)) {
					foreach($field as $pkey => $param) {
						$objField->$pkey = $param;
					}
				}
				$doc->addField($objField);
			}
		}

		if(self::$debug) {
			echo "add Document: ".get_class($object)." id:".$object->getId()."\n";
		}

		$index->addDocument($doc);

		$index->commit();
	}

	/**
	 * deletes a document
	 * @param sfBaseObject $object
	 */
	public static function deleteDocument($object) {
		$index = self::getIndex();

		foreach ($index->find(get_class($object).'_pk:'.$object->getId()) as $hit) {
			$index->delete($hit->id);
		}
	}

}

