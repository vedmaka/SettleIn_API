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

	    $country = false;
	    $state = false;
	    $city = false;

	    if( $this->parsedParams['country'] && !empty($this->parsedParams['country']) ) {
	    	$country = $this->parsedParams['country'];
	    }

	    if( $this->parsedParams['state'] && !empty($this->parsedParams['state']) ) {
		    $state = $this->parsedParams['state'];
	    }

	    if( $this->parsedParams['city'] && !empty($this->parsedParams['city']) ) {
		    $city = $this->parsedParams['city'];
	    }

	    //$searchTitle = Title::newFromText( $title );

	    $isTitleExists = false;

	    /*
	    if( $searchTitle && $searchTitle->exists() ) {
	    	$isTitleExists = true;
	    }

	    $suggestions = array();

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
	    
	    // If title with exact same name already exists lets add it to suggestions
	    if( $isTitleExists ) {
	        $suggestions[] = array(
	        	'title' => $searchTitle->getBaseText(),
		        'link' => $searchTitle->getFullURL()
	        );
	    }*/

	    $suggestions = array();

	    //$results = $this->getExactPages( $title, $country, $state, $city );
	    $results = $this->getExactPagesSphinx( $title, $country, $state, $city );

	    if( count($results) ) {
	    	$isTitleExists = true;
		    foreach ($results as $result) {
		    	$suggestions[] = array(
		    		'title' => SemanticTitle::getText( $result['title'] ),
				    'link' => $result['title']->getFullURL()
			    );
		    }
	    }else{
	    	// There is no exact match, but should we display similar pages instead ?
			//$suggestions = $this->getSimilarPages( $title );
		    //$suggestions = $this->getSimilarPagesEx( $title );
		    $suggestions = $this->getSimilarPagesSphinx( $title );
	    }

        $this->fResult['exists'] = (int)$isTitleExists;
        $this->fResult['suggestions'] = $suggestions;

    }

    //TODO: use Sphinx to query title-related properties to reduce CPU load and improve matching
	private function getExactPagesSphinx( $title, $country, $state, $city )
	{

		//TODO: be safe, but may lead to some unnecessary mismatches until same filter applied to page creation action
		$title = str_replace('"', '', $title);

		$store = SphinxStore::getInstance();
		$results = array();
		$result = $store->getQuery()
			->select('id')
			->from( $store->getIndex() )
			// Query against `alias_title` attribute (not field) to receive exact case-insensitive match
			->where( 'alias_title', $title );
			//->match( 'title', \Foolz\SphinxQL\SphinxQL::expr('"^'.$title.'$"') );

		if( $country ) {
			$result->where( 'properties.country[0]', $country );
		}

		if( $state ) {
			$result->where( 'properties.state[0]', $state );
		}

		if( $city ) {
			$result->where( 'properties.city[0]', $city );
		}

		$result = $result->execute();

		if( $result ) {
			foreach ($result as $r) {
				$results[] = array(
					'title' => Title::newFromID( $r['id'] )
				);
			}
		}

		return $results;
	}

	private function getSimilarPagesSphinx( $title )
	{

		//TODO: be safe, but may lead to some unnecessary mismatches until same filter applied to page creation action
		$title = str_replace('"', '', $title);
		$results = array();

		$store = SphinxStore::getInstance();
		$result = $store->getQuery()
			->select('id')
			->from( $store->getIndex() )
			->match('*', $title)
			->execute();

		if( $result )
		{
			foreach ( $result as $r ) {
				$t = Title::newFromID( $r['id'] );
				$results[] = array(
					'title' => SemanticTitle::getText( $t ),
					'link' => $t->getFullURL()
				);
			}
		}

		return $results;

	}

	private function getExactPages( $title, $country, $state, $city )
	{
		// Perform semantic search
		$sqi = new \SQI\SemanticQueryInterface();

		$query = $sqi->category('Card');

		$query->equals( 'Title', ucfirst($title) );

		if( $country ) {
			$query->condition( 'Country', $country );
		}
		if( $state ) {
			$query->condition( 'State', $state );
		}
		if( $city ) {
			$query->condition( 'City', $city );
		}

		$results = $sqi->toArray();

		return $results;
	}

	private function getSimilarPagesEx( $title )
	{
		$suggestions = array();

		// Perform semantic search
		$sqi = new \SQI\SemanticQueryInterface();

		$query = $sqi->category('Card');

		$query->like( 'Title', ucfirst($title).'*' );

		$results = $sqi->toArray();
		if( count($results) ) {
			foreach ($results as $result) {
				$suggestions[] = array(
					'title' => SemanticTitle::getText( $result['title'] ),
					'link' => $result['title']->getFullURL()
				);
			}
		}

		return $suggestions;
	}

    private function getSimilarPages( $titleName )
    {
	    $suggestions = array();

	    $search = SearchEngine::create();
	    $search->setNamespaces( array( NS_MAIN ) );
	    $search->setLimitOffset( 10 );
	    $result = $search->searchTitle( $search->transformSearchTerm( $search->replacePrefixes( $titleName ) ) );

	    if( !is_null($result) ) {
		    while ( $row = $result->next() ) {
		    	//TODO: fix that
			    if ( strtolower($titleName) === strtolower($row->getTitle()->getBaseText()) ) {
				    continue;
			    }
			    $suggestions[] = array(
				    'title' => $row->getTitle()->getBaseText(),
				    'link' => $row->getTitle()->getFullURL()
			    );
		    }
	    }

	    return $suggestions;
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
	        ),
            'state' => array(
	            ApiBase::PARAM_TYPE => 'string',
	            ApiBase::PARAM_REQUIRED => false
            ),
            'city' => array(
	            ApiBase::PARAM_TYPE => 'string',
	            ApiBase::PARAM_REQUIRED => false
            )

        );
    }
    
}