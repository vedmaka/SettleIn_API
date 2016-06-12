<?php

/**
 * @group medium
 * @group API
 * @group Database
 * @covers SettleInAPI
 */
class SettleInAPITest extends ApiTestCase {

  protected function setUp() {
    parent::setUp();
  }

  protected function tearDown() {
    parent::tearDown();
  }

  function addDBData() {
    // Add set of page for suggestions generation
    $this->editPage('Sample page 1', 'Lorem ipsum');
    $this->editPage('Sample page 2', 'Lorem ipsum');
    $this->editPage('Sample page 3', 'Lorem ipsum');
  }

  public function testSuggestionsExists()
  {
    
    $ret = $this->doApiRequest(array(
        'action' => 'settlein',
        'do' => 'check_unique',
        'page' => 'Sample page 1'
    ));
    
    $this->assertCount( 3, $ret );
    $this->assertArrayHasKey( 'settlein', $ret[0] );
    $this->assertArrayHasKey( 'exists', $ret[0]['settlein'] );
    
    // Ensure that page exists
    $this->assertEquals( 1, $ret[0]['settlein']['exists'] );
    // Ensure that suggestions there
    $this->assertGreaterThan( 0, count($ret[0]['settlein']['suggestions'] ) );
    // Ensure that page itself is there
    $this->assertContains( 'Sample page 1', $ret[0]['settlein']['suggestions'] );
    //TODO: add more tests
    
  }
  
  public function testSuggestionsNotExists()
  {
    
    $ret = $this->doApiRequest(array(
        'action' => 'settlein',
        'do' => 'check_unique',
        'page' => 'Sample'
    ));
    
    $this->assertCount( 3, $ret );
    $this->assertArrayHasKey( 'settlein', $ret[0] );
    $this->assertArrayHasKey( 'exists', $ret[0]['settlein'] );
    
    // Ensure that page exists
    $this->assertEquals( 0, $ret[0]['settlein']['exists'] );
    // Ensure that suggestions there
    $this->assertGreaterThan( 0, count($ret[0]['settlein']['suggestions'] ) );
    // Ensure that page itself is there
    $this->assertContains( 'Sample page 1', $ret[0]['settlein']['suggestions'] );
    $this->assertContains( 'Sample page 2', $ret[0]['settlein']['suggestions'] );
    $this->assertContains( 'Sample page 3', $ret[0]['settlein']['suggestions'] );
    //TODO: add more tests
    
  }

  public function testNotExists() {

    $ret = $this->doApiRequest(array(
          'action' => 'settlein',
          'do' => 'check_unique',
          'page' => 'RandomNonExistentPageWithoutSuggestions'
        ));

    $this->assertCount( 3, $ret );
    $this->assertArrayHasKey( 'settlein', $ret[0] );
    $this->assertArrayHasKey( 'exists', $ret[0]['settlein'] );
    $this->assertEquals( 0, $ret[0]['settlein']['exists'] );

  }

  public function testExists() {

      $ret = $this->doApiRequest(array(
          'action' => 'settlein',
          'do' => 'check_unique',
          'page' => 'UTPage'
        ));

    $this->assertCount( 3, $ret );
    $this->assertArrayHasKey( 'settlein', $ret[0] );
    $this->assertArrayHasKey( 'exists', $ret[0]['settlein'] );
    $this->assertEquals( 1, $ret[0]['settlein']['exists'] );

  }

}