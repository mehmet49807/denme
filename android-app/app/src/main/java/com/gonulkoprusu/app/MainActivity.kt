package com.gonulkoprusu.app

import android.annotation.SuppressLint
import android.content.Intent
import android.graphics.Bitmap
import android.net.Uri
import android.os.Bundle
import android.view.View
import android.webkit.CookieManager
import android.webkit.WebChromeClient
import android.webkit.WebResourceRequest
import android.webkit.WebSettings
import android.webkit.WebView
import android.webkit.WebViewClient
import androidx.activity.OnBackPressedCallback
import androidx.appcompat.app.AppCompatActivity
import androidx.browser.customtabs.CustomTabsIntent
import com.gonulkoprusu.app.bridge.GonulNativeBridge
import com.gonulkoprusu.app.databinding.ActivityMainBinding
import com.gonulkoprusu.app.network.MobileAuthApi
import com.gonulkoprusu.app.push.FcmHelper

class MainActivity : AppCompatActivity() {
    private lateinit var binding: ActivityMainBinding
    private var returningToNativeLogin = false

    @SuppressLint("SetJavaScriptEnabled")
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        binding = ActivityMainBinding.inflate(layoutInflater)
        setContentView(binding.root)

        FcmHelper.refreshTokenAsync()

        val cookieManager = CookieManager.getInstance()
        cookieManager.setAcceptCookie(true)
        cookieManager.setAcceptThirdPartyCookies(binding.webView, true)

        with(binding.webView.settings) {
            javaScriptEnabled = true
            domStorageEnabled = true
            databaseEnabled = true
            mediaPlaybackRequiresUserGesture = false
            mixedContentMode = WebSettings.MIXED_CONTENT_COMPATIBILITY_MODE
            userAgentString = "$userAgentString ${MobileAuthApi.USER_AGENT}"
            setSupportZoom(false)
            builtInZoomControls = false
            displayZoomControls = false
            loadWithOverviewMode = true
            useWideViewPort = true
        }

        binding.webView.addJavascriptInterface(GonulNativeBridge(), "GonulNative")
        binding.webView.webChromeClient = object : WebChromeClient() {
            override fun onProgressChanged(view: WebView?, newProgress: Int) {
                binding.progress.visibility = if (newProgress in 1..99) View.VISIBLE else View.GONE
            }
        }
        binding.webView.webViewClient = object : WebViewClient() {
            override fun shouldOverrideUrlLoading(
                view: WebView?,
                request: WebResourceRequest?,
            ): Boolean {
                val url = request?.url ?: return false
                return handleNavigation(url)
            }

            @Deprecated("Deprecated in Java")
            override fun shouldOverrideUrlLoading(view: WebView?, url: String?): Boolean {
                if (url.isNullOrBlank()) return false
                return handleNavigation(Uri.parse(url))
            }

            override fun onPageStarted(view: WebView?, url: String?, favicon: Bitmap?) {
                injectNativeFlag(view)
                maybeHandleAuthPage(url)
            }

            override fun onPageFinished(view: WebView?, url: String?) {
                injectNativeFlag(view)
                injectFcmTokenMessage(view)
                maybeHandleAuthPage(url)
            }
        }

        onBackPressedDispatcher.addCallback(
            this,
            object : OnBackPressedCallback(true) {
                override fun handleOnBackPressed() {
                    if (binding.webView.canGoBack()) {
                        binding.webView.goBack()
                    } else {
                        isEnabled = false
                        onBackPressedDispatcher.onBackPressed()
                    }
                }
            },
        )

        val startUrl = intent.getStringExtra(EXTRA_START_URL)
            ?: (BuildConfig.SITE_BASE_URL + "/feed")
        binding.webView.loadUrl(startUrl)
    }

    private fun handleNavigation(uri: Uri): Boolean {
        val host = uri.host?.lowercase().orEmpty()
        if (host.isEmpty()) return false

        if (isWebViewHost(host)) {
            return false
        }

        openExternal(uri)
        return true
    }

    private fun isWebViewHost(host: String): Boolean {
        if (host == "gonulkoprusu.com" || host == "www.gonulkoprusu.com") return true
        // Google OAuth must stay in WebView (Custom Tabs do not share cookies with WebView).
        if (host == "accounts.google.com" || host == "accounts.youtube.com") return true
        if (host.endsWith(".google.com") || host.endsWith(".gstatic.com")) return true
        if (host.endsWith(".googleusercontent.com")) return true
        return false
    }

    private fun maybeHandleAuthPage(url: String?) {
        if (returningToNativeLogin || url.isNullOrBlank()) return
        val path = Uri.parse(url).path?.trimEnd('/').orEmpty()

        // Google / password login succeeded inside WebView.
        if (path == "/feed" || path.startsWith("/messages") || path.startsWith("/profile") ||
            path.startsWith("/users") || path.startsWith("/notifications") || path.startsWith("/premium")
        ) {
            GonulApp.instance.sessionStore.hasCompletedNativeLogin = true
            return
        }

        // After site logout / session expiry, return to native login.
        if (path == "/login") {
            returningToNativeLogin = true
            GonulApp.instance.sessionStore.clearAuthFlag()
            startActivity(
                Intent(this, LoginActivity::class.java)
                    .putExtra(LoginActivity.EXTRA_FORCE_LOGIN, true)
                    .addFlags(Intent.FLAG_ACTIVITY_CLEAR_TOP or Intent.FLAG_ACTIVITY_NEW_TASK),
            )
            finish()
        }
    }

    private fun injectNativeFlag(view: WebView?) {
        view?.evaluateJavascript(
            """
            (function(){
              try {
                window.__GONUL_NATIVE_ANDROID = true;
                window.GonulNativePlatform = 'android';
              } catch (e) {}
            })();
            """.trimIndent(),
            null,
        )
    }

    private fun injectFcmTokenMessage(view: WebView?) {
        val token = GonulApp.instance.sessionStore.fcmToken ?: return
        if (token.isBlank()) return
        val safe = token.replace("\\", "\\\\").replace("'", "\\'")
        view?.evaluateJavascript(
            """
            (function(){
              try {
                window.postMessage({type:'fcm_token', token:'$safe', platform:'android'}, '*');
              } catch (e) {}
            })();
            """.trimIndent(),
            null,
        )
    }

    private fun openExternal(uri: Uri) {
        runCatching {
            CustomTabsIntent.Builder().build().launchUrl(this, uri)
        }.onFailure {
            startActivity(Intent(Intent.ACTION_VIEW, uri))
        }
    }

    override fun onDestroy() {
        CookieManager.getInstance().flush()
        if (::binding.isInitialized) {
            binding.webView.destroy()
        }
        super.onDestroy()
    }

    companion object {
        const val EXTRA_START_URL = "start_url"
    }
}
