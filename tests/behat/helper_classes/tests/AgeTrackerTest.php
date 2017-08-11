<?php
declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use PantheonSystems\CDNBehatHelpers\AgeTracker;

/**
 * @covers AgeTracker
 */
final class AgeTrackerTest extends TestCase
{
    /**
     * Tests AgeTracker::getTrackedHeaders
     *
     * @param string $path
     *   The url being tracked
     * @param array $headers
     *   The headers of each time the URL was checked
     *
     * @dataProvider providerPathsAndHeaders
     * @covers ::getTrackedHeaders
     */
    public function testGetTrackedHeaders($path, array $headers_set) {
        $AgeTracker = new AgeTracker();
        foreach ($headers_set as $headers) {
            $AgeTracker->trackHeaders($path, $headers);
        }
        $actual_tracked_headers = $AgeTracker->getTrackedHeaders($path);
        $this->assertEquals($headers_set, $actual_tracked_headers);
    }
    /**
     * Data provider for testExtractClipTitles.
     *
     * @return array
     *   An array of test data.
     */
    public function providerPathsAndHeaders() {
        $data = array();
        $data[] = [
            '/home',
            $this->cacheLifeIncreasing(),
        ];
        $data[] = [
            '/cache-got-cleared',
            $this->cacheGotClearedHeaders(),
        ];

        return $data;
    }


    public function providerExpectedCacheClears() {
        $data = array();
        $data[] = [
            '/home',
            $this->cacheLifeIncreasing(),
            FALSE,
        ];
        $data[] = [
            '/cache-got-cleared',
            $this->cacheGotClearedHeaders(),
            TRUE,
        ];

        return $data;
    }

    /**
     * Tests AgeTracker::getTrackedHeaders
     *
     * @param string $path
     *   The url being tracked
     * @param array $headers
     *   The headers of each time the URL was checked
     *
     * @dataProvider providerExpectedCacheClears
     * @covers ::wasCacheClearedBetweenLastTwoRequests
     */
    public function testCheckCacheClear($path, array $headers_set, $expected_cache_clear) {
        $AgeTracker = new AgeTracker();

        foreach ($headers_set as $headers) {
            $AgeTracker->trackHeaders($path, $headers);
        }
        $this->assertEquals($expected_cache_clear, $AgeTracker->wasCacheClearedBetweenLastTwoRequests($path));
    }



    protected function cacheLifeIncreasing(){
      return  [
          [
              'Cache-Control' => ['max-age=600, public'],
              'Age' => [3],
              'X-Timer' => ['S1502402462.916272,VS0,VE1']
          ],
          [
              'Cache-Control' => ['max-age=600, public'],
              'Age' => [10],
              'X-Timer' => ['S1502402469.916272,VS0,VE1']
          ],
      ];

    }

    protected function cacheGotClearedHeaders(){
        return             [
            [
                'Cache-Control' => ['max-age=600, public'],
                'Age' => [30],
                'X-Timer' => ['S1502402462.916272,VS0,VE1']
            ],
            [
                'Cache-Control' => ['max-age=600, public'],
                'Age' => [0],
                'X-Timer' => ['S1502402469.916272,VS0,VE1']
            ],
        ];

    }
}