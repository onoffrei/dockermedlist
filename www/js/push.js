push_vars = {}
push_registerServiceWorker = function(){
	return navigator.serviceWorker.register(window.cs_url + 'js/sw.js', { scope: '/js/' }).then(function(serviceWorkerRegistration){
		push_vars.serviceWorkerRegistration = serviceWorkerRegistration;
		console.log('Push Service Worker has been registered successfully');
	}).catch(function(error){
		console.error(error)
		console.error('Service worker registration failed');			
	});
};
urlB64ToUint8Array = function(base64String){
	const padding = '='.repeat((4 - base64String.length % 4) % 4);
	const base64 = (base64String + padding).replace(/\-/g, '+').replace(/_/g, '/');

	const rawData = window.atob(base64);
	const outputArray = new Uint8Array(rawData.length);

	for (var i = 0; i < rawData.length; ++i) {
		outputArray[i] = rawData.charCodeAt(i);
	}

	return outputArray;
};
if (typeof(Promise) != 'undefined')
push_init =  new Promise(function(resolve,reject){
	console.log('resolving')
	if (!('serviceWorker' in navigator)) {
		console.error('Service Workers not supported');
		resolve(push_vars)
		return;
	}
	if (!('PushManager' in window)) {
		console.error('PushManager not supported');
		resolve(push_vars)
		return;
	}
	if (!('ServiceWorkerRegistration' in window)) {
		console.error('ServiceWorkerRegistration not supported');
		resolve(push_vars)
		return;
	}
	if (!('showNotification' in ServiceWorkerRegistration.prototype)) {
		console.error('showNotification not supported');
		resolve(push_vars)
		return;
	}
	if (Notification.permission === 'denied') {
		console.error('showNotification denied');
		resolve(push_vars)
		return;
	}
	if (typeof(window.cs_push_publickey) == 'undefined') {
		console.error('cs_push_publickey');
		resolve(push_vars)
		return;
	}
	push_vars.serverPublicKey = urlB64ToUint8Array(window.cs_push_publickey);
	document.addEventListener("DOMContentLoaded", function(){
		push_registerServiceWorker().then(function(){
			if (typeof(push_vars.serviceWorkerRegistration) != 'undefined'){
				push_vars.serviceWorkerRegistration.pushManager.getSubscription().then(function (subscription) {
					if (subscription != null) push_vars.pushSubscription = subscription
					resolve(push_vars)		
					return
				});
			}else{
				resolve(push_vars)
			}
		})
	})
})
push_subscribe = function(){
	return new Promise(function(resolve,reject){
		console.log('subscribing...')
		if (typeof(push_vars.serviceWorkerRegistration) == 'undefined'){
			console.error('serviceWorkerRegistration??')
			reject()
			return
		}
        push_vars.serviceWorkerRegistration.pushManager.subscribe({
            userVisibleOnly: true,
            applicationServerKey: push_vars.serverPublicKey
        }).then(function (pushSubscription) {
			push_vars.pushSubscription = pushSubscription
			resolve(push_vars)
		}).catch(function (error) {
			console.error('Failed to subscribe for Push Notifications: ' + error);
			resolve(push_vars)
		});
	})
}
push_unsubscribe = function(){
	return new Promise(function(resolve,reject){
		console.log('unsubscribing...')
		if (typeof(push_vars.serviceWorkerRegistration) == 'undefined'){
			console.error('serviceWorkerRegistration??')
			reject()
			return
		}
        push_vars.serviceWorkerRegistration.pushManager.getSubscription().then(function (pushSubscription) {
			if (pushSubscription) {
				pushSubscription.unsubscribe().then(function () {
					console.log('Successfully unsubscribed from Push Notifications');
					resolve(push_vars)
				}).catch(function (error) {
					console.log('Failed to unsubscribe from Push Notifications: ' + error);
					resolve(push_vars)
				});
			}
		});
	})
}
if (typeof(Promise) != 'undefined')
push_init.then(function(d){
	console.log('push initing..')
	if (typeof(push_vars.serviceWorkerRegistration) == 'undefined'){
		console.error('serviceWorkerRegistration??')
		return
	}
})