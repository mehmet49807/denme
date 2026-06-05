import SwiftUI

struct RegisterView: View {
    @State private var username = ""
    @State private var fullName = ""
    @State private var email = ""
    @State private var password = ""
    @State private var phone = ""
    @State private var city = ""
    @State private var district = ""

    var body: some View {
        Form {
            TextField("Username", text: $username)
            TextField("First name & Last name", text: $fullName)
            TextField("Email", text: $email)
            SecureField("Password", text: $password)
            TextField("Phone Number", text: $phone)
            HStack {
                TextField("City", text: $city)
                TextField("District", text: $district)
            }
        }
        .scrollContentBackground(.hidden)
        .background(AppTheme.creamBackground)
    }
}
