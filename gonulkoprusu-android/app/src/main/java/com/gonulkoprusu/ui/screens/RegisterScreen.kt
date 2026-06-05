package com.gonulkoprusu.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.Button
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.rememberCoroutineScope
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.text.input.PasswordVisualTransformation
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.gonulkoprusu.data.SessionManager
import com.gonulkoprusu.data.api.ApiService
import com.gonulkoprusu.data.model.RegisterRequest
import kotlinx.coroutines.launch

@Composable
fun RegisterScreen(api: ApiService, session: SessionManager, nav: NavController) {
    var username by remember { mutableStateOf("") }
    var firstName by remember { mutableStateOf("") }
    var lastName by remember { mutableStateOf("") }
    var email by remember { mutableStateOf("") }
    var phone by remember { mutableStateOf("") }
    var password by remember { mutableStateOf("") }
    var gender by remember { mutableStateOf("female") }
    var city by remember { mutableStateOf("") }
    var district by remember { mutableStateOf("") }
    val scope = rememberCoroutineScope()

    Column(
        modifier = Modifier.fillMaxSize().padding(20.dp).verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.spacedBy(10.dp),
    ) {
        Text("Aramıza Katılın", style = androidx.compose.material3.MaterialTheme.typography.titleLarge)

        OutlinedTextField(username, { username = it }, label = { Text("Kullanıcı Adı") }, modifier = Modifier.fillMaxWidth())
        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
            OutlinedTextField(firstName, { firstName = it }, label = { Text("Ad") }, modifier = Modifier.weight(1f))
            OutlinedTextField(lastName, { lastName = it }, label = { Text("Soyad") }, modifier = Modifier.weight(1f))
        }
        OutlinedTextField(email, { email = it }, label = { Text("E-posta") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(phone, { phone = it }, label = { Text("Telefon") }, modifier = Modifier.fillMaxWidth())
        OutlinedTextField(password, { password = it }, label = { Text("Şifre") },
            visualTransformation = PasswordVisualTransformation(), modifier = Modifier.fillMaxWidth())
        // City - District side-by-side selection box
        Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
            OutlinedTextField(city, { city = it }, label = { Text("Şehir") }, modifier = Modifier.weight(1f))
            OutlinedTextField(district, { district = it }, label = { Text("İlçe") }, modifier = Modifier.weight(1f))
        }

        Button(
            onClick = {
                scope.launch {
                    runCatching {
                        api.register(RegisterRequest(username, firstName, lastName, email, password, phone, gender, city, district))
                    }.onSuccess { session.save(it.token); nav.navigate("feed") }
                }
            },
            modifier = Modifier.fillMaxWidth(),
        ) { Text("Kayıt Ol") }
    }
}
