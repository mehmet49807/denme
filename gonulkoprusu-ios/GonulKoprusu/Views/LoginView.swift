import SwiftUI

struct LoginView: View {
    @EnvironmentObject var session: SessionManager
    @State private var login = ""
    @State private var password = ""
    @State private var error: String?
    @State private var showRegister = false

    var body: some View {
        VStack(spacing: 16) {
            Spacer()
            Text("Gönül Köprüsü")
                .font(.largeTitle).bold()
                .foregroundColor(Theme.textMain)

            TextField("Kullanıcı adı veya e-posta", text: $login)
                .textInputAutocapitalization(.never)
                .padding().background(Theme.card).clipShape(RoundedRectangle(cornerRadius: 12))

            SecureField("Şifre", text: $password)
                .padding().background(Theme.card).clipShape(RoundedRectangle(cornerRadius: 12))

            if let error { Text(error).foregroundColor(Theme.roseDeep) }

            Button("Giriş Yap") { Task { await signIn() } }
                .frame(maxWidth: .infinity)
                .padding()
                .background(Theme.rose)
                .foregroundColor(Theme.card)
                .clipShape(RoundedRectangle(cornerRadius: 24))

            Button("Hesabınız yok mu? Kayıt olun") { showRegister = true }
                .foregroundColor(Theme.roseDeep)
            Spacer()
        }
        .padding()
        .background(Theme.cream.ignoresSafeArea())
        .sheet(isPresented: $showRegister) { RegisterView() }
    }

    private func signIn() async {
        do {
            let res = try await APIClient.shared.login(login: login, password: password)
            session.signIn(res.token)
        } catch {
            self.error = "Giriş başarısız."
        }
    }
}
