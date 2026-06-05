package com.gonulkoprusu.model

enum class Gender { Female, Male }

data class PublicUser(
    val id: Long,
    val username: String,
    val profilePhotoUrl: String?,
    val gender: Gender,
    val city: String,
    val district: String
)

data class OwnerUser(
    val id: Long,
    val username: String,
    val firstName: String,
    val lastName: String,
    val email: String,
    val phone: String,
    val gender: Gender,
    val profilePhotoUrl: String?,
    val city: String,
    val district: String
)

data class Post(
    val id: Long,
    val author: PublicUser,
    val imageUrl: String,
    val likesCount: Int,
    val likedByMe: Boolean,
    val commentsEnabled: Boolean = false
)

data class PremiumPackage(
    val type: String,
    val label: String,
    val durationDays: Int,
    val priceTry: Int
)
