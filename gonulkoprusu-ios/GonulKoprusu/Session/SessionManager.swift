import Foundation
import Combine

/// Persists the auth token (UserDefaults here; move to Keychain for production)
/// so the user stays logged in across launches.
final class SessionManager: ObservableObject {
    @Published var token: String? {
        didSet {
            UserDefaults.standard.set(token, forKey: Self.key)
            APIClient.shared.token = token
        }
    }

    private static let key = "gonul_token"

    init() {
        let stored = UserDefaults.standard.string(forKey: Self.key)
        token = stored
        APIClient.shared.token = stored
    }

    func signIn(_ token: String) { self.token = token }
    func signOut() { self.token = nil }
}
