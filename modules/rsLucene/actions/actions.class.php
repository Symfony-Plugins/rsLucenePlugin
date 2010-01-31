<?php

/**
 * search actions.
 *
 * @package    rsLucenePlugin
 * @subpackage actions
 * @author     robert schoenthal
 */
class homeActions extends sfActions {

  public function executeSearch(sfWebRequest $request)
  {
    try{
      $this->hits = rsLucene::search($request->getParameter('query'),$request->getParameter("category"));
    }
    catch(Exception $e){

    }
  }
}
