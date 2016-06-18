<?php

class SettleInAPI extends ApiBase {
    
    protected $parsedParams;
    protected $fResult;
    
    public function execute()
    {
        $this->parsedParams = $this->extractRequestParams();
        $do = $this->parsedParams['do'];
        switch( $do )
        {
            case 'check_unique':
                $this->check_unique();
                break;
        }
        $this->getResult()->addValue( null, $this->getModuleName(), $this->fResult );
        
    }
    
    private function check_unique()
    {
        $title = $this->parsedParams['page'];
	    $searchTitle = Title::newFromText( $title );

	    $isTitleExists = false;
	    if( $searchTitle && $searchTitle->exists() ) {
	    	$isTitleExists = true;
	    }

	    $suggestions = array();

	    //if( $isTitleExists ) {
		    $search = SearchEngine::create();
		    $search->setNamespaces( array( NS_MAIN ) );
		    $search->setLimitOffset( 10 );
		    $result = $search->searchTitle( $search->transformSearchTerm( $search->replacePrefixes($title) ) );
		    //$result = $search->getNearMatchResultSet( $search->transformSearchTerm( $search->replacePrefixes($title) ) );
		    if( !is_null($result) ) {
			    while ( $row = $result->next() ) {
				    if ( $searchTitle->getArticleID() === $row->getTitle()->getArticleID() ) {
					    continue;
				    }
				    $suggestions[] = array(
				    	'title' => $row->getTitle()->getBaseText(),
					    'link' => $row->getTitle()->getFullURL()
				    );
			    }
		    }
	    //}
	    
	    // If title with exact same name already exists lets add it to suggestions
	    if( $isTitleExists ) {
	        $suggestions[] = array(
	        	'title' => $searchTitle->getBaseText(),
		        'link' => $searchTitle->getFullURL()
	        );
	    }

        $this->fResult['exists'] = (int)$isTitleExists;
        $this->fResult['suggestions'] = $suggestions;

    }
    
    public function getAllowedParams()
    {
        return array(
            'do' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => true
            ),
            'page' => array(
                ApiBase::PARAM_TYPE => 'string',
                ApiBase::PARAM_REQUIRED => false
            ),
	        'category' => array(
	        	ApiBase::PARAM_TYPE => 'string',
		        ApiBase::PARAM_REQUIRED => false
	        ),
	        'country' => array(
	        	ApiBase::PARAM_TYPE => 'string',
	        	ApiBase::PARAM_REQUIRED => false
	        )
        );
    }
    
}