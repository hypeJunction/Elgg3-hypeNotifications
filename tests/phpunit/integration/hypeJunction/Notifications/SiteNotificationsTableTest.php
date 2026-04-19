<?php

namespace hypeJunction\Notifications;

use Elgg\IntegrationTestCase;

/**
 * Pre-migration test suite for SiteNotificationsTable.
 *
 * Verifies the on-site notifications storage layer end-to-end:
 * insert, read, mark seen/read, count, delete. Tests assert on the
 * round-tripped Notification objects so that any migration that
 * silently changes the schema, the row->object mapping, or the
 * markRead semantics will fail.
 */
class SiteNotificationsTableTest extends IntegrationTestCase {

	public function up() {
		$libFile = dirname(__DIR__, 5) . '/lib/functions.php';
		if (file_exists($libFile) && !function_exists('hypeapps_get_notifications')) {
			require_once $libFile;
		}
	}

	public function down() {}

	public function getPluginID(): string {
		// Skip the auto plugin-active check; the plugin is loaded via mod mount.
		return '';
	}

	private function table(): SiteNotificationsTable {
		return \elgg()->{'notifications.site'}->getTable();
	}

	private function makeNotification(\ElggUser $recipient, \ElggUser $actor = null, \ElggEntity $object = null, string $action = 'create'): Notification {
		$row = (object) [
			'recipient_guid' => $recipient->guid,
			'actor_guid' => $actor ? $actor->guid : 0,
			'object_id' => $object ? $object->guid : 0,
			'object_type' => $object ? $object->getType() : '',
			'object_subtype' => $object ? $object->getSubtype() : '',
			'action' => $action,
			'time_created' => time(),
			'time_seen' => 0,
			'time_read' => 0,
			'access_id' => $recipient->guid,
			'access_owner_guid' => $recipient->guid,
			'access_guid' => $recipient->guid,
			'data' => null,
		];
		$n = new Notification($row);
		$n->setData(['summary' => 'a summary', 'body' => 'a body']);
		return $n;
	}

	public function testInsertAssignsIdAndPersistsFields(): void {
		$recipient = $this->createUser();
		$actor = $this->createUser();
		$object = $this->createObject(['subtype' => 'blog', 'owner_guid' => $actor->guid]);

		$n = $this->makeNotification($recipient, $actor, $object, 'create');
		$id = $this->table()->insert($n);
		$this->assertIsInt($id);
		$this->assertGreaterThan(0, $id);

		$loaded = $this->table()->get($id);
		$this->assertInstanceOf(Notification::class, $loaded);
		$this->assertEquals($recipient->guid, $loaded->recipient_guid);
		$this->assertEquals($actor->guid, $loaded->actor_guid);
		$this->assertEquals($object->guid, $loaded->object_id);
		$this->assertEquals('object', $loaded->object_type);
		$this->assertEquals('blog', $loaded->object_subtype);
		$this->assertEquals('create', $loaded->action);
		$this->assertGreaterThan(0, $loaded->time_created);
		$this->assertSame(0, (int) $loaded->time_seen);
		$this->assertSame(0, (int) $loaded->time_read);

		$this->table()->delete($id);
	}

	public function testDataIsSerializedAndRestored(): void {
		$recipient = $this->createUser();
		$n = $this->makeNotification($recipient);
		$n->setData(['summary' => 'sum', 'body' => 'bd', 'extra' => ['nested' => 1]]);
		$id = $this->table()->insert($n);

		$loaded = $this->table()->get($id);
		$this->assertIsArray($loaded->data);
		$this->assertEquals('sum', $loaded->data['summary']);
		$this->assertEquals('bd', $loaded->data['body']);
		$this->assertSame(1, $loaded->data['extra']['nested']);

		$this->table()->delete($id);
	}

