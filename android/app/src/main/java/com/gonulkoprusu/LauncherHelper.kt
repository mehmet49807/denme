package com.gonulkoprusu

import android.net.Uri

object LauncherHelper {
    private const val BASE = "https://gonulkoprusu.com/feed"

    fun startUrl(token: String?): String {
        if (token.isNullOrBlank()) return BASE
        return Uri.parse(BASE).buildUpon()
            .appendQueryParameter("gk_android_fcm", token)
            .appendQueryParameter("utm_source", "android_app")
            .build()
            .toString()
    }
}
