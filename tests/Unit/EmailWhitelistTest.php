<?php

namespace hypeJunction\Notifications\Tests\Unit;

use hypeJunction\Notifications\EmailWhitelist;
use PHPUnit\Framework\TestCase;

/**
 * @group unit
 */
class EmailWhitelistTest extends TestCase {

    public function testNormalizeTrimsAndLowercases() {
        $this->assertEquals('user@example.com', EmailWhitelist::normalize('  User@Example.COM  '));
    }

    public function testNormalizeHandlesEmptyString() {
        $this->assertEquals('', EmailWhitelist::normalize(''));
    }

    public function testNormalizeAlreadyNormalized() {
        $this->assertEquals('user@example.com', EmailWhitelist::normalize('user@example.com'));
    }

    public function testIsWhitelistedReturnsFalseForInvalidEmail() {
        // Invalid emails should return false before any plugin setting lookups
        $this->assertFalse(EmailWhitelist::isWhitelisted('not-an-email'));
        $this->assertFalse(EmailWhitelist::isWhitelisted(''));
        $this->assertFalse(EmailWhitelist::isWhitelisted('@nodomain'));
    }
}
