package com.gonulkoprusu.app.network

import com.gonulkoprusu.app.BuildConfig
import okhttp3.MediaType.Companion.toMediaType
import okhttp3.OkHttpClient
import okhttp3.Request
import okhttp3.RequestBody.Companion.toRequestBody
import org.json.JSONObject
import java.util.concurrent.TimeUnit

data class MobileLoginResult(
    val ok: Boolean,
    val handoffUrl: String? = null,
    val message: String? = null,
)

class MobileAuthApi(
    private val client: OkHttpClient = OkHttpClient.Builder()
        .connectTimeout(20, TimeUnit.SECONDS)
        .readTimeout(20, TimeUnit.SECONDS)
        .build(),
) {
    fun login(login: String, password: String, remember: Boolean = true): MobileLoginResult {
        val payload = JSONObject()
            .put("login", login.trim())
            .put("password", password)
            .put("remember", remember)
            .toString()

        val request = Request.Builder()
            .url("${BuildConfig.API_BASE_URL}/api/mobile/login")
            .addHeader("Accept", "application/json")
            .addHeader("Content-Type", "application/json")
            .addHeader("User-Agent", USER_AGENT)
            .post(payload.toRequestBody(JSON_MEDIA))
            .build()

        client.newCall(request).execute().use { response ->
            val body = response.body?.string().orEmpty()
            val json = runCatching { JSONObject(body) }.getOrNull()

            if (response.isSuccessful && json?.optBoolean("ok") == true) {
                return MobileLoginResult(
                    ok = true,
                    handoffUrl = json.optString("handoff_url").takeIf { it.isNotBlank() },
                )
            }

            val message = json?.optString("message")
                ?.takeIf { it.isNotBlank() }
                ?: "Giriş bilgileri hatalı."

            return MobileLoginResult(ok = false, message = message)
        }
    }

    companion object {
        private val JSON_MEDIA = "application/json; charset=utf-8".toMediaType()
        const val USER_AGENT = "GonulKoprusuAndroid/1.0"
    }
}
