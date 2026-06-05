import Foundation

enum Gender: String, Codable {
    case female
    case male
}

struct PublicUser: Identifiable, Codable {
    let id: Int
    let username: String
    let profilePhotoURL: URL?
    let gender: Gender
    let city: String
    let district: String
}

struct OwnerUser: Identifiable, Codable {
    let id: Int
    let username: String
    var firstName: String
    var lastName: String
    var email: String
    var phone: String
    let gender: Gender
    var profilePhotoURL: URL?
    var city: String
    var district: String
}

struct Post: Identifiable, Codable {
    let id: Int
    let author: PublicUser
    let imageURL: URL
    let likesCount: Int
    let likedByMe: Bool
    let commentsEnabled: Bool
}

struct PremiumPackage: Identifiable, Codable {
    let type: String
    let label: String
    let durationDays: Int
    let priceTry: Int

    var id: String { type }
}
