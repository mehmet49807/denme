import Foundation

struct APIClient {
    static let baseURL = URL(string: "https://api.gonulkoprusu.example/api/v1")!

    enum Endpoint {
        static let register = "/auth/register"
        static let login = "/auth/login"
        static let feed = "/feed"
        static let profileMe = "/profile/me"
        static let premiumPackages = "/premium/packages"
        static let conversations = "/conversations"

        static func profile(_ userID: Int) -> String { "/profiles/\(userID)" }
        static func report(_ userID: Int) -> String { "/profiles/\(userID)/report" }
        static func block(_ userID: Int) -> String { "/profiles/\(userID)/block" }
        static func messages(_ userID: Int) -> String { "/messages/\(userID)" }
    }
}
