package com.gonulkoprusu.data.api

import com.gonulkoprusu.data.model.AuthResponse
import com.gonulkoprusu.data.model.LikeResponse
import com.gonulkoprusu.data.model.LoginRequest
import com.gonulkoprusu.data.model.Paginated
import com.gonulkoprusu.data.model.Post
import com.gonulkoprusu.data.model.PrivateUser
import com.gonulkoprusu.data.model.RegisterRequest
import retrofit2.http.Body
import retrofit2.http.DELETE
import retrofit2.http.GET
import retrofit2.http.POST
import retrofit2.http.PUT
import retrofit2.http.Path

interface ApiService {

    // ---- Auth ----
    @POST("auth/register") suspend fun register(@Body body: RegisterRequest): AuthResponse
    @POST("auth/login")    suspend fun login(@Body body: LoginRequest): AuthResponse
    @GET("auth/me")        suspend fun me(): PrivateUser
    @POST("auth/logout")   suspend fun logout()

    // ---- Profile ----
    @GET("profile")        suspend fun profile(): PrivateUser
    // username is intentionally excluded - it is read-only.
    @PUT("profile")        suspend fun updateProfile(@Body body: Map<String, String>): PrivateUser

    // ---- Feed (comments disabled by contract) ----
    @GET("feed")           suspend fun feed(): Paginated<Post>
    @POST("posts/{id}/like") suspend fun like(@Path("id") id: Long): LikeResponse

    // ---- Premium (men only) ----
    @POST("premium/subscribe") suspend fun subscribe(@Body body: Map<String, String>)

    // ---- Stories (premium men only) ----
    @POST("stories")       suspend fun postStory(@Body body: Map<String, String>)

    // ---- Safety: Report (Şikayet) & Block (Engelle) on every profile ----
    @POST("users/{id}/report") suspend fun report(@Path("id") id: Long, @Body body: Map<String, String>)
    @POST("users/{id}/block")  suspend fun block(@Path("id") id: Long)
    @DELETE("users/{id}/block") suspend fun unblock(@Path("id") id: Long)

    // ---- Messaging ----
    @POST("conversations/{id}") suspend fun sendMessage(@Path("id") id: Long, @Body body: Map<String, String>)
}
