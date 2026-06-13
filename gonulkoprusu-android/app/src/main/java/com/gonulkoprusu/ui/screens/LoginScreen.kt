package com.gonulkoprusu.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.Button
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.gonulkoprusu.data.SessionManager
import com.gonulkoprusu.data.api.ApiService
import com.gonulkoprusu.data.model.LoginRequest
import kotlinx.coroutines.launch

@Composable
fun LoginScreen(api: ApiService, session: SessionManager, nav: NavController) {
    var login by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var error by remember { mutableStateOf<String?>(null) }
    val scope = rememberCoroutineScope()

    Column(
        modifier = Modifier.fillMaxSize().padding(24.dp),
        verticalArrangement = Arrangement.spacedBy(14.dp, Alignment.CenterVertically),
        horizontalAlignment = Alignment.CenterHorizontally,
    ) {
        Text("Gönül Köprüsü", style = androidx.compose.material3.MaterialTheme.typography.titleLarge)

        OutlinedTextField(value = login, onValueChange = { login = it },
            label = { Text("Kullanıcı adı veya e-posta") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(value = password, onValueChange = { password = it },
            label = { Text("Şifre") }, visualTransformation = PasswordVisualTransformation(),
            modifier = Modifier.fillMaxWidth())

        error?.let { Text(it) }

        Button(
            onClick = {
                scope.launch {
                    runCatching { api.login(LoginRequest(login, password)) }
                        .onSuccess { session.save(it.token); nav.navigate("feed") }
                        .onFailure { error = "Giriş başarısız." }
                }
            },
            modifier = Modifier.fillMaxWidth(),
        ) { Text("Giriş Yap") }

        TextButton(onClick = { nav.navigate("register") }) { Text("Hesabınız yok mu? Kayıt olun") }
    }
}
