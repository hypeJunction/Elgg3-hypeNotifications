<?php

namespace hypeJunction\Notifications;

use Elgg\Upgrade\Batch;
use Elgg\Upgrade\Result;

class MigrateNotifier implements Batch {

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
	public function getVersion() {
		return 20180308000;
	}

	/**
	 * Should this upgrade be skipped?
	 *
	 * If true, the upgrade will not be performed and cannot be accessed later.
	 *
	 * @return bool
	 */
	public function shouldBeSkipped() {
		return !$this->countItems();
	}

	/**
	 * Should the run() method receive an offset representing all processed items?
	 *
	 * If true, run() will receive as $offset the number of items already processed. This is useful
	 * if you are only modifying data, and need to use the $offset in a function like elgg_get_entities*()
	 * to know how many to skip over.
	 *
	 * If false, run() will receive as $offset the total number of failures. This should be used if your
	 * process deletes or moves data out of the way of the process. E.g. if you delete 50 objects on each
	 * run(), you may still use the $offset to skip objects that already failed once.
	 *
	 * @return bool
	 */
	public function needsIncrementOffset() {
		return true;
	}

	/**
	 * The total number of items to process during the upgrade
	 *
	 * If unknown, Batch::UNKNOWN_COUNT should be returned, and run() must manually mark the result
	 * as complete.
	 *
	 * @return int
	 */
	public function countItems() {
		$count = elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'notification',
			'count' => true,
		]);

		return $count;
	}

	/**
	 * Runs upgrade on a single batch of items
	 *
	 * If countItems() returns Batch::UNKNOWN_COUNT, this method must call $result->markCompleted()
	 * when the upgrade is complete.
	 *
	 * @param Result $result Result of the batch (this must be returned)
	 * @param int    $offset Number to skip when processing
	 *
	 * @return Result Instance of \Elgg\Upgrade\Result
	 */
	public function run(Result $result, $offset) {

		$entities = elgg_get_entities([
			'types' => 'object',
			'subtypes' => 'notification',
			'offset' => $offset,
		]);

		foreach ($entities as $entity) {
			$objects = $entity->getEntitiesFromRelationship(['relationship' => 'hasObject']);
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