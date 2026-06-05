import Foundation

/// Public view of another user. Private fields are never delivered by the API.
struct PublicUser: Codable, Identifiable {
    let id: Int
    let username: String
    let profilePhoto: String?
    let bio: String?
    let gender: String
    let city: String
    let district: String
    let isPremium: Bool

    enum CodingKeys: String, CodingKey {
        case id, username, bio, gender, city, district
        case profilePhoto = "profile_photo"
        case isPremium = "is_premium"
    }
}

/// Owner/admin full profile, including PRIVATE fields.
struct PrivateUser: Codable, Identifiable {
    let id: Int
    let username: String          // READ-ONLY on the client
    let firstName: String
    let lastName: String
    let email: String
    let phone: String
    let gender: String
    let city: String
    let district: String
    let profilePhoto: String?
    let bio: String?
    let role: String
    let isPremium: Bool

    enum CodingKeys: String, CodingKey {
        case id, username, email, phone, gender, city, district, bio, role
        case firstName = "first_name"
        case lastName = "last_name"
        case profilePhoto = "profile_photo"
        case isPremium = "is_premium"
    }
}

struct Post: Codable, Identifiable {
    let id: Int
    let imageUrl: String
    let caption: String?
    let likesCount: Int
    let likedByMe: Bool
    let commentsEnabled: Bool
    let author: PublicUser?

    enum CodingKeys: String, CodingKey {
        case id, caption, author
        case imageUrl = "image_url"
        case likesCount = "likes_count"
        case likedByMe = "liked_by_me"
        case commentsEnabled = "comments_enabled"
    }
}

struct AuthResponse: Codable {
    let token: String
    let user: PrivateUser
}

struct Paginated<T: Codable>: Codable {
    let data: [T]
}
