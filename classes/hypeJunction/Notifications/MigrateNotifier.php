<?php

namespace hypeJunction\Notifications;

use Elgg\Upgrade\AsynchronousUpgrade;
use Elgg\Upgrade\Result;

/**
 * MigrateNotifier class.
 */
class MigrateNotifier extends AsynchronousUpgrade {

	/**
	 * Version of the upgrade
	 *
	 * This tells the date when the upgrade was added. It consists of eight digits and is in format ``yyyymmddnn``
	 * where:
	 *
	 * - ``yyyy`` is the year
	 * - ``mm`` is the month (with leading zero)
	 * - ``dd`` is the day (with leading zero)
	 * - ``nn`` is an incrementing number (starting from ``00``) that is used in case two separate upgrades
	 *          have been added during the same day
	 *
	 * @return int E.g. 2016123101
	 */
	public function getVersion(): int {
		return 20180308000;
	}

	/**
	 * Should this upgrade be skipped?
	 *
	 * If true, the upgrade will not be performed and cannot be accessed later.
	 *
	 * @return bool
	 */
	public function shouldBeSkipped(): bool {
		return !$this->countItems();
	}

	/**
	 * Should the run() method receive an offset representing all processed items?
	 *
	 * @return bool
	 */
	public function needsIncrementOffset(): bool {
		return true;
	}

	/**
	 * The total number of items to process during the upgrade
	 *
	 * @return int
	 */
	public function countItems(): int {
		$count = \elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'notification',
			'count' => true,
		]);

		return (int) $count;
	}

	/**
	 * Runs upgrade on a single batch of items
	 *
	 * @param Result $result Result of the batch (this must be returned)
	 * @param int    $offset Number to skip when processing
	 *
	 * @return Result Instance of \Elgg\Upgrade\Result
	 */
	public function run(Result $result, $offset): Result {

		$entities = \elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'notification',
			'offset' => $offset,
		]);

		foreach ($entities as $entity) {
			$objects = $entity->getEntitiesFromRelationship(['relationship' => 'hasObject']);
			$object = null;
			if ($objects) {
				$object = array_shift($objects);
			}

			$actors = $entity->getEntitiesFromRelationship(['relationship' => 'hasActor']);
			if (!$actors) {
				$entity->delete();
				$result->addSuccesses();
				continue;
			}

			$recipient = $entity->getOwnerEntity();
			if (!$recipient) {
				$entity->delete();
				$result->addSuccesses();
				continue;
			}

			foreach ($actors as $actor) {
				$notification = new Notification();
				$notification->setActor($actor);
				$notification->setRecipient($recipient);
				if ($object) {
					$notification->setObject($object);
				} else {
					list($action, $object_type, $object_subtype) = explode(':', $entity->event);
					$notification->action = $action;
					$notification->object_type = $object_type;
					$notification->object_subtype = $object_subtype;
				}

				$notification->time_created = $entity->time_created;

				if ($entity->status == 'read') {
					$notification->markAsSeen();
					$notification->markAsRead();
				}

				$notification->setData([
					'summary' => $entity->title,
				]);

				if ($notification->save()) {
					$entity->delete();
					$result->addSuccesses();
				} else {
					$result->addFailures();
				}
			}
		}

		return $result;
	}
}
