<?php

namespace hypeJunction\Notifications;

use Elgg\IntegrationTestCase;

/**
 * Verifies the plugin's elgg-plugin.php registrations are wired up:
 * service container entries, hook handlers, event handlers, routes,
 * actions, and the 'site' notification method. These tests catch
 * "the migration silently dropped a registration" regressions.
 */
class PluginRegistrationTest extends IntegrationTestCase {

	public function up() {}
	public function down() {}

	public function getPluginID(): string {
		return '';
	}

	public function testServicesAreRegistered(): void {
		$svc = \elgg()->{'notifications.site'};
		$this->assertInstanceOf(SiteNotificationsService::class, $svc);
		$this->assertInstanceOf(SiteNotificationsTable::class, $svc->getTable());

		$digest = \elgg()->{'notifications.digest'};
		$this->assertInstanceOf(DigestService::class, $digest);
	}

	public function testSiteNotificationMethodIsRegistered(): void {
		$methods = \_elgg_services()->notifications->getMethods();
		$this->assertContains('site', $methods, 'site notification method must be registered');
	}

	public function testRoutesResolve(): void {
		$routes = \_elgg_services()->routes;

		$this->assertNotNull($routes->get('collection:notification:owner'));
		$this->assertNotNull($routes->get('view:notification'));
		$this->assertNotNull($routes->get('settings:notification:digest'));
		$this->assertNotNull($routes->get('ajax:notifications:ticker'));

		// Generated URL must reflect the plugin's path
		$url = \elgg_generate_url('view:notification', ['id' => 42]);
		$this->assertStringContainsString('/notifications/view/42', $url);
	}

	public function testActionsAreRegistered(): void {
		$actions = \_elgg_services()->actions->getAllActions();
		$this->assertArrayHasKey('notifications/mark_read', $actions);
		$this->assertArrayHasKey('notifications/mark_all_read', $actions);
		$this->assertArrayHasKey('notifications/settings/digest', $actions);
		$this->assertArrayHasKey('admin/notifications/methods', $actions);
		$this->assertArrayHasKey('admin/notifications/test_email', $actions);
		$this->assertArrayHasKey('hypeNotifications/settings/save', $actions);
	}

	public function testSendSiteNotificationHookRegistered(): void {
		$registered = \_elgg_services()->hooks->hasHandler('send', 'notification:site');
		$this->assertTrue($registered, 'send/notification:site hook must be registered');
	}

	public function testFormatEmailHookRegistered(): void {
		$this->assertTrue(\_elgg_services()->hooks->hasHandler('format', 'notification:email'));
	}

	public function testEntitySyncEventsRegistered(): void {
		$this->assertTrue(\_elgg_services()->events->hasHandler('update', 'all'));
		$this->assertTrue(\_elgg_services()->events->hasHandler('delete', 'all'));
	}

	public function testHelperFunctionsExist(): void {
		// Loaded by lib/functions.php through the plugin bootstrap
		$libFile = dirname(__DIR__, 5) . '/lib/functions.php';
		if (file_exists($libFile) && !function_exists('hypeapps_get_notifications')) {
			require_once $libFile;
		}
		$this->assertTrue(function_exists('hypeapps_get_notifications'));
		$this->assertTrue(function_exists('hypeapps_count_notifications'));
		$this->assertTrue(function_exists('hypeapps_get_notification_by_id'));
		$this->assertTrue(function_exists('hypeapps_mark_all_notifications_read'));
	}
}
