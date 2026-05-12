<?php

namespace hypeJunction\Notifications\Tests\Unit;

use hypeJunction\Notifications\DigestService;
use hypeJunction\Notifications\DigestTable;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class DigestServiceTest extends TestCase {

    private function makeService(): DigestService {
        $table = $this->createMock(DigestTable::class);
        return new DigestService($table);
    }

    public function testGetTableReturnsTable() {
        $table = $this->createMock(DigestTable::class);
        $service = new DigestService($table);
        $this->assertSame($table, $service->getTable());
    }

    public function testGetNextDeliveryTimeForHourIsInFuture() {
        $service = $this->makeService();
        $next = $service->getNextDeliveryTime(DigestService::HOUR);
        $this->assertGreaterThan(time(), $next);
    }

    public function testGetNextDeliveryTimeForDayIsInFuture() {
        $service = $this->makeService();
        $next = $service->getNextDeliveryTime(DigestService::DAY);
        $this->assertGreaterThan(time(), $next);
    }

    public function testGetNextDeliveryTimeForSixHoursIsTimestamp() {
        $service = $this->makeService();
        $next = $service->getNextDeliveryTime(DigestService::SIX_HOURS);
        $this->assertIsInt($next);
    }

    public function testGetNextDeliveryTimeForTwelveHoursIsTimestamp() {
        $service = $this->makeService();
        $next = $service->getNextDeliveryTime(DigestService::TWELVE_HOURS);
        $this->assertIsInt($next);
    }

    public function testIntervalConstants() {
        $this->assertEquals('never', DigestService::NEVER);
        $this->assertEquals('instant', DigestService::INSTANT);
        $this->assertEquals('hour', DigestService::HOUR);
        $this->assertEquals('day', DigestService::DAY);
    }
}
