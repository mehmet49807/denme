package com.gonulkoprusu.app.push

import com.gonulkoprusu.app.GonulApp
import com.google.firebase.messaging.FirebaseMessaging

/**
 * Soft FCM bootstrap. Safe when google-services.json / FirebaseApp is not configured yet.
 */
object FcmHelper {
    fun refreshTokenAsync() {
        runCatching {
            FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
                if (task.isSuccessful) {
                    val token = task.result
                    if (!token.isNullOrBlank()) {
                        GonulApp.instance.sessionStore.fcmToken = token
                    }
                }
            }
        }
    }
}
