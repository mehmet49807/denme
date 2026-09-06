package com.gonulkoprusu

import android.annotation.SuppressLint
import android.os.Bundle
import android.webkit.CookieManager
import android.webkit.JavascriptInterface
import android.webkit.WebChromeClient
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.appcompat.app.AppCompatActivity
import com.google.firebase.messaging.FirebaseMessaging
import org.json.JSONObject

class FallbackActivity : AppCompatActivity() {
    private var fcmToken: String? = null

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val webView = WebView(this)
        setContentView(webView)

        fcmToken = FcmTokenStore.get(this)
        FirebaseMessaging.getInstance().token.addOnSuccessListener { t ->
            FcmTokenStore.save(this, t)
            fcmToken = t
        }

        CookieManager.getInstance().setAcceptCookie(true)
        CookieManager.getInstance().setAcceptThirdPartyCookies(webView, true)

        webView.settings.apply {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            mediaPlaybackRequiresUserGesture = false
            mixedContentMode = WebSettings.MIXED_CONTENT_NEVER_ALLOW
            userAgentString = "$userAgentString GonulKoprusuApp/2.0"
        }
        webView.addJavascriptInterface(NativeBridge(), "GonulNative")
        webView.webViewClient = object : WebViewClient() {
            override fun onPageFinished(view: WebView?, url: String?) {
                super.onPageFinished(view, url)
                val token = fcmToken ?: return
                val quoted = JSONObject.quote(token)
                val js = """
                    (function(){
                      try {
                        if (window.GkPush && window.GkPush.register) {
                          window.GkPush.register($quoted, 'android');
                        }
                      } catch(e) {}
                    })();
                """.trimIndent()
                view?.evaluateJavascript(js, null)
            }
        }
        webView.webChromeClient = WebChromeClient()

        val url = intent?.data?.toString() ?: LauncherHelper.startUrl(fcmToken)
        webView.loadUrl(url)
    }

    inner class NativeBridge {
        @JavascriptInterface
        fun getFcmToken(): String = fcmToken ?: FcmTokenStore.get(this@FallbackActivity) ?: ""
    }
}
