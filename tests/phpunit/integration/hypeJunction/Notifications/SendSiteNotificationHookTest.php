<?php

namespace hypeJunction\Notifications;

use Elgg\IntegrationTestCase;
use Elgg\Notifications\Notification as ElggNotification;
use Elgg\Notifications\SubscriptionNotificationEvent as NotificationEvent;

/**
 * Verifies the SendSiteNotification event handler actually inserts a
 * row into site_notifications when triggered with a notification:site
 * payload, and that it short-circuits when another handler has already
 * sent the notification.
 */
class SendSiteNotificationHookTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testHandlerInsertsSiteNotification(): void {
		$recipient = $this->createUser();
		$actor = $this->createUser();
		$object = $this->createObject(['subtype' => 'blog', 'owner_guid' => $actor->guid]);

		$notification = new ElggNotification($actor, $recipient, 'en', 'subject', 'body', '', []);
		$event = new NotificationEvent($object, 'create', $actor);

		$result = \elgg_trigger_event_results('send', 'notification:site', [
			'notification' => $notification,
			'event' => $event,
		], false);

		$this->assertTrue($result, 'Handler should signal delivery success');

		\elgg_call(ELGG_IGNORE_ACCESS, function () use ($recipient, $object) {
			_elgg_services()->session_manager->setLoggedInUser($recipient);
			$rows = \elgg()->{'notifications.site'}->getTable()->getAll([
				'recipient_guid' => $recipient->guid,
			]);
			_elgg_services()->session_manager->removeLoggedInUser();
			$this->assertNotEmpty($rows);
			$found = false;
			foreach ($rows as $row) {
				if ((int) $row->object_id === (int) $object->guid && $row->action === 'create') {
					$found = true;
					\elgg()->{'notifications.site'}->getTable()->delete($row->id);
				}
			}
			$this->assertTrue($found, 'Inserted row for the triggering object should be found');
		});
	}

	public function testHandlerShortCircuitsWhenAlreadySent(): void {
		$recipient = $this->createUser();
		$actor = $this->createUser();
		$notification = new ElggNotification($actor, $recipient, 'en', 's', 'b', '', []);

		// Pass true as initial value: another handler "already sent it"
		$result = \elgg_trigger_event_results('send', 'notification:site', [
			'notification' => $notification,
		], true);

		$this->assertTrue($result);

		\elgg_call(ELGG_IGNORE_ACCESS, function () use ($recipient) {
			_elgg_services()->session_manager->setLoggedInUser($recipient);
			$count = \elgg()->{'notifications.site'}->getTable()->count([
				'recipient_guid' => $recipient->guid,
			]);
			_elgg_services()->session_manager->removeLoggedInUser();
			$this->assertSame(0, $count, 'No row should be inserted on short-circuit');
		});
	}
}
