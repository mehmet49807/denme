package com.gonulkoprusu

import android.annotation.SuppressLint
import android.os.Bundle
import android.webkit.CookieManager
import android.webkit.WebChromeClient
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.appcompat.app.AppCompatActivity

/**
 * Chrome Custom Tabs / TWA kullanılamazsa yedek WebView.
 * Site kuralları aynıdır — sunucu tarafı (premium, kadın ücretsiz mesaj, 2FA rozet vb.).
 */
class FallbackActivity : AppCompatActivity() {
    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        val webView = WebView(this)
        setContentView(webView)

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
        webView.webViewClient = WebViewClient()
        webView.webChromeClient = WebChromeClient()

        val url = intent?.data?.toString() ?: "https://gonulkoprusu.com/feed"
        webView.loadUrl(url)
    }
}
