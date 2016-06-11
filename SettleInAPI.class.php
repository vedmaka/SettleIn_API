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
        #$title = $this->params['title'];
        #$search = SearchEngine::create();
        #$search->setNamespaces( NS_MAIN );
        #$result = $search->searchTitle( $title );
        $this->fResult['test'] = $this->parsedParams['do'];
        //die($this->params['pagename']);
    }
    
    public function getAllowedParams()
    {
        return array(
            'do' => false,
            'pagename' => false
        );
    }
    
}