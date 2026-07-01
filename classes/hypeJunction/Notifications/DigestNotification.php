<?php

namespace hypeJunction\Notifications;

use ElggData;
use ElggEntity;
use LogicException;
use stdClass;

/**
 * @access private
 *
 * @property-read int    $recipient_guid
 * @property-read int    $id
 * @property-read int    $time_created
 * @property-read int    $time_scheduled
 * @property-read mixed  $data
 */
class DigestNotification extends ElggData {

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['type'] = 'notification';
		$this->attributes['recipient_guid'] = 0;
		$this->attributes['id'] = ELGG_ENTITIES_ANY_VALUE;
		$this->attributes['time_created'] = ELGG_ENTITIES_ANY_VALUE;
		$this->attributes['time_scheduled'] = ELGG_ENTITIES_ANY_VALUE;
		$this->attributes['data'] = [];
	}

	/**
	 * Constructor
	 *
	 * @param stdClass $row DB row
	 */
	public function __construct(stdClass $row = null) {
		$this->initializeAttributes();
		if ($row instanceof stdClass) {
			foreach ($row as $key => $value) {
				if ($key == 'data' && !empty($value)) {
					// Restrict unserialize to scalar values only to prevent object injection
					$value = unserialize($value, ['allowed_classes' => false]);
				}

				$this->set($key, $value);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function save(): bool {
		$id = $this->id;

		$table = elgg()->{'db.digest'};
		/* @var $table DigestTable */

		if (!$id) {
			$this->set('time_created', time());
			return (bool) $table->insert($this);
		} else {
			return (bool) $table->update($this);
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function __get($name) {
		return $this->get($name);
	}

	/**
	 * {@inheritdoc}
	 */
	protected function get($name) {
		if (array_key_exists($name, $this->attributes)) {
			return $this->attributes[$name];
		}

		return $this->$name;
	}

	/**
	 * {@inheritdoc}
	 */
	protected function set($name, $value) {
		if (array_key_exists($name, $this->attributes)) {
			$this->attributes[$name] = $value;
			return;
		}

		$this->$name = $value;
	}

	/**
	 * Set the recipient of the digest notification
	 *
	 * @param ElggEntity $recipient Recipient entity
	 * @return void
	 * @throws LogicException
	 */
	public function setRecipient(ElggEntity $recipient) {
		if ($this->id) {
			throw new LogicException('Can not change the recipient of the notification once it is saved');
		}

		$this->set('recipient_guid', (int) $recipient->guid);
	}

	/**
	 * Get the recipient entity
	 *
	 * @return \ElggEntity|false
	 */
	public function getRecipient() {
		return $this->recipient_guid ? get_entity((int) $this->recipient_guid) : false;
	}

	/**
	 * Set notification data payload
	 *
	 * @param mixed $data Notification data
	 * @return void
	 */
	public function setData($data) {
		$this->set('data', $data);
	}

	/**
	 * Get notification data payload
	 *
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Set scheduled delivery timestamp
	 *
	 * @param int|null $timestamp Unix timestamp; defaults to now
	 * @return void
	 */
	public function setTimeScheduled($timestamp = null) {
		if (!isset($timestamp)) {
			$timestamp = time();
		}

		$this->set('time_scheduled', $timestamp);
		if ($this->id) {
			$this->save();
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete(): bool {
		$table = elgg()->{'db.digest'};
		/* @var $table DigestTable */

		return (bool) $table->delete($this->id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function export() {
		return $this->toObject();
	}

	/**
	 * {@inheritdoc}
	 */
	public function getExportableValues() {
		return array_keys($this->attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getObjectFromID(int $id): mixed {
		$svc = elgg()->{'notifications.digest'};
		/* @var $svc DigestService */

		return $svc->getTable()->get($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubtype(): string {
		return 'digest';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSystemLogID(): int {
		return (int) $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType(): string {
		return 'notification';
	}

	/**
	 * {@inheritdoc}
	 */
	public function toObject(array $params = []) {
		return (object) $this->attributes;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getURL(): string {
		return '';
	}
}
