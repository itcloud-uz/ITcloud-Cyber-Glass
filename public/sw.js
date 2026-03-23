self.addEventListener('install', function(event) {
  console.log('SW Installed');
});

self.addEventListener('fetch', function(event) {
  // Pass-through for now
  event.respondWith(fetch(event.request));
});
