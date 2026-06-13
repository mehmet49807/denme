package com.gonulkoprusu.data

import android.content.Context
import androidx.datastore.preferences.core.edit
import androidx.datastore.preferences.core.stringPreferencesKey
import androidx.datastore.preferences.preferencesDataStore
import kotlinx.coroutines.flow.first
import kotlinx.coroutines.flow.map

private val Context.dataStore by preferencesDataStore(name = "gonul_session")

/** Persists the auth token so the user stays logged in across launches. */
class SessionManager(private val context: Context) {

    @Volatile var cachedToken: String? = null
        private set

    private val tokenKey = stringPreferencesKey("token")

    suspend fun load() {
        cachedToken = context.dataStore.data.map { it[tokenKey] }.first()
    }

    suspend fun save(token: String) {
        cachedToken = token
        context.dataStore.edit { it[tokenKey] = token }
    }

    suspend fun clear() {
        cachedToken = null
        context.dataStore.edit { it.remove(tokenKey) }
    }
}