	public function testGetAllFiltersByRecipient(): void {
		$a = $this->createUser();
		$b = $this->createUser();

		$ids = [];
		$ids[] = $this->table()->insert($this->makeNotification($a));
		$ids[] = $this->table()->insert($this->makeNotification($a));
		$ids[] = $this->table()->insert($this->makeNotification($b));

\elgg_call(ELGG_IGNORE_ACCESS | ELGG_SHOW_DISABLED_ENTITIES, function () use ($a, $b) {
			\elgg_get_session()->setLoggedInUser($a);
			$forA = $this->table()->getAll(['recipient_guid' => $a->guid]);
			$forB = $this->table()->getAll(['recipient_guid' => $b->guid]);
			\elgg_get_session()->removeLoggedInUser();
			$this->assertCount(2, $forA);
			$this->assertCount(1, $forB);
		});

		foreach ($ids as $id) {
			$this->table()->delete($id);
		}
	}

	public function testCountReturnsInt(): void {
		$recipient = $this->createUser();
		$ids = [];
		for ($i = 0; $i < 3; $i++) {
			$ids[] = $this->table()->insert($this->makeNotification($recipient));
		}

\elgg_call(ELGG_IGNORE_ACCESS, function () use ($recipient) {
			\elgg_get_session()->setLoggedInUser($recipient);
			$count = $this->table()->count(['recipient_guid' => $recipient->guid]);
			\elgg_get_session()->removeLoggedInUser();
			$this->assertSame(3, $count);
		});

		foreach ($ids as $id) {
			$this->table()->delete($id);
		}
	}

	public function testStatusFiltersUnreadAndRead(): void {
		$recipient = $this->createUser();
		$id1 = $this->table()->insert($this->makeNotification($recipient));
		$id2 = $this->table()->insert($this->makeNotification($recipient));

		// Mark only the first as read
		$n1 = $this->table()->get($id1);
		$n1->markAsRead();

\elgg_call(ELGG_IGNORE_ACCESS, function () use ($recipient) {
			\elgg_get_session()->setLoggedInUser($recipient);
$unread = $this->table()->getAll([
				'recipient_guid' => $recipient->guid,
				'status' => 'unread',
			]);
$read = $this->table()->getAll([
				'recipient_guid' => $recipient->guid,
				'status' => 'read',
			]);
			\elgg_get_session()->removeLoggedInUser();
			$this->assertCount(1, $unread);
			$this->assertCount(1, $read);
		});

		$this->table()->delete($id1);
		$this->table()->delete($id2);
	}

	public function testMarkAllReadSetsTimestamps(): void {
		$recipient = $this->createUser();
		$ids = [];
		for ($i = 0; $i < 3; $i++) {
			$ids[] = $this->table()->insert($this->makeNotification($recipient));
		}

		$this->assertTrue((bool) $this->table()->markAllRead($recipient->guid));

		foreach ($ids as $id) {
			$loaded = $this->table()->get($id);
			$this->assertGreaterThan(0, (int) $loaded->time_read);
			$this->assertGreaterThan(0, (int) $loaded->time_seen);
		}

		foreach ($ids as $id) {
			$this->table()->delete($id);
		}
	}

	public function testDeleteRemovesRow(): void {
		$recipient = $this->createUser();
		$id = $this->table()->insert($this->makeNotification($recipient));

		$this->assertInstanceOf(Notification::class, $this->table()->get($id));
		$this->table()->delete($id);
		$this->assertEmpty($this->table()->get($id));
	}

	public function testDeleteByEntityGuidRemovesAssociatedRows(): void {
		$recipient = $this->createUser();
		$actor = $this->createUser();
		$object = $this->createObject(['subtype' => 'blog', 'owner_guid' => $actor->guid]);

		$id = $this->table()->insert($this->makeNotification($recipient, $actor, $object));
		$this->assertInstanceOf(Notification::class, $this->table()->get($id));

		$this->table()->deleteByEntityGUID($object->guid);
		$this->assertEmpty($this->table()->get($id));
	}

	public function testGetReturnsFalseForUnknownId(): void {
		$res = $this->table()->get(999999999);
		$this->assertEmpty($res);
	}
}
