package com.gonulkoprusu.data.model

import com.squareup.moshi.Json
import com.squareup.moshi.JsonClass

/** Public view of another user (private fields are never delivered by the API). */
@JsonClass(generateAdapter = true)
data class PublicUser(
    val id: Long,
    val username: String,
    @Json(name = "profile_photo") val profilePhoto: String?,
    val bio: String?,
    val gender: String,
    val city: String,
    val district: String,
    @Json(name = "is_premium") val isPremium: Boolean = false,
)

/** Owner / admin full profile including PRIVATE fields. */
@JsonClass(generateAdapter = true)
data class PrivateUser(
    val id: Long,
    val username: String,                       // READ-ONLY on the client
    @Json(name = "first_name") val firstName: String,
    @Json(name = "last_name") val lastName: String,
    val email: String,
    val phone: String,
    val gender: String,
    val city: String,
    val district: String,
    @Json(name = "profile_photo") val profilePhoto: String?,
    val bio: String?,
    val role: String,
    @Json(name = "is_premium") val isPremium: Boolean = false,
)

@JsonClass(generateAdapter = true)
data class Post(
    val id: Long,
    @Json(name = "image_url") val imageUrl: String,
    val caption: String?,
    @Json(name = "likes_count") val likesCount: Int,
    @Json(name = "liked_by_me") val likedByMe: Boolean,
    @Json(name = "comments_enabled") val commentsEnabled: Boolean = false,
    val author: PublicUser?,
)

@JsonClass(generateAdapter = true)
data class AuthResponse(val token: String, val user: PrivateUser)

@JsonClass(generateAdapter = true)
data class LoginRequest(val login: String, val password: String)

@JsonClass(generateAdapter = true)
data class RegisterRequest(
    val username: String,
    @Json(name = "first_name") val firstName: String,
    @Json(name = "last_name") val lastName: String,
    val email: String,
    val password: String,
    val phone: String,
    val gender: String,
    val city: String,
    val district: String,
)

@JsonClass(generateAdapter = true)
data class Paginated<T>(val data: List<T>)

@JsonClass(generateAdapter = true)
data class LikeResponse(val liked: Boolean, @Json(name = "likes_count") val likesCount: Int)
