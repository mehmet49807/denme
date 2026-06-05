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
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.gonulkoprusu.ui.theme.LinenSurface

@Composable
fun PremiumScreen() {
    Column(
        modifier = Modifier
            .fillMaxSize()
            .background(androidx.compose.material3.MaterialTheme.colorScheme.background)
            .padding(20.dp),
        verticalArrangement = Arrangement.spacedBy(14.dp)
    ) {
        Text("Premium applies only to male accounts")
        PremiumPackageCard("Pro", "1 Week", "250 TL")
        PremiumPackageCard("Gold", "2 Weeks", "300 TL")
        PremiumPackageCard("Platinum", "30 Days", "500 TL")
        Text("Premium men can add stories. Female accounts have full access without a premium tier.")
    }
}

@Composable
private fun PremiumPackageCard(label: String, duration: String, price: String) {
    Card(colors = CardDefaults.cardColors(containerColor = LinenSurface), shape = RoundedCornerShape(24.dp), modifier = Modifier.fillMaxWidth()) {
        Column(modifier = Modifier.padding(18.dp)) {
            Text(label)
            Text(duration)
            Text(price)
        }
    }
}
