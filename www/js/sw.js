self.addEventListener('push', function (event) {
	if (!(self.Notification && self.Notification.permission === 'granted')) {
		return;
	}
	
	var data = {};
	try {
		data = JSON.parse(event.data.text())
	}
	catch(err) {
		data.message = event.data.text()
	}

	const title = data.title || 'Medlist.ro';
	const message = data.message || 'Updates availabble on Medlist';
	const iconPrefix = 'https://medlist.ro';
	const iconUrl = iconPrefix + '/icon2.ico';
	const options =  {
		body: message,
		tag: 'Medlist',
		icon: iconUrl,
		badge: iconUrl
	};

	event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
	event.notification.close();
	event.waitUntil(
		clients.openWindow('https://medlist.ro')
	);
});