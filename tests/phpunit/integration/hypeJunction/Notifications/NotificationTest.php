<?php

namespace hypeJunction\Notifications;

use Elgg\IntegrationTestCase;

/**
 * Tests the Notification value object: setters, save(), markAsRead/Seen,
 * and the getURL/getSubtype helpers. These tests pin behavior of public
 * API surface that consumers depend on, so a migration that drops
 * properties or alters method semantics will fail loudly.
 */
class NotificationTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testSetActionPersistsAfterSave(): void {
		$recipient = $this->createUser();
		$n = new Notification();
		$n->setRecipient($recipient);
		$n->setAction('comment');
		$this->assertTrue($n->save());
		$this->assertGreaterThan(0, (int) $n->id);

		$loaded = \elgg()->{'notifications.site'}->getTable()->get($n->id);
		$this->assertEquals('comment', $loaded->action);

		\elgg()->{'notifications.site'}->getTable()->delete($n->id);
	}

	public function testCannotChangeRecipientAfterSave(): void {
		$a = $this->createUser();
		$b = $this->createUser();
		$n = new Notification();
		$n->setRecipient($a);
		$this->assertTrue($n->save());
		$id = $n->id;

		$this->expectException(\LogicException::class);
		try {
			$n->setRecipient($b);
		} finally {
			\elgg()->{'notifications.site'}->getTable()->delete($id);
		}
	}

	public function testMarkAsReadSetsTimestampOnUnsavedNotification(): void {
		$n = new Notification();
		$n->setRecipient($this->createUser());
		$n->markAsRead(123456789);
		$this->assertSame(123456789, $n->time_read);
		$this->assertTrue($n->isRead());
	}

	public function testMarkAsSeenIsTrueAfterMarking(): void {
		$n = new Notification();
		$n->setRecipient($this->createUser());
		$this->assertFalse($n->isSeen());
		$n->markAsSeen(111);
		$this->assertTrue($n->isSeen());
	}

	public function testGetSubtypeJoinsActionAndType(): void {
		$recipient = $this->createUser();
		$actor = $this->createUser();
		$obj = $this->createObject(['subtype' => 'blog', 'owner_guid' => $actor->guid]);
		$n = new Notification();
		$n->setRecipient($recipient);
		$n->setObject($obj);
		$n->setAction('publish');
		$this->assertEquals('publish:object:blog', $n->getSubtype());
	}

	public function testGetUrlIsFalseBeforeSaveAndStringAfter(): void {
		$n = new Notification();
		$n->setRecipient($this->createUser());
		$this->assertFalse($n->getURL());

		$this->assertTrue($n->save());
		$id = (int) $n->id;
		$url = $n->getURL();
		$this->assertIsString($url);
		$this->assertStringContainsString('/notifications/view/' . $id, $url);

		\elgg()->{'notifications.site'}->getTable()->delete($id);
	}

	public function testTypeIsNotification(): void {
		$n = new Notification();
		$this->assertEquals('notification', $n->getType());
	}
}
