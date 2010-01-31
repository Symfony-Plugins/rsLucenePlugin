<?php

/**
 * symfony task for shows information of a lucene index
 *
 * @package    rsLucenePlugin
 * @subpackage lib.task
 * @author     robert schoenthal
 */
class sfLuceneInfoTask extends sfBaseTask
{
  protected function configure()
  {
    $this->addOptions(array(
      new sfCommandOption('connection', null, sfCommandOption::PARAMETER_REQUIRED, 'The connection name', 'propel'),
    ));

    $this->namespace        = 'lucene';
    $this->name             = 'info';
    $this->briefDescription = 'show informations about a lucene index';
    $this->aliases          = array("lucene-info");
    $this->detailedDescription = <<<EOF
The [rsLuceneInfo|INFO] task show informations about a lucene index.
Call it with:

  [php symfony lucene:info|INFO]
EOF;
  }

  protected function execute($arguments = array(), $options = array())
  {
    foreach(rsLucene::getInfos() as $key=>$info){
      echo $key." : ".$info."\n";
    }
  }
}
