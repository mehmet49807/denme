package com.gonulkoprusu.app.bridge

import android.webkit.JavascriptInterface
import com.gonulkoprusu.app.GonulApp

/**
 * Exposed to the website as window.GonulNative.
 * Site already calls GonulNative.getFcmToken() from layouts/app.blade.php.
 */
class GonulNativeBridge {
    @JavascriptInterface
    fun getFcmToken(): String {
        return GonulApp.instance.sessionStore.fcmToken.orEmpty()
    }

    @JavascriptInterface
    fun getPlatform(): String = "android"

    @JavascriptInterface
    fun getAppVersion(): String = "1.0.0"
}
