# hypeNotifications plugin architecture (Elgg 5.x)

Provides enhanced on-site and off-site notification functionality for Elgg 5.x, including
Facebook-style site notifications, configurable email digest scheduling, multiple email
transport backends (SMTP, SendGrid, Mailgun, SparkPost), and admin tools for managing
notification methods.

**Elgg version:** 5.x (PHP 8.2+). See CHANGELOG.md for the 4.x→5.x migration summary.

## Layout

```
hypenotifications/
├── composer.json                    Plugin metadata & dependencies
├── elgg-plugin.php                  Declarative events, actions, routes, upgrades
├── elgg-services.php                DI container service definitions
├── classes/hypeJunction/Notifications/
│   ├── Bootstrap.php                Plugin initialization & activation
│   ├── Notification.php             ElggData-based site notification entity
│   ├── DigestNotification.php       ElggData for queued digest notifications
│   ├── SiteNotificationsService.php Service wrapper for site notification operations
│   ├── SiteNotificationsTable.php   DB table wrapper (select/insert/update/delete)
│   ├── DigestService.php            Digest scheduling & preference management
│   ├── DigestTable.php              DB table wrapper for digest queue
│   ├── EmailTransport.php           Factory for email transport backends
│   ├── SendGridEmailTransport.php   SendGrid API transport
│   ├── MailgunEmailTransport.php    Mailgun API transport
│   ├── SparkPostEmailTransport.php  SparkPost API transport
│   ├── EmailWhitelist.php           Email domain/address whitelist for testing
│   ├── SendSiteNotification.php     Hook: send:notification:site
│   ├── ScheduleDigest.php           Hook: send:all (batch digests or send immediately)
│   ├── SendDigest.php               Hook: cron:hourly (process scheduled digests)
│   ├── FormatEmailNotification.php  Hook: format:notification:email
│   ├── PrepareEmail.php             Hook: prepare:system:email
│   ├── ValidateEmail.php            Hook: validate:system:email
│   ├── AddHtmlEmailPart.php         Hook: zend:message:system:email
│   ├── SetClientConfig.php          Hook: elgg.data:site (client-side config)
│   ├── TopbarMenu.php               Hook: register:menu:topbar
│   ├── PageMenu.php                 Hook: register:menu:page
│   ├── DismissObjectNotifications.php Hook: view:object/* & post/elements/full
│   ├── DismissProfileNotifications.php Hook: view:profile/details & groups/profile/layout
│   ├── SyncEntityUpdate.php         Hook: update:all (sync entity changes)
│   ├── SyncEntityDelete.php         Hook: delete:all (clean up orphaned notifications)
│   ├── SyncNewUser.php              Hook: create:user
│   ├── SyncNewMember.php            Hook: create:relationship
│   ├── SystemEmailEvent.php         Event wrapper for email system
│   └── Upgrades/
│       └── MigratePluginId.php      Upgrade: migrate settings from 3.x plugin ID
├── actions/
│   ├── admin/notifications/
│   │   ├── methods.php              Admin action: update notification methods
│   │   └── test_email.php           Admin action: send test email
│   ├── notifications/
│   │   ├── mark_read.php            User action: mark notification as read
│   │   ├── mark_all_read.php        User action: mark all as read
│   │   └── settings/
│   │       └── digest.php           User action: save digest preferences
│   └── hypeNotifications/settings/
│       └── save.php                 Admin action: save plugin settings
├── views/default/
│   ├── notifications/
│   │   ├── popup.php                Popup display template
│   │   ├── notification.php         Single notification template
│   │   ├── listing.php              Notification list wrapper
│   │   ├── list.php                 Notification list items
│   │   ├── digest.php               Digest email template
│   │   └── stylesheet.css           Plugin styles
│   ├── resources/notifications/
│   │   ├── all.php                  Notifications listing page
│   │   └── view.php                 Single notification view page
│   ├── forms/notifications/         User preference forms
│   ├── forms/admin/notifications/   Admin settings forms
│   ├── plugins/hypenotifications/
│   │   └── settings.php             Plugin admin settings form
│   └── page/elements/               Topbar popup extension
├── install/
│   └── mysql.sql                    Database schema (site_notifications, digest)
├── languages/
│   └── en.php                       English translations
├── lib/
│   └── functions.php                Utility functions
├── tests/
│   ├── bootstrap.php                Test setup
│   └── phpunit/integration/
│       ├── NotificationTest.php
│       ├── PluginRegistrationTest.php
│       ├── SendSiteNotificationHookTest.php
│       └── SiteNotificationsTableTest.php
└── docker/                          Docker test environment
```

