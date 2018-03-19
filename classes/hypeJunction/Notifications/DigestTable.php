<?php

namespace hypeJunction\Notifications;

use DatabaseException;
use Elgg\Application\Database;
use Elgg\Database\Delete;
use Elgg\Database\Insert;
use Elgg\Database\Select;
use Elgg\Database\Update;
use stdClass;

/**
 * @access private
 */
class DigestTable {

	/**
	 * @var callable
	 */
	private $row_callback;

	/**
	 * @var Database
	 */
	protected $db;

	/**
	 * Constructor
	 */
	public function __construct(Database $database) {
		$this->db = $database;
		$this->row_callback = [$this, 'rowToNotification'];
	}

	/**
	 * Convert DB row to an instance of Notification
	 *
	 * @param stdClass $row DB row
	 *
	 * @return DigestNotification
	 */
	public function rowToNotification(stdClass $row) {
		return new DigestNotification($row);
	}

	/**
	 * Get notification by its ID
	 *
	 * @param int $id ID
	 *
	 * @return DigestNotification|false
	 * @throws DatabaseException
	 */
	public function get($id) {

		$qb = Select::fromTable('digest');
		$qb->select('*')
			->where($qb->compare('id', '=', $id, ELGG_VALUE_INTEGER));

		return $this->db->getDataRow($qb, $this->row_callback) ? : false;
	}

	/**
	 * Get user notifications
	 *
	 * @param array $options Options
	 *
	 * @return DigestNotification[]|false
	 * @throws DatabaseException
	 */
	public function getAll(array $options = []) {

		$recipient_guid = elgg_extract('recipient_guid', $options);
		$time_scheduled = elgg_extract('time_scheduled', $options, time());

		$qb = Select::fromTable('digest');
		$qb->select('*')
			->where($qb->compare('recipient_guid', '=', (int) $recipient_guid, ELGG_VALUE_INTEGER))
			->orderBy('time_created', 'asc')
			->setMaxResults(100);

		return $this->db->getData($qb, $this->row_callback);
	}

	/**
	 * Get recipients who have pending digest notifications
	 *
	 * @param array $options Options
	 *
	 * @return array
	 * @throws DatabaseException
	 */
	public function getRecipients(array $options = []) {

		$recipients = [];

		$time_scheduled = elgg_extract('time_scheduled', $options, time());

		$qb = Select::fromTable('digest');
		$qb->select('recipient_guid')
			->where($qb->compare('time_scheduled', '<=', $time_scheduled, ELGG_VALUE_INTEGER))
			->groupBy('recipient_guid');

		$data = $this->db->getData($qb);

		foreach ($data as $row) {
			$recipients[] = $row->recipient_guid;
		}

		return $recipients;
	}

	/**
	 * Insert row
	 *
	 * @param DigestNotification $notification Notification
	 *
	 * @return int|false
	 * @throws DatabaseException
	 */
	public function insert(DigestNotification $notification) {

		$qb = Insert::intoTable('digest');
		$qb->values([
			'recipient_guid' => $qb->param($notification->recipient_guid, ELGG_VALUE_INTEGER),
			'time_created' => $qb->param($notification->time_created, ELGG_VALUE_INTEGER),
			'time_scheduled' => $qb->param($notification->time_scheduled, ELGG_VALUE_INTEGER),
			'data' => $qb->param(serialize($notification->data), ELGG_VALUE_STRING),
		]);

		return $this->db->insertData($qb);
	}

	/**
	 * Update database row
	 *
	 * @param DigestNotification $notification Notification
	 *
	 * @return bool
	 * @throws DatabaseException
	 */
	public function update(DigestNotification $notification) {

		$qb = Update::table('digest');
		$qb->set('time_scheduled', $qb->param($notification->time_scheduled, ELGG_VALUE_INTEGER))
			->where($qb->compare('id', '=', $notification->id, ELGG_VALUE_INTEGER));

		return $this->db->updateData($qb);
	}

	/**
	 * Delete row
	 *
	 * @param int $id ID
	 *
	 * @return bool
	 * @throws DatabaseException
	 */
	public function delete($id) {

		$qb = Delete::fromTable('digest');
		$qb->where($qb->compare('id', '=', $id, ELGG_VALUE_INTEGER));

		return $this->db->deleteData($qb);
	}

}
