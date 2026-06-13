package com.gonulkoprusu

import android.os.Bundle
import androidx.activity.ComponentActivity
import androidx.activity.compose.setContent
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateOf
import androidx.compose.runtime.remember
import androidx.compose.runtime.setValue
import androidx.navigation.compose.NavHost
import androidx.navigation.compose.composable
import androidx.navigation.compose.rememberNavController
import com.gonulkoprusu.data.SessionManager
import com.gonulkoprusu.data.api.ApiClient
import com.gonulkoprusu.ui.screens.FeedScreen
import com.gonulkoprusu.ui.screens.LoginScreen
import com.gonulkoprusu.ui.screens.ProfileScreen
import com.gonulkoprusu.ui.screens.RegisterScreen
import com.gonulkoprusu.ui.theme.GonulKoprusuTheme

class MainActivity : ComponentActivity() {
    override fun onCreate(savedInstanceState: Bundle?) {
        super.onCreate(savedInstanceState)

        val session = SessionManager(applicationContext)
        val api = ApiClient.create(session)

        setContent {
            GonulKoprusuTheme {
                val nav = rememberNavController()
                NavHost(navController = nav, startDestination = "login") {
                    composable("login") { LoginScreen(api, session, nav) }
                    composable("register") { RegisterScreen(api, session, nav) }
                    composable("feed") { FeedScreen(api, nav) }
                    composable("profile") { ProfileScreen(api, nav) }
                }
            }
        }
    }
}