## Registered events (elgg-plugin.php)

| Event | Identifier | Handler |
|------|------------|---------|
| event | send:all | ScheduleDigest |
| event | send:notification:site | SendSiteNotification (priority 400) |
| event | cron:hourly | SendDigest |
| event | view:profile/details | DismissProfileNotifications |
| event | view:groups/profile/layout | DismissProfileNotifications |
| event | view:object/default | DismissObjectNotifications |
| event | view:post/elements/full | DismissObjectNotifications |
| event | elgg.data:site | SetClientConfig |
| event | format:notification:email | FormatEmailNotification (priority 999) |
| event | prepare:system:email | PrepareEmail (priority 999) |
| event | validate:system:email | ValidateEmail |
| event | zend:message:system:email | AddHtmlEmailPart |
| event | register:menu:topbar | TopbarMenu |
| event | register:menu:page | PageMenu |
| event | update:all | SyncEntityUpdate (priority 999) |
| event | delete:all | SyncEntityDelete (priority 999) |
| event | create:user | SyncNewUser |
| event | create:relationship | SyncNewMember |

Additional registrations in `Bootstrap::init()`:
- `elgg_register_notification_method('site')` — registers site notification channel
- Dynamic `view:object/{subtype}` event handlers were removed in 5.x (object/default handles the common case)
- Email transport via DI container (`elgg-services.php`)

## Routes

| Route name | Path | Resource |
|------------|------|----------|
| collection:notification:owner | /notifications/all/{username?} | notifications/all |
| view:notification | /notifications/view/{id} | notifications/view |
| settings:notification:digest | /notifications/settings/digest/{username?} | notifications/settings/digest |
| ajax:notifications:ticker | /notifications/ticker | notifications/ticker |

## Actions

| Path | Access | Handler |
|------|--------|---------|
| admin/notifications/methods | admin | methods.php |
| admin/notifications/test_email | admin | test_email.php |
| notifications/mark_all_read | user | mark_all_read.php |
| notifications/mark_read | user | mark_read.php |
| notifications/settings/digest | user | digest.php |
| hypeNotifications/settings/save | admin | save.php |

## Entities (custom ElggData tables)

Two custom ElggData-based entities (not ElggEntity subclasses — stored in plugin-owned tables):

### Notification (site_notifications table)
Properties: `recipient_guid`, `actor_guid`, `action`, `object_type`, `object_subtype`,
`object_id`, `time_created`, `time_seen`, `time_read`, `access_*`, `data` (JSON).

### DigestNotification (digest table)
Properties: `recipient_guid`, `time_created`, `time_scheduled`, `data` (JSON).

## External dependencies

| Package | Version | Purpose |
|---------|---------|---------|
| laminas/laminas-mail | ^2.10 | Email library (replaces Zend_Mail) |
| laminas/laminas-servicemanager | ^3.4 | DI container |
| pelago/emogrifier | ~1.0 | CSS inlining for email clients |
| sendgrid/sendgrid | ^6.0 | SendGrid API transport |
| guzzlehttp/guzzle | ^6.3 | HTTP client |
| php-http/guzzle6-adapter | ^1.1 | PSR-18 HTTP adapter |
| sparkpost/sparkpost | ^2.1 | SparkPost API transport |
| mailgun/mailgun-php | ^2.4 | Mailgun API transport |

## Migration notes (3.x → 4.x)

1. **Plugin ID lowercasing** — `hypeNotifications` → `hypenotifications`. `MigratePluginId`
   upgrade (version 2026041701) copies all settings from the orphaned 3.x plugin entity to the
   4.x entity on first activation.

2. **Email transport** — replaced Zend_Mail with Laminas\Mail. Transport configuration moved
   to `elgg-services.php` DI. SendGrid/Mailgun/SparkPost adapters refactored for Laminas.

3. **Declarative config** — hooks, actions, routes, view extensions moved from PHP registration
   to `elgg-plugin.php`. All hook handlers implement `__invoke`.

4. **Notification event system** — refactored to use Elgg 4.x `NotificationEvent` and
   `InstantNotificationEvent`. DigestService filters via core subscription registry.

5. **Without MigratePluginId upgrade** — plugin settings (transport type, API keys, test
   whitelist) are lost and notifications default to unstyled plain text.

## Seeding

No seeder required. This plugin owns no entity types, subtypes, or persistent relationship schemas — it is a pure UI/utility/admin plugin with no persisted entity surface of its own.
