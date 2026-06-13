import SwiftUI

/// Owner profile. Username is READ-ONLY (cannot be changed).
/// Real name, email, phone are shown here only because this is the owner's view.
struct ProfileView: View {
    @EnvironmentObject var session: SessionManager
    @State private var user: PrivateUser?
    @State private var firstName = ""
    @State private var lastName = ""
    @State private var email = ""
    @State private var phone = ""
    @State private var city = ""
    @State private var district = ""

    var body: some View {
        NavigationStack {
            Form {
                Section("Kullanıcı Adı (değiştirilemez)") {
                    Text(user?.username ?? "")
                        .foregroundColor(Theme.textSoft)   // READ-ONLY: shown, not editable
                }

                Section {
                    Text("Bu bilgiler yalnızca size ve yöneticilere görünür.")
                        .font(.caption).foregroundColor(Theme.textSoft)
                    TextField("Ad", text: $firstName)
                    TextField("Soyad", text: $lastName)
                    TextField("E-posta", text: $email)
                    TextField("Telefon", text: $phone)
                }

                Section("Konum") {
                    HStack {
                        TextField("Şehir", text: $city)
                        Divider()
                        TextField("İlçe", text: $district)
                    }
                }

                Section {
                    Button("Kaydet") { Task { await save() } }
                    Button("Çıkış Yap", role: .destructive) { session.signOut() }
                }
            }
            .navigationTitle("Profilim")
            .task { await load() }
        }
    }

    private func load() async {
        guard let u = try? await APIClient.shared.profile() else { return }
        user = u
        firstName = u.firstName; lastName = u.lastName
        email = u.email; phone = u.phone
        city = u.city; district = u.district
    }

    private func save() async {
        // username is intentionally omitted - it can never change.
        _ = try? await APIClient.shared.updateProfile([
            "first_name": firstName, "last_name": lastName,
            "email": email, "phone": phone,
            "city": city, "district": district,
        ])
    }
}
