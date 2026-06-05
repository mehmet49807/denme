import Foundation

enum APIError: Error { case badResponse, decoding }

/// Thin async/await REST client. Injects the Sanctum bearer token so the same
/// account works across Web / Android / iOS against the central database.
final class APIClient {
    static let shared = APIClient()

    // Central REST API base URL (placeholder until backend host is supplied).
    private let baseURL = URL(string: "https://api.gonulkoprusu.com/api/v1/")!

    var token: String?

    private func request(_ path: String, method: String = "GET", body: Encodable? = nil) async throws -> Data {
        var req = URLRequest(url: baseURL.appendingPathComponent(path))
        req.httpMethod = method
        req.setValue("application/json", forHTTPHeaderField: "Accept")
        req.setValue("application/json", forHTTPHeaderField: "Content-Type")
        if let token { req.setValue("Bearer \(token)", forHTTPHeaderField: "Authorization") }
        if let body { req.httpBody = try JSONEncoder().encode(AnyEncodable(body)) }

        let (data, response) = try await URLSession.shared.data(for: req)
        guard let http = response as? HTTPURLResponse, (200..<300).contains(http.statusCode) else {
            throw APIError.badResponse
        }
        return data
    }

    private func decode<T: Decodable>(_ data: Data) throws -> T {
        do { return try JSONDecoder().decode(T.self, from: data) }
        catch { throw APIError.decoding }
    }

    // MARK: - Auth
    func login(login: String, password: String) async throws -> AuthResponse {
        let data = try await request("auth/login", method: "POST",
                                     body: ["login": login, "password": password])
        return try decode(data)
    }

    func register(_ payload: [String: String]) async throws -> AuthResponse {
        let data = try await request("auth/register", method: "POST", body: payload)
        return try decode(data)
    }

    // MARK: - Profile (username is read-only and never sent)
    func profile() async throws -> PrivateUser {
        try decode(try await request("profile"))
    }

    func updateProfile(_ fields: [String: String]) async throws -> PrivateUser {
        try decode(try await request("profile", method: "PUT", body: fields))
    }

    // MARK: - Feed (comments disabled by contract)
    func feed() async throws -> [Post] {
        let page: Paginated<Post> = try decode(try await request("feed"))
        return page.data
    }

    func like(postId: Int) async throws {
        _ = try await request("posts/\(postId)/like", method: "POST")
    }

    // MARK: - Safety: Report (Şikayet) & Block (Engelle)
    func report(userId: Int, reason: String) async throws {
        _ = try await request("users/\(userId)/report", method: "POST", body: ["reason": reason])
    }

    func block(userId: Int) async throws {
        _ = try await request("users/\(userId)/block", method: "POST")
    }
}

/// Type-erasing wrapper so heterogeneous dictionaries/structs can be encoded.
private struct AnyEncodable: Encodable {
    private let encodeFunc: (Encoder) throws -> Void
    init(_ wrapped: Encodable) { encodeFunc = wrapped.encode }
    func encode(to encoder: Encoder) throws { try encodeFunc(encoder) }
}
