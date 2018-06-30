# hypeNotifications for Elgg

![Elgg 3.0](https://img.shields.io/badge/Elgg-3.0-orange.svg?style=flat-square)

![Popup](https://raw.github.com/hypeJunction/hypeNotifications/master/screenshots/popup.png "Popup")
![Digest](https://raw.github.com/hypeJunction/hypeNotifications/master/screenshots/digest.png "Email Digest")

## Features

 * Facebook-style site notifications
 * Email digest: users can specify at which interval they receive notifications for each type
 * A tool to update preferred notification methods for all site users
 * Leverages `Zend_Mail` (email library used in core) to send out HTML emails
 * Allows to configure email transports (Sendmail, SMTP, File Transport, SendGrid, Mailgun, SparkPost)
 * Allows to send file attachments
 * Inlines CSS styles for improved email client experience
 * Simpler testing experience: catch all email address, email/domain whitelist

## Usage

### Notification preferences

Go to Admin > Administer > Utilities > Notification Methods to update personal
and subscription notification preferences globally.


## Developer Notes

### Notification event types

Notification event types can be filtered using ``'notification_events','notifications'`` hook.
Users will be given an option to unsubscribe from notifications about these events or batch them into a digest.
Note that some instant notification events should not be added this list, e.g. password reset and other
account related notifications should remain instant.

### Notification Testing

You can disable outgoing email by switching to File Transport in plugin settings,
this will instead write email as txt files to the filestore under `/notifications_log/zend/`

### Sample SMTP config for GMail

To use GMail as your SMTP relay, you will likely need to Allow less secure apps:
https://support.google.com/accounts/answer/6010255?hl=en

- Host: smtp.gmail.com
- Port: 587
- Secure Connection: TLS
- Auth: SMTP with AUTH LOGIN
- Username: <your gmail email>
- Password: <your gmail password>

### Sample SMTP config for SendGrid

- Host: smtp.sendgrid.com
- Port: 587
- Secure Connection: TLS
- Auth: SMTP with AUTH LOGIN
- Username: apikey
- Password: <your api key>


### File Attachments

To add attachments to your email, add an array of `ElggFile` objects to notification parameters:

```php
notify_user($to, $from, $subject, $body, array(
	'attachments' => array(
		$file1, $file2,
	)
));
```
