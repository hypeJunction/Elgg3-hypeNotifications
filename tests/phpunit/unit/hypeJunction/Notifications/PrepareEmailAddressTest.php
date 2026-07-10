<?php

namespace hypeJunction\Notifications;

use Elgg\UnitTestCase;

/**
 * REGRESSION: PrepareEmail imported \Elgg\Email\Address, which Elgg 6 removed.
 * \Elgg\Email::setFrom()/setTo() declare Symfony\Component\Mime\Address.
 *
 * It never fataled in development because a stray `composer install` inside
 * mod/hypefaker had vendored a whole Elgg 5.1.12 core into its vendor/ dir, and
 * composer's autoloader resolved \Elgg\Email\Address to THAT stale copy — a
 * Laminas-based class. So every outbound email died with a TypeError on the
 * preview, and would have died with "class not found" once the stale core was
 * removed. Registration, password reset and all notifications were affected.
 */
class PrepareEmailAddressTest extends UnitTestCase {

	public function testTheRemovedElggAddressClassIsNotUsed(): void {
		$this->assertFalse(
			class_exists('Elgg\Email\Address'),
			'\Elgg\Email\Address was removed in Elgg 6; if it exists, a stale core is on the autoload path'
		);
	}

	public function testPrepareEmailImportsSymfonyAddress(): void {
		$src = file_get_contents(dirname(__DIR__, 5) . '/classes/hypeJunction/Notifications/PrepareEmail.php');

		$this->assertStringContainsString('use Symfony\Component\Mime\Address;', $src);
		$this->assertStringNotContainsString('use Elgg\Email\Address;', $src);
	}

	public function testSymfonyAddressRejectsANullDisplayName(): void {
		// $from->getName() may be null; Symfony's Address declares string $name
		$this->expectException(\TypeError::class);
		new \Symfony\Component\Mime\Address('a@example.invalid', null);
	}
}
