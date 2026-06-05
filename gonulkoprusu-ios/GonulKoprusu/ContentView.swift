import SwiftUI

struct ContentView: View {
    var body: some View {
        TabView {
            FeedView()
                .tabItem { Label("Akis", systemImage: "photo.on.rectangle") }
            ProfileView()
                .tabItem { Label("Profil", systemImage: "person.crop.circle") }
            PremiumView()
                .tabItem { Label("Premium", systemImage: "sparkles") }
        }
        .tint(AppTheme.berry)
    }
}
