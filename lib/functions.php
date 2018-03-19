<?php

use hypeJunction\Notifications\Notification;
use hypeJunction\Notifications\SiteNotificationsService;

/**
 * Returns user notifications
 *
 * @param array $options Options:
 *                       - limit
 *                       - offset
 *                       - status (read|seen|unread|unseen|all)
 *                       - recipient_guid
 *
 * @return Notification[]|false
 * @throws DatabaseException
 */
function hypeapps_get_notifications(array $options = []) {
	if (!isset($options['recipient_guid'])) {
		$options['recipient_guid'] = elgg_get_logged_in_user_guid();
	}

	$svc = elgg()->{'notifications.site'};

	/* @var $svc SiteNotificationsService */

	return $svc->getTable()->getAll($options);
}

/**
 * Count user notifications
 *
 * @param array $options Options:
 *                       - status (read|seen|unread|unseen|all)
 *                       - recipient_guid
 *
 * @return int
 * @throws DatabaseException
 */
function hypeapps_count_notifications(array $options = []) {
	if (!isset($options['recipient_guid'])) {
		$options['recipient_guid'] = elgg_get_logged_in_user_guid();
	}

	$svc = elgg()->{'notifications.site'};

	/* @var $svc SiteNotificationsService */

	return $svc->getTable()->count($options);
}

/**
 * Load notification by its ID
 *
 * @param int $id ID
 *
 * @return Notification|false
 * @throws DatabaseException
 */
function hypeapps_get_notification_by_id($id) {
	$svc = elgg()->{'notifications.site'};

	/* @var $svc SiteNotificationsService */

	return $svc->getTable()->get($id) ? : false;
}

/**
 * Mark all notification as read for recipient
 *
 * @param int $recipient_guid Recipient guid
 *
 * @return bool
 * @throws DatabaseException
 */
function hypeapps_mark_all_notifications_read($recipient_guid) {
	$svc = elgg()->{'notifications.site'};

	/* @var $svc SiteNotificationsService */

	return $svc->getTable()->markAllRead($recipient_guid);
}
