self.addEventListener('push', function (event) {
    if (!event.data) {
        return;
    }

    const payload = event.data.json();
    const title = payload.title || 'TERDEPAN';
    const options = {
        body: payload.body,
        icon: payload.icon || '/assets/img/logo.webp',
        badge: payload.badge,
        tag: payload.tag,
        data: payload.data,
    };

    event.waitUntil(self.registration.showNotification(title, options));
});

self.addEventListener('notificationclick', function (event) {
    event.notification.close();

    const url = event.notification.data && event.notification.data.url;
    if (!url) {
        return;
    }

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (windowClients) {
            for (const client of windowClients) {
                if (client.url === url && 'focus' in client) {
                    return client.focus();
                }
            }

            if (clients.openWindow) {
                return clients.openWindow(url);
            }
        })
    );
});
