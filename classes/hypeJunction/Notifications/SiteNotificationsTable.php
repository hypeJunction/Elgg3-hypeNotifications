<?php

namespace hypeJunction\Notifications;

use Elgg\Application\Database;
use Elgg\Database\Clauses\AccessWhereClause;
use Elgg\Database\Delete;
use Elgg\Database\Insert;
use Elgg\Database\Select;
use Elgg\Database\Update;
use Elgg\TimeUsing;
use ElggData;
use ElggEntity;
use ElggExtender;
use stdClass;

/**
 * @access private
 */
class SiteNotificationsTable {

	use TimeUsing;

	/**
	 * @var Database
	 */
	protected $db;

	/**
	 * @var callable
	 */
	private $row_callback;

	/**
	 * Constructor
	 */
	public function __construct(Database $db) {
		$this->db = $db;
		$this->row_callback = [$this, 'rowToNotification'];
	}

	/**
	 * Convert DB row to an instance of Notification
	 *
	 * @param stdClass $row DB row
	 *
	 * @return Notification
	 */
	public function rowToNotification(stdClass $row) {
		return new Notification($row);
	}

	/**
	 * Get notification by its ID
	 *
	 * @param int $id ID
	 *
	 * @return Notification|false
	 * @throws \DatabaseException
	 */
	public function get($id) {
		$qb = Select::fromTable('site_notifications');
		$qb->select('*')
			->where($qb->compare('id', '=', $id, ELGG_VALUE_INTEGER));

		return $this->db->getDataRow($qb, $this->row_callback);
	}

	/**
	 * Get user notifications
	 *
	 * @param array $options Options
	 *
	 * @return Notification[]|false|int
	 * @throws \DatabaseException
	 */
	public function getAll(array $options = []) {

		$recipient_guid = elgg_extract('recipient_guid', $options);
		$limit = (int) elgg_extract('limit', $options, 25);
		$offset = (int) elgg_extract('offset', $options, 0);
		$status = elgg_extract('status', $options);
		$count = elgg_extract('count', $options);

		$qb = Select::fromTable('site_notifications', 'nt');
		$qb->where($qb->compare('nt.recipient_guid', '=', $recipient_guid, ELGG_VALUE_GUID));

		$access = new AccessWhereClause();
		$access->guid_column = 'access_guid';
		$access->owner_guid_column = 'access_owner_guid';
		$access->use_enabled_clause = false;

		$qb->andWhere($access->prepare($qb, 'nt'));

		switch ($status) {
			case 'read' :
				$qb->andWhere($qb->compare('nt.time_read', 'IS NOT NULL'));
				break;
			case 'unread' :
				$qb->andWhere($qb->merge([
					$qb->compare('nt.time_read', 'IS NULL'),
					$qb->compare('nt.time_read', '=', 0, ELGG_VALUE_INTEGER),
				], 'OR'));
				break;
			case 'seen' :
				$qb->andWhere($qb->compare('nt.time_seen', 'IS NOT NULL'));
				break;
			case 'unseen' :
				$qb->andWhere($qb->merge([
					$qb->compare('nt.time_seen', 'IS NULL'),
					$qb->compare('nt.time_seen', '=', 0, ELGG_VALUE_INTEGER),
				], 'OR'));
				break;
		}

		if ($count) {
			$qb->select('COUNT(DISTINCT nt.id) as total');

			$row = $this->db->getDataRow($qb);
			if ($row) {
				return (int) $row->total;
			}

			return 0;
		} else {
			$qb->select('nt.*');

			$qb->orderBy('nt.time_created', 'desc');

			$qb->setMaxResults($limit);
			$qb->setFirstResult($offset);

			return $this->db->getData($qb, $this->row_callback);
		}
	}

	/**
	 * Count user notifications
	 *
	 * @param array $options
	 *
	 * @return int
	 * @throws \DatabaseException
	 */
	public function count(array $options = []) {

		$options['count'] = true;

		return $this->getAll($options);
	}

