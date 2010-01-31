<?php

/**
 * symfony task for generating a lucene index
 *
 * @package    rsLucenePlugin
 * @subpackage lib.task
 * @author     robert schoenthal
 */
class rsLuceneGenerateTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace        = 'lucene';
    $this->name             = 'generate';
    $this->briefDescription = 'generates a lucene index';
    $this->aliases          = array("lucene-generate");
    $this->detailedDescription = <<<EOF
The [rsLuceneGenerate|INFO] task generates a lucene index.
Call it with:

  [php symfony lucene:generate|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection("Task", "start lucene index generating");

    // initialize the database connection
    $databaseManager = new sfDatabaseManager($this->configuration);
    $connection = $databaseManager->getDatabase($options['connection'] ? $options['connection'] : null)->getConnection();

    $stime = time();
    rsLucene::debug();

    rsLucene::dumpIndex();
    rsLucene::generateIndex();
		rsLucene::optimizeIndex();
    $etime = time();

    $time = $etime - $stime;

    foreach(rsLucene::getInfos() as $key=>$info){
      $this->logSection($key,$info);
    }
    $this->logSection("time",$time." s");

    $this->logSection("index",rsLucene::$indexName);
  }

}
