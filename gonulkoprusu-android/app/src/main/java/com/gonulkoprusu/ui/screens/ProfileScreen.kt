package com.gonulkoprusu.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.OutlinedTextField
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.gonulkoprusu.ui.theme.LinenSurface

@Composable
fun ProfileScreen() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(androidx.compose.material3.MaterialTheme.colorScheme.background)
            .padding(20.dp),
        verticalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        Text("Profile")
        Card(colors = CardDefaults.cardColors(containerColor = LinenSurface), shape = RoundedCornerShape(28.dp)) {
            Column(modifier = Modifier.padding(18.dp), verticalArrangement = Arrangement.spacedBy(12.dp)) {
                OutlinedTextField(value = "deniz34", onValueChange = {}, enabled = false, label = { Text("Username (read-only)") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(value = "Deniz Yilmaz", onValueChange = {}, label = { Text("Full name (owner/admin only)") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(value = "deniz@example.com", onValueChange = {}, label = { Text("Email (hidden from others)") }, modifier = Modifier.fillMaxWidth())
                OutlinedTextField(value = "+905551112233", onValueChange = {}, label = { Text("Phone (hidden from others)") }, modifier = Modifier.fillMaxWidth())
                CityDistrictBox(city = "Istanbul", district = "Kadikoy")
            }
        }
    }
}
