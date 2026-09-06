package com.gonulkoprusu

import android.content.Intent
import android.net.Uri
import android.os.Bundle
import androidx.appcompat.app.AppCompatActivity
import androidx.browser.customtabs.CustomTabsIntent
import androidx.core.content.ContextCompat
import com.google.firebase.messaging.FirebaseMessaging

class MainActivity : AppCompatActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        FirebaseMessaging.getInstance().token.addOnCompleteListener { task ->
            val token = if (task.isSuccessful) task.result else FcmTokenStore.get(this)
            if (!token.isNullOrBlank()) {
                FcmTokenStore.save(this, token)
            }
            openSite(token)
        }
    }

    private fun openSite(token: String?) {
        val url = LauncherHelper.startUrl(token)
        try {
            val customTabs = CustomTabsIntent.Builder()
                .setShowTitle(false)
                .setToolbarColor(ContextCompat.getColor(this, R.color.theme_color))
                .setUrlBarHidingEnabled(true)
                .setShareState(CustomTabsIntent.SHARE_STATE_OFF)
                .build()
            customTabs.intent.putExtra(
                "android.support.customtabs.extra.LAUNCH_AS_TRUSTED_WEB_ACTIVITY",
                true
            )
            customTabs.launchUrl(this, Uri.parse(url))
        } catch (e: Exception) {
            startActivity(Intent(this, FallbackActivity::class.java).setData(Uri.parse(url)))
        }
        finish()
    }
}
