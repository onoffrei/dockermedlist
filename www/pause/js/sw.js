const pushNotificationTitle = 'new notification available';
const pushNotificationIcon = '/favicon.ico';

self.addEventListener('push', function (event) {
	if (!(self.Notification && self.Notification.permission === 'granted')) {return;}
	event.waitUntil(self.registration.showNotification(pushNotificationTitle, {
		body: event.data.text(),
		icon: pushNotificationIcon
	}));
});

self.addEventListener('notificationclick', function (event) {
	event.notification.close();
});