	/**
	 * Insert row
	 *
	 * @param Notification $notification Notification
	 *
	 * @return int|false
	 * @throws \DatabaseException
	 */
	public function insert(Notification $notification) {

		$qb = Insert::intoTable('site_notifications');
		$qb->values([
			'recipient_guid' => $qb->param($notification->recipient_guid, ELGG_VALUE_INTEGER),
			'actor_guid' => $qb->param($notification->actor_guid, ELGG_VALUE_INTEGER),
			'object_id' => $qb->param($notification->object_id, ELGG_VALUE_INTEGER),
			'object_type' => $qb->param($notification->object_type, ELGG_VALUE_STRING),
			'object_subtype' => $qb->param($notification->object_subtype, ELGG_VALUE_STRING),
			'action' => $qb->param($notification->action, ELGG_VALUE_STRING),
			'time_created' => $qb->param($notification->time_created, ELGG_VALUE_INTEGER),
			'time_seen' => $qb->param($notification->time_seen, ELGG_VALUE_INTEGER),
			'time_read' => $qb->param($notification->time_read, ELGG_VALUE_INTEGER),
			'access_guid' => $qb->param($notification->access_guid, ELGG_VALUE_INTEGER),
			'access_owner_guid' => $qb->param($notification->access_owner_guid, ELGG_VALUE_INTEGER),
			'access_id' => $qb->param($notification->access_id, ELGG_VALUE_INTEGER),
			'data' => $qb->param(serialize($notification->data), ELGG_VALUE_STRING),
		]);

		return $this->db->insertData($qb);
	}

