package com.gonulkoprusu.app.session

import android.content.Context

class SessionStore(context: Context) {
    private val prefs = context.getSharedPreferences(PREFS, Context.MODE_PRIVATE)

    var fcmToken: String?
        get() = prefs.getString(KEY_FCM, null)
        set(value) {
            prefs.edit().putString(KEY_FCM, value).apply()
        }

    var hasCompletedNativeLogin: Boolean
        get() = prefs.getBoolean(KEY_LOGGED_IN, false)
        set(value) {
            prefs.edit().putBoolean(KEY_LOGGED_IN, value).apply()
        }

    fun clearAuthFlag() {
        hasCompletedNativeLogin = false
    }

    companion object {
        private const val PREFS = "gonul_session"
        private const val KEY_FCM = "fcm_token"
        private const val KEY_LOGGED_IN = "native_login_done"
    }
}
