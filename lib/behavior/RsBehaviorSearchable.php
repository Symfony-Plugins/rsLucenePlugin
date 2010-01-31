<?php

/**
 * propel behavior for making models indexable und searchable
 *
 * @package    rsLucenePlugin
 * @subpackage lib.behavior
 * @author     robert schoenthal
 */
class rsBehaviorSearchable extends SfPropelBehaviorBase
{
  protected $parameters = array(
    'check_active' => false
  );

  public function postSave()
  {
    if ($this->isDisabled())
    {
      return;
    }

		if($this->getParameter('check_active') === false){
			return <<<EOF
rsLucene::updateDocument(\$this);
EOF;
		}else{			
			if ($column = $this->getParameter('check_active'))
			{
	      return <<<EOF
if (\$this->{$column})
{
  rsLucene::updateDocument(\$this);
}
else
{
  rsLucene::deleteDocument(\$this);
}
EOF;
				
			}
			
		}
  }

  public function postInsert()
  {
    if ($this->isDisabled())
    {
      return;
    }

		if($this->getParameter('check_active') === false){
			return <<<EOF
rsLucene::updateDocument(\$this);
EOF;
		}else{
			if ($column = $this->getParameter('check_active'))
			{
	      return <<<EOF
if (\$this->{$column})
{
rsLucene::updateDocument(\$this);
}
EOF;

			}

		}
  }

  public function postDelete()
  {
    if ($this->isDisabled())
    {
      return;
    }

      return <<<EOF
    rsLucene::deleteDocument(\$this);
EOF;
			
  }
}
