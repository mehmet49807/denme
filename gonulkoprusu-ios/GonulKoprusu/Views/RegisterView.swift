import SwiftUI

struct RegisterView: View {
    @EnvironmentObject var session: SessionManager
    @Environment(\.dismiss) var dismiss

    @State private var username = ""
    @State private var firstName = ""
    @State private var lastName = ""
    @State private var email = ""
    @State private var phone = ""
    @State private var password = ""
    @State private var gender = "female"
    @State private var city = ""
    @State private var district = ""

    var body: some View {
        NavigationStack {
            Form {
                Section("Hesap") {
                    TextField("Kullanıcı Adı", text: $username)
                        .textInputAutocapitalization(.never)
                    TextField("Ad", text: $firstName)
                    TextField("Soyad", text: $lastName)
                    TextField("E-posta", text: $email)
                    TextField("Telefon", text: $phone)
                    SecureField("Şifre", text: $password)
                    Picker("Cinsiyet", selection: $gender) {
                        Text("Kadın").tag("female")
                        Text("Erkek").tag("male")
                    }
                }
                // City - District side-by-side selection box
                Section("Konum") {
                    HStack {
                        TextField("Şehir", text: $city)
                        Divider()
                        TextField("İlçe", text: $district)
                    }
                }
                Button("Kayıt Ol") { Task { await register() } }
            }
            .navigationTitle("Aramıza Katılın")
        }
    }

    private func register() async {
        let payload = [
            "username": username, "first_name": firstName, "last_name": lastName,
            "email": email, "password": password, "phone": phone,
            "gender": gender, "city": city, "district": district,
        ]
        if let res = try? await APIClient.shared.register(payload) {
            session.signIn(res.token)
            dismiss()
        }
    }
}
