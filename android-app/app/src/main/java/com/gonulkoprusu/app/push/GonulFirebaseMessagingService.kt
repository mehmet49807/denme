package com.gonulkoprusu.app.push

import com.gonulkoprusu.app.GonulApp
import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

class GonulFirebaseMessagingService : FirebaseMessagingService() {
    override fun onNewToken(token: String) {
        GonulApp.instance.sessionStore.fcmToken = token
    }

    override fun onMessageReceived(message: RemoteMessage) {
        // Website / server push payloads are handled as system notifications by FCM
        // when a notification payload is present. Data-only handling can be added later.
    }
}
