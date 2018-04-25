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
					$value = unserialize($value);
				}
				$this->set($key, $value);
			}
		}
	}

	/**
	 * {@inheritdoc}
	 */
	public function save() {
		$id = $this->id;

		$table = elgg()->{'db.digest'};
		/* @var $table DigestTable */

		if (!$id) {
			$this->set('time_created', time());
			return $table->insert($this);
		} else {
			return $table->update($this);
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

	public function setRecipient(ElggEntity $recipient) {
		if ($this->id) {
			throw new LogicException('Can not change the recipient of the notification once it is saved');
		}
		$this->set('recipient_guid', (int) $recipient->guid);
	}

	public function getRecipient() {
		return get_entity($this->recipient_guid);
	}

	public function setData($data) {
		$this->set('data', $data);
	}

	public function getData() {
		return $this->data;
	}

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
	public function delete() {
		$table = elgg()->{'db.digest'};
		/* @var $table DigestTable */

		return $table->delete($this->id);
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
		return array_key($this->attributes);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getObjectFromID($id) {
		$svc = DigestNotification::getInstance();
		$svc->getTable()->get($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubtype() {
		return 'digest';
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSystemLogID() {
		return $this->id;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getType() {
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
	public function getURL() {
		return false;
	}
}
