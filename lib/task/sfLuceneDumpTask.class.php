<?php

/**
 * symfony task for deleting a lucene index
 *
 * @package    rsLucenePlugin
 * @subpackage lib.task
 * @author     robert schoenthal
 */
class rsLuceneDumpTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace        = 'lucene';
    $this->name             = 'dump';
    $this->briefDescription = 'dumps a lucene index';
    $this->aliases          = array("lucene-dump");
    $this->detailedDescription = <<<EOF
The [rsLuceneDump|INFO] task dumps a lucene index.
Call it with:

  [php symfony lucene:dump|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    rsLucene::dumpIndex();
    $this->logSection("Info", "dumped index: ".rsLucene::$indexName);
  }

}