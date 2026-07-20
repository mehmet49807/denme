<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Services\FcmWebConfigService;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class FcmWebController extends Controller
{
    public function config(FcmWebConfigService $web): SymfonyResponse
    {
        $cfg = $web->publicConfig();

        return response()->json($cfg, 200, [
            'Cache-Control' => 'no-store',
        ]);
    }

    public function serviceWorker(FcmWebConfigService $web): Response
    {
        $cfg = $web->publicConfig();
        $projectId = (string) ($cfg['projectId'] ?? 'gonulkoprusu-325eb');
        $apiKey = (string) ($cfg['apiKey'] ?? '');
        $authDomain = (string) ($cfg['authDomain'] ?? '');
        $senderId = (string) ($cfg['messagingSenderId'] ?? '');
        $appId = (string) ($cfg['appId'] ?? '');
        $bucket = (string) ($cfg['storageBucket'] ?? '');

        $js = <<<JS
/* Gönül Köprüsü — FCM web service worker */
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-app-compat.js');
importScripts('https://www.gstatic.com/firebasejs/10.14.1/firebase-messaging-compat.js');

firebase.initializeApp({
  apiKey: {$this->jsString($apiKey)},
  authDomain: {$this->jsString($authDomain)},
  projectId: {$this->jsString($projectId)},
  storageBucket: {$this->jsString($bucket)},
  messagingSenderId: {$this->jsString($senderId)},
  appId: {$this->jsString($appId)}
});

const messaging = firebase.messaging();

messaging.onBackgroundMessage(function (payload) {
  const note = (payload && payload.notification) || {};
  const data = (payload && payload.data) || {};
  const title = note.title || data.title || 'Gönül Köprüsü';
  const body = note.body || data.body || '';
  const options = {
    body: body,
    icon: '/images/logo-180.png',
    badge: '/images/favicon.png',
    data: data,
    tag: data.type || 'gk-push',
    renotify: true
  };
  self.registration.showNotification(title, options);
});

self.addEventListener('notificationclick', function (event) {
  event.notification.close();
  const data = event.notification.data || {};
  let url = '/notifications';
  if (data.type === 'new_message' && data.actor_username) {
    url = '/messages/' + encodeURIComponent(data.actor_username);
  } else if (data.type === 'broadcast') {
    url = '/notifications';
  } else if (data.type === 'support_reply') {
    url = '/destek';
  }
  event.waitUntil(
    clients.matchAll({ type: 'window', includeUncontrolled: true }).then(function (clientList) {
      for (let i = 0; i < clientList.length; i++) {
        const client = clientList[i];
        if (client.url && 'focus' in client) {
          client.focus();
          if ('navigate' in client) client.navigate(url);
          return;
        }
      }
      if (clients.openWindow) return clients.openWindow(url);
    })
  );
});
JS;

        return response($js, 200, [
            'Content-Type' => 'application/javascript; charset=utf-8',
            'Service-Worker-Allowed' => '/',
            'Cache-Control' => 'no-store',
        ]);
    }

    private function jsString(string $value): string
    {
        return json_encode($value, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) ?: '""';
    }
}
