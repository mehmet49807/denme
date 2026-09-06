package com.gonulkoprusu

import android.app.Application
import com.google.firebase.FirebaseApp

class GonulApp : Application() {
    override fun onCreate() {
        super.onCreate()
        FirebaseApp.initializeApp(this)
    }
}
