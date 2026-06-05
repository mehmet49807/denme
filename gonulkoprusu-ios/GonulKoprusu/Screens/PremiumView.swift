import SwiftUI

struct PremiumView: View {
    private let packages = [
        ("Pro", "1 Week", "250 TL"),
        ("Gold", "2 Weeks", "300 TL"),
        ("Platinum", "30 Days", "500 TL")
    ]

    var body: some View {
        VStack(alignment: .leading, spacing: 16) {
            Text("Premium applies only to male accounts")
                .font(.title3.weight(.semibold))
            ForEach(packages, id: \.0) { package in
                VStack(alignment: .leading, spacing: 6) {
                    Text(package.0).font(.headline)
                    Text(package.1)
                    Text(package.2)
                }
                .frame(maxWidth: .infinity, alignment: .leading)
                .padding(18)
                .background(AppTheme.linenSurface)
                .clipShape(RoundedRectangle(cornerRadius: 24))
            }
            Text("Premium men can add stories. Female accounts have full access for free.")
            Spacer()
        }
        .padding(20)
        .background(AppTheme.creamBackground.ignoresSafeArea())
    }
}
