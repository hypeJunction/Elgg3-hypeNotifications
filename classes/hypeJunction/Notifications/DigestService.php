<?php

namespace hypeJunction\Notifications;

use DateTime;
use Elgg\Notifications\InstantNotificationEvent;
use Elgg\Notifications\Notification;
use Elgg\Notifications\NotificationEvent;
use ElggData;
use ElggEntity;
use ElggObject;

/**
 * @access private
 */
class DigestService {

	const NEVER = 'never';
	const INSTANT = 'instant';
	const HOUR = 'hour';
	const SIX_HOURS = 'six_hour';
	const TWELVE_HOURS = 'twelve_hour';
	const DAY = 'day';

	/**
	 * @var self
	 */
	static $_instance;

	/**
	 * @var DigestTable
	 */
	private $table;

	/**
	 * Constructor
	 *
	 * @param DigestTable $table DB table
	 */
	public function __construct(DigestTable $table) {
		$this->table = $table;
	}

	/**
	 * Returns DB table
	 * @return DigestTable
	 */
	public function getTable() {
		return $this->table;
	}

	/**
	 * Get registered notification events
	 * @return array
	 */
	public static function getNotificationEvents() {
		$ignored_events = [
			'send',
			'enqueue',
			'admin_approval',
			'unban',
			'make_admin',
			'remove_admin',
		];

		$subscriptions = _elgg_services()->notifications->getEvents();

		foreach ($subscriptions as $object_type => $object_subtypes) {
			foreach ($object_subtypes as $object_subtype => $events) {
				foreach ($events as $key => $event) {
					if (in_array($event, $ignored_events)) {
						unset($subscriptions[$object_type][$object_subtype][$key]);
					}
				}
			}
		}

		$notification_events['subscriptions'] = $subscriptions;

		// Add instant notifications that can be batched
		$notification_events['instant']['user']['default'][] = 'add_friend';
		if (elgg_is_active_plugin('friend_request')) {
			$notification_events['instant']['user']['default'][] = 'friend_request';
			$notification_events['instant']['user']['default'][] = 'friend_request_decline';
		}

		$notification_events['instant']['object']['comment'][] = 'create';

		if (elgg_is_active_plugin('likes')) {
			$notification_events['instant']['annotation']['likes'][] = 'create';
		}

		if (elgg_is_active_plugin('groups')) {
			$notification_events['instant']['group']['default'][] = 'add_membership';
			$notification_events['instant']['group']['default'][] = 'invite';
		}

		return elgg_trigger_plugin_hook('notification_events', 'notifications', null, $notification_events);
	}

	/**
	 * Get the time of the next digest delivery
	 *
	 * @param string $interval Interval
	 *
	 * @return int
	 */
	public function getNextDeliveryTime($interval = null) {

		$now = new DateTime();
		$dt = new DateTime();

		switch ($interval) {
			case self::HOUR :
				$dt->modify('+1 hour');
				$h = $dt->format('H');
				$dt->setTime($h, 0, 0);
				break;

			case self::SIX_HOURS :
				foreach ([0, 6, 12, 18, 24] as $h) {
					$dt->setTime($h, 0, 0);
					if ($dt->getTimestamp() > $now->getTimestamp()) {
						break;
					}
				}
				break;

			case self::TWELVE_HOURS :
				foreach ([0, 12, 24] as $h) {
					$dt->setTime($h, 0, 0);
					if ($dt->getTimestamp() > $now->getTimestamp()) {
						break;
					}
				}
				break;

			case self::DAY :
				$dt->modify('+1 day');
				$dt->setTime(0, 0, 0);
				break;
		}

		return $dt->getTimestamp();
	}
}
