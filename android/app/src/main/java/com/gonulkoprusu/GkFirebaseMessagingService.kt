package com.gonulkoprusu

import com.google.firebase.messaging.FirebaseMessagingService
import com.google.firebase.messaging.RemoteMessage

/** FCM push — web bildirim tercihleri ile uyumlu token kaydı sonraki adımda. */
class GkFirebaseMessagingService : FirebaseMessagingService() {
    override fun onNewToken(token: String) {
        super.onNewToken(token)
        // TODO: POST token to https://gonulkoprusu.com/api/device-tokens when endpoint is ready
    }

    override fun onMessageReceived(message: RemoteMessage) {
        super.onMessageReceived(message)
    }
}
