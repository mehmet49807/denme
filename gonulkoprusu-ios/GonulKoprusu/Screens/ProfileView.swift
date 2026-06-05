import SwiftUI

struct ProfileView: View {
    @State private var firstName = "Deniz"
    @State private var lastName = "Yilmaz"
    @State private var email = "deniz@example.com"
    @State private var phone = "+905551112233"

    var body: some View {
        Form {
            Section("Public") {
                TextField("Username", text: .constant("deniz34"))
                    .disabled(true)
                CityDistrictBox(city: "Istanbul", district: "Kadikoy")
            }
            Section("Owner/Admin only") {
                TextField("First name", text: $firstName)
                TextField("Last name", text: $lastName)
                TextField("Email", text: $email)
                TextField("Phone", text: $phone)
            }
            Section("Safety") {
                Button("Sikayet") { }
                Button("Engelle") { }
            }
        }
        .scrollContentBackground(.hidden)
        .background(AppTheme.creamBackground)
    }
}