	/**
	 * Update database row
	 *
	 * @param Notification $notification Notification
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function update(Notification $notification) {

		$qb = Update::table('site_notifications');
		$qb->set('access_guid', $qb->param($notification->access_guid, ELGG_VALUE_INTEGER))
			->set('access_owner_guid', $qb->param($notification->access_owner_guid, ELGG_VALUE_INTEGER))
			->set('access_id', $qb->param($notification->access_id, ELGG_VALUE_INTEGER))
			->set('time_seen', $qb->param($notification->time_seen, ELGG_VALUE_INTEGER))
			->set('time_read', $qb->param($notification->time_read, ELGG_VALUE_INTEGER))
			->where($qb->compare('id', '=', $notification->id, ELGG_VALUE_INTEGER));

		return $this->db->updateData($qb);
	}

	/**
	 * Update database row
	 *
	 * @param ElggData $object Object
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function updateAccess(ElggData $object) {

		if ($object instanceof ElggEntity) {
			$object_id = $object->guid;
			$object_type = $object->getType();
			$access_guid = $object->guid;
			$access_owner_guid = $object->owner_guid;
			$access_id = $object->access_id;
		} else if ($object instanceof ElggExtender) {
			$object_id = $object->id;
			$object_type = $object->getType();
			$access_guid = $object->entity_guid;
			$access_owner_guid = $object->owner_guid;
			$access_id = $object->access_id;
		} else {
			return true;
		}

		$qb = Update::table('site_notifications');
		$qb->set('access_guid', $qb->param($access_guid, ELGG_VALUE_INTEGER))
			->set('access_owner_guid', $qb->param($access_owner_guid, ELGG_VALUE_INTEGER))
			->set('access_id', $qb->param($access_id, ELGG_VALUE_INTEGER))
			->where($qb->compare('object_id', '=', $object_id, ELGG_VALUE_INTEGER))
			->andWhere($qb->compare('object_type', '=', $object_type, ELGG_VALUE_STRING));

		return $this->db->updateData($qb);
	}

	/**
	 * Delete row
	 *
	 * @param int $id ID
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function delete($id) {

		$qb = Delete::fromTable('site_notifications');
		$qb->where($qb->compare('id', '=', $id, ELGG_VALUE_INTEGER));

		return $this->db->deleteData($qb);
	}

	/**
	 * Delete rows by recipient, actor, object guids
	 *
	 * @param int $guid GUID
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function deleteByEntityGUID($guid) {

		$qb = Delete::fromTable('site_notifications');
		$qb->where(
			$qb->merge([
				$qb->compare('recipient_guid', '=', $guid, ELGG_VALUE_INTEGER),
				$qb->compare('actor_guid', '=', $guid, ELGG_VALUE_INTEGER),
				$qb->merge([
					$qb->compare('object_id', '=', $guid, ELGG_VALUE_INTEGER),
					// @todo: Oddly, using IN with an array fatals here. PDO with delete issue?
					$qb->compare('object_type', 'IN', "'object', 'user', 'site', 'group'"),
				], 'AND')
			], 'OR')
		);

		return $this->db->deleteData($qb);
	}

	/**
	 * Delete rows by object id
	 *
	 * @param int    $id   Extender or relationship ID
	 * @param string $type Object type
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function deleteByExtenderID($id, $type) {

		$qb = Delete::fromTable('site_notifications');
		$qb->where($qb->compare('object_id', '=', $id, ELGG_VALUE_INTEGER))
			->andWhere($qb->compare('object_type', '=', $type, ELGG_VALUE_STRING));

		return $this->db->deleteData($qb);
	}

	/**
	 * Mark all notifications read
	 *
	 * @param int $recipient_guid Recipient GUID
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function markAllRead($recipient_guid) {

		$time = $this->getCurrentTime()->getTimestamp();

		$qb = Update::table('site_notifications');
		$qb->set('time_seen', $qb->param($time, ELGG_VALUE_INTEGER))
			->set('time_read', $qb->param($time, ELGG_VALUE_INTEGER))
			->where($qb->compare('recipient_guid', '=', $recipient_guid, ELGG_VALUE_INTEGER));

		return $this->db->updateData($qb);
	}

	/**
	 * Mark notifications about an entity as read
	 *
	 * @param int $guid           GUID
	 * @param int $recipient_guid Recipient GUID (defaults to logged in user)
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function markReadByEntityGUID($guid, $recipient_guid = null) {

		if (!isset($recipient_guid)) {
			$recipient_guid = elgg_get_logged_in_user_guid();
		}

		$time = $this->getCurrentTime()->getTimestamp();

		$qb = Update::table('site_notifications');
		$qb->set('time_seen', $qb->param($time, ELGG_VALUE_INTEGER))
			->set('time_read', $qb->param($time, ELGG_VALUE_INTEGER))
			->where($qb->compare('object_id', '=', $guid, ELGG_VALUE_INTEGER))
			->where($qb->compare('object_type', 'IN', ['object', 'user', 'site', 'group'], ELGG_VALUE_STRING))
			->where($qb->compare('recipient_guid', '=', $recipient_guid, ELGG_VALUE_INTEGER));

		return $this->db->updateData($qb);

	}

	/**
	 * Mark notifications about an entity as read
	 *
	 * @param int    $id             Object id
	 * @param string $type           Object type
	 * @param int    $recipient_guid Recipient GUID (defaults to logged in user)
	 *
	 * @return bool
	 * @throws \DatabaseException
	 */
	public function markReadByExtenderID($id, $type, $recipient_guid = null) {

		if (!isset($recipient_guid)) {
			$recipient_guid = elgg_get_logged_in_user_guid();
		}

		$time = $this->getCurrentTime()->getTimestamp();

		$qb = Update::table('site_notifications');
		$qb->set('time_seen', $qb->param($time, ELGG_VALUE_INTEGER))
			->set('time_read', $qb->param($time, ELGG_VALUE_INTEGER))
			->where($qb->compare('object_id', '=', $id, ELGG_VALUE_INTEGER))
			->where($qb->compare('object_type', '=', $type, ELGG_VALUE_STRING))
			->where($qb->compare('recipient_guid', '=', $recipient_guid, ELGG_VALUE_INTEGER));

		return $this->db->updateData($qb);
	}

}
