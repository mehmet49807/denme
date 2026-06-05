package com.gonulkoprusu.ui.screens

import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.rememberScrollState
import androidx.compose.foundation.verticalScroll
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import com.gonulkoprusu.data.api.ApiService
import com.gonulkoprusu.data.model.PrivateUser

/**
 * Owner profile. Username is READ-ONLY (cannot be changed).
 * Real name, email, phone are hidden from other users and shown here only
 * because this is the owner's own view.
 */
@Composable
fun ProfileScreen(api: ApiService, nav: NavController) {
    var user by remember { mutableStateOf<PrivateUser?>(null) }

    LaunchedEffect(Unit) {
        runCatching { api.profile() }.onSuccess { user = it }
    }

    val u = user ?: return

    Column(
        modifier = Modifier.fillMaxSize().padding(16.dp).verticalScroll(rememberScrollState()),
        verticalArrangement = Arrangement.spacedBy(12.dp),
    ) {
        Text("Profilim", style = androidx.compose.material3.MaterialTheme.typography.titleLarge)

        OutlinedTextField(
            value = u.username,
            onValueChange = {},
            enabled = false,                        // READ-ONLY
            label = { Text("Kullanıcı Adı (değiştirilemez)") },
        )
        Text("Aşağıdaki bilgiler yalnızca size ve yöneticilere görünür.",
            style = androidx.compose.material3.MaterialTheme.typography.labelSmall)

        EditableField("Ad", u.firstName)
        EditableField("Soyad", u.lastName)
        EditableField("E-posta", u.email)
        EditableField("Telefon", u.phone)
        // City - District side-by-side bounded box
        LocationBox(u.city, u.district)
    }
}

@Composable
private fun EditableField(label: String, value: String) {
    var text by remember { mutableStateOf(value) }
    OutlinedTextField(value = text, onValueChange = { text = it }, label = { Text(label) })
}
