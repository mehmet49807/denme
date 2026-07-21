package com.gonulkoprusu.app

import android.app.Application
import com.gonulkoprusu.app.session.SessionStore

class GonulApp : Application() {
    lateinit var sessionStore: SessionStore
        private set

    override fun onCreate() {
        super.onCreate()
        instance = this
        sessionStore = SessionStore(this)
    }

    companion object {
        lateinit var instance: GonulApp
            private set
    }
}
