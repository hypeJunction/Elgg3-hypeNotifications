<?php

namespace hypeJunction\Notifications;

use Elgg\Notifications\Notification as NotificationInstance;
use Elgg\Notifications\NotificationEvent;
use ElggData;
use ElggEntity;
use ElggExtender;
use ElggRelationship;

/**
 * @access private
 */
class SiteNotificationsService {

	/**
	 * @var SiteNotificationsTable
	 */
	private $table;

	/**
	 * Constructor 
	 * @param SiteNotificationsTable $table DB table
	 */
	public function __construct(SiteNotificationsTable $table) {
		$this->table = $table;
	}

	/**
	 * Returns DB table
	 * @return SiteNotificationsTable
	 */
	public function getTable() {
		return $this->table;
	}
}
