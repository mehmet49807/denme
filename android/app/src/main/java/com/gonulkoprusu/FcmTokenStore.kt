package com.gonulkoprusu

import android.content.Context

object FcmTokenStore {
    private const val PREF = "gk_fcm"
    private const val KEY = "token"

    fun save(context: Context, token: String) {
        context.getSharedPreferences(PREF, Context.MODE_PRIVATE)
            .edit()
            .putString(KEY, token)
            .apply()
    }

    fun get(context: Context): String? =
        context.getSharedPreferences(PREF, Context.MODE_PRIVATE).getString(KEY, null)
}
