package com.gonulkoprusu.ui.screens

import androidx.compose.foundation.background
import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Box
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.Spacer
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.height
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material3.Button
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.unit.dp
import com.gonulkoprusu.ui.theme.Apricot
import com.gonulkoprusu.ui.theme.LinenSurface
import com.gonulkoprusu.ui.theme.Sage

@Composable
fun FeedScreen() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(MaterialTheme.colorScheme.background)
            .padding(20.dp),
        verticalArrangement = Arrangement.spacedBy(18.dp)
    ) {
        Text("Instagram-like post feed", style = MaterialTheme.typography.headlineSmall)
        FeedCard(username = "ayse_istanbul", city = "Istanbul", district = "Kadikoy")
    }
}

@Composable
private fun FeedCard(username: String, city: String, district: String) {
    Card(colors = CardDefaults.cardColors(containerColor = LinenSurface), shape = RoundedCornerShape(28.dp)) {
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            horizontalArrangement = Arrangement.SpaceBetween,
            verticalAlignment = Alignment.CenterVertically
        ) {
            Text(username, style = MaterialTheme.typography.titleMedium)
            CityDistrictBox(city = city, district = district)
        }
        Box(
            modifier = Modifier
                .fillMaxWidth()
                .height(280.dp)
                .padding(horizontal = 16.dp)
                .clip(RoundedCornerShape(24.dp))
                .background(Apricot),
            contentAlignment = Alignment.Center
        ) {
            Text("Post image placeholder")
        }
        Row(
            modifier = Modifier
                .fillMaxWidth()
                .padding(16.dp),
            horizontalArrangement = Arrangement.SpaceBetween
        ) {
            Button(onClick = { }) { Text("Like") }
            Row(horizontalArrangement = Arrangement.spacedBy(10.dp)) {
                Button(onClick = { }) { Text("Sikayet") }
                Button(onClick = { }) { Text("Engelle") }
            }
        }
        Text(
            "Comments are closed for every post.",
            modifier = Modifier.padding(start = 16.dp, end = 16.dp, bottom = 16.dp),
            color = MaterialTheme.colorScheme.onSurface.copy(alpha = 0.68f)
        )
    }
}

@Composable
fun CityDistrictBox(city: String, district: String) {
    Row(
        modifier = Modifier
            .clip(RoundedCornerShape(16.dp))
            .background(Sage)
            .padding(horizontal = 12.dp, vertical = 8.dp),
        horizontalArrangement = Arrangement.spacedBy(6.dp)
    ) {
        Text(city)
        Text("-")
        Text(district)
    }
}
