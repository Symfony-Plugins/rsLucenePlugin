<?php

/**
 * symfony task for optimizing a lucene index
 *
 * @package    rsLucenePlugin
 * @subpackage lib.task
 * @author     robert schoenthal
 */
class rsLuceneOptimizeTask extends sfBaseTask
{
  protected function configure()
  {

    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace        = 'lucene';
    $this->name             = 'optimize';
    $this->briefDescription = 'optimizes a lucene index';
    $this->aliases          = array("lucene-optimize");
    $this->detailedDescription = <<<EOF
The [rsLuceneOptimize|INFO] task optimizes a lucene index.
Call it with:

  [php symfony lucene:optimize|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    $this->logSection("Info", "optimzing index: ".sfKaozLucene::$indexName);
    rsLucene::optimizeIndex();

    $this->logSection("Task", "lucene index optimizing complete");
  }

}
