"use strict";

const CACHE_NAME = "offline-cache-v1";
const OFFLINE_URL = "/offline.html";

const filesToCache = [OFFLINE_URL];

self.addEventListener("install", (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => cache.addAll(filesToCache))
    );
});

self.addEventListener("fetch", (event) => {
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(() => {
                return caches.match(OFFLINE_URL);
            })
        );
    } else {
        event.respondWith(
            caches.match(event.request).then((response) => {
                return response || fetch(event.request);
            })
        );
    }
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        Promise.all([
            self.clients.claim(),
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => {
                        if (cacheName !== CACHE_NAME) {
                            return caches.delete(cacheName);
                        }
                    })
                );
            }),
        ])
    );
});

self.addEventListener("push", (event) => {
    if (!(self.Notification && self.Notification.permission === "granted")) {
        return;
    }

    const payload = event.data ? event.data.json() : {};

    event.waitUntil(
        self.registration.showNotification(payload.title, {
            body: payload.body,
            icon: payload.icon,
            badge: payload.badge,
            actions: payload.actions || [],
            data: payload.data || {},
        })
    );
});

self.addEventListener("notificationclick", (event) => {
    const notification = event.notification;
    notification.close();

    if (event.action === "open") {
        event.waitUntil(clients.openWindow(notification.data.action_url));
    } else {
        event.waitUntil(
            clients.openWindow(notification.data.action_url || "/app")
        );
    }
});
