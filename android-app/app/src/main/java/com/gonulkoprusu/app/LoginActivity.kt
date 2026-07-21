package com.gonulkoprusu.app

import android.content.Intent
import android.os.Bundle
import android.view.View
import android.view.inputmethod.EditorInfo
import androidx.appcompat.app.AppCompatActivity
import androidx.lifecycle.lifecycleScope
import com.gonulkoprusu.app.databinding.ActivityLoginBinding
import com.gonulkoprusu.app.network.MobileAuthApi
import com.gonulkoprusu.app.network.MobileLoginResult
import com.gonulkoprusu.app.push.FcmHelper
import kotlinx.coroutines.Dispatchers
import kotlinx.coroutines.launch
import kotlinx.coroutines.withContext

class LoginActivity : AppCompatActivity() {
    private lateinit var binding: ActivityLoginBinding
    private val api = MobileAuthApi()

    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        // Returning users who already have a web session can go straight to the shell.
        if (GonulApp.instance.sessionStore.hasCompletedNativeLogin &&
            intent?.getBooleanExtra(EXTRA_FORCE_LOGIN, false) != true
        ) {
            openWeb(BuildConfig.SITE_BASE_URL + "/feed")
            finish()
            return
        }

        binding = ActivityLoginBinding.inflate(layoutInflater)
        setContentView(binding.root)

        FcmHelper.refreshTokenAsync()

        binding.passwordInput.setOnEditorActionListener { _, actionId, _ ->
            if (actionId == EditorInfo.IME_ACTION_DONE) {
                attemptLogin()
                true
            } else {
                false
            }
        }

        binding.loginButton.setOnClickListener { attemptLogin() }
        binding.googleButton.setOnClickListener {
            openWeb(BuildConfig.SITE_BASE_URL + "/auth/google")
        }
        binding.registerLink.setOnClickListener {
            openWeb(
                BuildConfig.SITE_BASE_URL +
                    "/register?utm_source=android&utm_medium=app&utm_campaign=native_login"
            )
        }
        binding.forgotLink.setOnClickListener {
            openWeb(BuildConfig.SITE_BASE_URL + "/forgot-password")
        }
    }

    private fun attemptLogin() {
        val login = binding.loginInput.text?.toString().orEmpty().trim()
        val password = binding.passwordInput.text?.toString().orEmpty()

        if (login.isBlank() || password.isBlank()) {
            showError("Kullanıcı adı/e-posta ve şifre gerekli.")
            return
        }

        setLoading(true)
        lifecycleScope.launch {
            val result = withContext(Dispatchers.IO) {
                runCatching { api.login(login, password) }
                    .getOrElse {
                        MobileLoginResult(
                            ok = false,
                            message = getString(R.string.login_error_network),
                        )
                    }
            }

            setLoading(false)

            if (result.ok && !result.handoffUrl.isNullOrBlank()) {
                GonulApp.instance.sessionStore.hasCompletedNativeLogin = true
                openWeb(result.handoffUrl)
                finish()
            } else {
                showError(result.message ?: getString(R.string.login_error_generic))
            }
        }
    }

    private fun openWeb(url: String) {
        startActivity(
            Intent(this, MainActivity::class.java).putExtra(MainActivity.EXTRA_START_URL, url)
        )
    }

    private fun setLoading(loading: Boolean) {
        binding.progress.visibility = if (loading) View.VISIBLE else View.GONE
        binding.loginButton.isEnabled = !loading
        binding.googleButton.isEnabled = !loading
    }

    private fun showError(message: String) {
        binding.errorText.visibility = View.VISIBLE
        binding.errorText.text = message
    }

    companion object {
        const val EXTRA_FORCE_LOGIN = "force_login"
    }
}
