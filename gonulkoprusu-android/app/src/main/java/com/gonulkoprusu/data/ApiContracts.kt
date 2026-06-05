package com.gonulkoprusu.data

object ApiContracts {
    const val BASE_URL = "https://api.gonulkoprusu.example/api/v1"

    object Auth {
        const val REGISTER = "/auth/register"
        const val LOGIN = "/auth/login"
        const val LOGOUT = "/auth/logout"
    }

    object Feed {
        const val FEED = "/feed"
        const val POSTS = "/posts"
        fun like(postId: Long) = "/posts/$postId/like"
    }

    object Profile {
        const val ME = "/profile/me"
        fun show(userId: Long) = "/profiles/$userId"
        fun report(userId: Long) = "/profiles/$userId/report"
        fun block(userId: Long) = "/profiles/$userId/block"
    }

    object Premium {
        const val PACKAGES = "/premium/packages"
        const val STATUS = "/premium/status"
        const val SUBSCRIBE = "/premium/subscribe"
    }

    object Messaging {
        const val CONVERSATIONS = "/conversations"
        fun messages(userId: Long) = "/messages/$userId"
    }
}
