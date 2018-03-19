<?php

namespace hypeJunction\Notifications;

use ElggData;
use ElggEntity;
use ElggExtender;
use ElggRelationship;
use LogicException;
use stdClass;

/**
 * @access private
 *
 * @property-read int    $recipient_guid
 * @property-read int    $id
 * @property-read string $action
 * @property-read string $object_type
 * @property-read string $object_subtype
 * @property-read int    $object_id
 * @property-read int    $actor_guid
 * @property-read int    $time_created
 * @property-read int    $time_seen
 * @property-read int    $time_read
 * @property-read int    $access_guid
 * @property-read int    $access_owner_guid
 * @property-read int    $access_id
 * @property-read mixed  $data
 */
class Notification extends ElggData {

	/**
	 * {@inheritdoc}
	 */
	protected function initializeAttributes() {
		parent::initializeAttributes();
		$this->attributes['type'] = 'notification';
		$this->attributes['recipient_guid'] = 0;
		$this->attributes['id'] = ELGG_ENTITIES_ANY_VALUE;
		$this->attributes['action'] = '';
		$this->attributes['object_type'] = '';
		$this->attributes['object_subtype'] = '';
		$this->attributes['object_id'] = 0;
		$this->attributes['actor_guid'] = 0;
		$this->attributes['time_created'] = ELGG_ENTITIES_ANY_VALUE;
		$this->attributes['time_seen'] = ELGG_ENTITIES_ANY_VALUE;
		$this->attributes['time_read'] = ELGG_ENTITIES_ANY_VALUE;
		$this->attributes['access_owner_guid'] = 0;
		$this->attributes['access_guid'] = 0;
		$this->attributes['access_id'] = ACCESS_PRIVATE;
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

		$svc = elgg()->{'notifications.site'};
		/* @var $svc SiteNotificationsService */
		if (!$id) {
			if (!$this->get('time_created')) {
				$this->set('time_created', time());
			}

			return $svc->getTable()->insert($this);
		} else {
			return $svc->getTable()->update($this);
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
	 * Set the recipient
	 *
	 * @param ElggEntity $recipient Recipient
	 *
	 * @return void
	 * @throws LogicException
	 */
	public function setRecipient(ElggEntity $recipient = null) {
		if ($this->id) {
			throw new LogicException('Can not change the recipient of the notification once it is saved');
		}
		$this->set('recipient_guid', (int)$recipient->guid);
		if (null == $this->access_guid) {
			$this->set('access_guid', (int)$recipient->guid);
			$this->set('access_owner_guid', (int)$recipient->guid);
			$this->set('access_id', ACCESS_PRIVATE);
		}
	}

	/**
	 * Get the recipient
	 *
	 * @return ElggEntity|false
	 */
	public function getRecipient() {
		return get_entity($this->recipient_guid);
	}

	/**
	 * Set actor
	 *
	 * @param ElggEntity $actor Actor
	 *
	 * @return void
	 * @throws LogicException
	 */
	public function setActor(ElggEntity $actor = null) {
		if ($this->id) {
			throw new LogicException('Can not change the actor of the notification once it is saved');
		}
		if (!isset($actor)) {
			$this->set('actor_guid', null);

			return;
		}
		$this->set('actor_guid', (int)$actor->guid);
	}

	/**
	 * Get the actor
	 *
	 * @return ElggEntity|false
	 */
	public function getActor() {
		return get_entity($this->actor_guid);
	}

	/**
	 * Set action
	 *
	 * @param string $action Action name
	 *
	 * @return void
	 * @throws LogicException
	 */
	public function setAction($action = '') {
		if ($this->id) {
			throw new LogicException('Can not change the action of the notification once it is saved');
		}
		$this->set('action', (string)$action);
	}

	/**
	 * Set the object
	 *
	 * @param ElggData $object Object
	 *
	 * @return void
	 * @throws LogicException
	 */
	public function setObject(ElggData $object = null) {
		if ($this->id) {
			throw new LogicException('Can not change the object of the notification once it is saved');
		}
		if ($object instanceof ElggEntity) {
			$this->set('object_id', $object->guid);
		} else if ($object instanceof ElggData) {
			$this->set('object_id', $object->id);
		}

		if ($object instanceof ElggData) {
			$this->set('object_type', (string)$object->getType());
			$this->set('object_subtype', (string)$object->getSubtype());
		}

		while ($object instanceof \ElggComment) {
			// Special case, because the access id owner is the owner of the original item
			$object = $object->getContainerEntity();
		}

		if ($object instanceof ElggEntity) {
			$this->set('access_guid', $object->guid);
			$this->set('access_owner_guid', $object->owner_guid);
			$this->set('access_id', $object->access_id);
		} else if ($object instanceof ElggExtender) {
			$this->set('access_guid', $object->entity_guid);
			$this->set('access_owner_guid', $object->owner_guid);
			$this->set('access_id', $object->access_id);
		}
	}

	/**
	 * Get the object
	 * @return ElggEntity|ElggExtender|ElggRelationship
	 */
	public function getObject() {
		switch ($this->object_type) {
			case 'object' :
			case 'user' :
			case 'group' :
			case 'site' :
				return get_entity($this->object_id);
			case 'annotation' :
				return elgg_get_annotation_from_id($this->object_id);
			case 'metadata' :
				return elgg_get_metadata_from_id($this->object_id);
			case 'relationship' :
				return get_relationship($this->object_id);
		}
	}

	/**
	 * Set extra data
	 *
	 * @param mixed $data Data
	 *
	 * @return void
	 */
	public function setData($data = null) {
		$this->set('data', $data);
	}

	/**
	 * Get the extra data
	 * @return mixed
	 */
	public function getData() {
		return $this->data;
	}

	/**
	 * Mark notification as seen
	 *
	 * @param int $timestamp Time seen
	 *
	 * @return void
	 */
	public function markAsSeen($timestamp = null) {
		if (!isset($timestamp)) {
			$timestamp = time();
		}
		$this->set('time_seen', $timestamp);
		if ($this->id) {
			$this->save();
		}
	}

	/**
	 * Was the notification seen?
	 * @return bool
	 */
	public function isSeen() {
		return $this->time_seen > 0;
	}

	/**
	 * Mark notification as read
	 *
	 * @param int $timestamp Time read
	 *
	 * @return void
	 */
	public function markAsRead($timestamp = null) {
		if (!isset($timestamp)) {
			$timestamp = time();
		}
		$this->set('time_read', $timestamp);
		if ($this->id) {
			$this->save();
		}
	}

	/**
	 * Was the notification read?
	 * @return bool
	 */
	public function isRead() {
		return $this->time_read > 0;
	}

	/**
	 * {@inheritdoc}
	 */
	public function delete() {
		$svc = elgg()->{'notifications.site'};
		/* @var $svc SiteNotificationsService */

		return $svc->getTable()->delete($this->id);
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
	public function getObjectFromID($id) {
		$svc = elgg()->{'notifications.site'};
		/* @var $svc SiteNotificationsService */

		$svc->getTable()->get($id);
	}

	/**
	 * {@inheritdoc}
	 */
	public function getSubtype() {
		return implode(':', array_filter([
			$this->action,
			$this->object_type,
			$this->object_subtype,
		]));
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
		return $this->type;
	}

	/**
	 * {@inheritdoc}
	 */
	public function getURL() {
		$id = $this->id;
		if (!$id) {
			return false;
		}

		return elgg_generate_url('view:notification', [
			'id' => $id,
		]);
	}

	/**
	 * Get final target URL
	 * @return string
	 */
	public function getTargetURL() {

		if (isset($this->data['url'])) {
			$url = $this->data['url'];
		} else {
			$url = false;
			$object = $this->getObject();
			if ($object instanceof ElggEntity) {
				$url = $object->getURL();
			}
		}

		$url = elgg_normalize_url($url);
		if ($url == elgg_get_site_url()) {
			$url = false;
		}

		$params = [
			'notification' => $this,
		];

		return elgg_trigger_plugin_hook('target:url', 'notification', $params, $url);
	}

	/**
	 * {@inheritdoc}
	 */
	public function toObject() {
		return (object)$this->attributes;
	}

	/**
	 * Get notification content
	 * @return string
	 */
	public function getBody() {

		$actor = $this->getActor();
		$object = $this->getObject();

		$action = $this->action;
		$object_type = $this->object_type;
		$object_subtype = $this->object_subtype;

		$keys = [
			"notification:body:$action:$object_type:$object_subtype",
			"notification:body:$action:$object_type:default",
			"notification:body:$action:default",
		];

		$subject_link = '';
		if ($actor) {
			$subject_link = elgg_view('output/url', [
				'href' => $actor->getURL(),
				'text' => $actor->getDisplayName(),
			]);
		}
		$object_link = '';
		if ($object instanceof ElggExtender) {
			$object = $object->getEntity();
		}
		if ($object instanceof ElggEntity) {
			$object_link = elgg_view('output/url', [
				'href' => $object->getURL(),
				'text' => $object->getDisplayName(),
			]);
		}

		$summary = '';
		foreach ($keys as $key) {
			if (elgg_language_key_exists($key)) {
				$summary = elgg_echo($key, [$subject_link, $object_link]);
				break;
			}
		}

		if (!$summary) {
			$summary = $this->data['body'];
		}

		if (!$summary) {
			$summary = $this->data['summary'];
		}

		$params = [
			'notification' => $this,
		];

		return elgg_trigger_plugin_hook('format:body', 'notification', $params, $summary);
	}

	/**
	 * Get notification summary
	 * @return string
	 */
	public function getSummary() {

		$actor = $this->getActor();
		$object = $this->getObject();

		$action = $this->action;
		$object_type = $this->object_type;
		$object_subtype = $this->object_subtype;

		$keys = [
			"notification:summary:$action:$object_type:$object_subtype",
			"notification:summary:$action:$object_type:default",
			"notification:summary:$action:default",
		];

		$subject_link = '';
		if ($actor) {
			$subject_link = elgg_view('output/url', [
				'href' => $actor->getURL(),
				'text' => $actor->getDisplayName(),
			]);
		}
		$object_link = '';
		if ($object instanceof ElggExtender) {
			$object = $object->getEntity();
		}
		if ($object instanceof ElggEntity) {
			$object_link = elgg_view('output/url', [
				'href' => $object->getURL(),
				'text' => $object->getDisplayName(),
			]);
		}

		$summary = '';
		foreach ($keys as $key) {
			if (elgg_language_key_exists($key)) {
				$summary = elgg_echo($key, [$subject_link, $object_link]);
				break;
			}
		}

		if (!$summary) {
			$summary = $this->data['summary'] ?: $this->data['subject'];
			if (!preg_match_all('/<a.*\/a>/i', $summary)) {
				$summary = elgg_view('output/url', [
					'text' => $summary,
					'href' => $this->getURL(),
					'class' => 'notification-summary',
				]);
			}
		}

		$params = [
			'notification' => $this,
		];

		return elgg_trigger_plugin_hook('format:summary', 'notification', $params, $summary);
	}

}
