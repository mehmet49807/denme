package com.gonulkoprusu

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.ExperimentalMaterial3Api
import androidx.compose.material3.Scaffold
import androidx.compose.material3.Tab
import androidx.compose.material3.TabRow
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableIntStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.compose.ui.Modifier
import com.gonulkoprusu.ui.screens.FeedScreen
import com.gonulkoprusu.ui.screens.PremiumScreen
import com.gonulkoprusu.ui.screens.ProfileScreen
import com.gonulkoprusu.ui.theme.GonulKoprusuTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)
        setContent { GonulKoprusuApp() }
    }
}

@OptIn(ExperimentalMaterial3Api::class)
@Composable
fun GonulKoprusuApp() {
    GonulKoprusuTheme {
        var selectedTab by remember { mutableIntStateOf(0) }
        val tabs = listOf("Akis", "Profil", "Premium")

        Scaffold { padding ->
            Column(modifier = Modifier.padding(padding)) {
                TabRow(selectedTabIndex = selectedTab) {
                    tabs.forEachIndexed { index, title ->
                        Tab(selected = selectedTab == index, onClick = { selectedTab = index }, text = { Text(title) })
                    }
                }
                when (selectedTab) {
                    0 -> FeedScreen()
                    1 -> ProfileScreen()
                    else -> PremiumScreen()
                }
            }
        }
    }
}
