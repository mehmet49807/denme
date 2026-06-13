import SwiftUI

@main
struct GonulKoprusuApp: App {
    @StateObject private var session = SessionManager()

    var body: some Scene {
        WindowGroup {
            RootView()
                .environmentObject(session)
                .tint(Theme.rose)
        }
    }
}

/// Switches between the auth flow and the main app based on the stored token.
struct RootView: View {
    @EnvironmentObject var session: SessionManager

    var body: some View {
        Group {
            if session.token == nil {
                LoginView()
            } else {
                MainTabView()
            }
        }
        .background(Theme.cream.ignoresSafeArea())
    }
}

struct MainTabView: View {
    var body: some View {
        TabView {
            FeedView()
                .tabItem { Label("Akış", systemImage: "house") }
            ProfileView()
                .tabItem { Label("Profil", systemImage: "person") }
        }
    }
}
