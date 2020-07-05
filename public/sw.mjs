self.addEventListener('push', function (event) {
  if (event && event.data) {
    const data = event.data.json();
    event.waitUntil(self.registration.showNotification(data.title, {
      body: data.body,
      icon: data.icon || null
    });
  }
});
