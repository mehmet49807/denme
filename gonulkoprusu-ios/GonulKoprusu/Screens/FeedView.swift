import SwiftUI

struct FeedView: View {
    var body: some View {
        ScrollView {
            VStack(alignment: .leading, spacing: 18) {
                Text("Instagram-like post feed")
                    .font(.title2.weight(.semibold))
                FeedCard(username: "ayse_istanbul", city: "Istanbul", district: "Kadikoy")
                TestimonialsView()
            }
            .padding(20)
        }
        .background(AppTheme.creamBackground.ignoresSafeArea())
    }
}

private struct FeedCard: View {
    let username: String
    let city: String
    let district: String

    var body: some View {
        VStack(spacing: 0) {
            HStack {
                Text(username).font(.headline)
                Spacer()
                CityDistrictBox(city: city, district: district)
            }
            .padding(16)

            RoundedRectangle(cornerRadius: 26)
                .fill(LinearGradient(colors: [AppTheme.softRose, AppTheme.apricot, AppTheme.sage], startPoint: .topLeading, endPoint: .bottomTrailing))
                .frame(height: 320)
                .overlay(Text("Post image placeholder"))
                .padding(.horizontal, 16)

            HStack {
                Button("Like") { }
                    .buttonStyle(.borderedProminent)
                Spacer()
                Button("Sikayet") { }
                Button("Engelle") { }
            }
            .padding(16)

            Text("Comments are closed for every post.")
                .font(.footnote)
                .foregroundStyle(AppTheme.cocoaText.opacity(0.7))
                .padding(.bottom, 16)
        }
        .background(AppTheme.linenSurface)
        .clipShape(RoundedRectangle(cornerRadius: 30))
    }
}

struct CityDistrictBox: View {
    let city: String
    let district: String

    var body: some View {
        HStack(spacing: 6) {
            Text(city)
            Text("-")
            Text(district)
        }
        .font(.subheadline.weight(.medium))
        .padding(.horizontal, 12)
        .padding(.vertical, 8)
        .background(AppTheme.warmBeige)
        .clipShape(RoundedRectangle(cornerRadius: 16))
    }
}

private struct TestimonialsView: View {
    var body: some View {
        VStack(alignment: .leading, spacing: 12) {
            Text("Thank You messages")
                .font(.headline)
            Text("Tesekkurler! Burada tanistik ve guvenli iletisim sayesinde ciddi bir adim attik.")
            Text("Gonul Koprusu ekibine tesekkur ederiz.")
        }
        .padding(18)
        .background(AppTheme.linenSurface)
        .clipShape(RoundedRectangle(cornerRadius: 24))
    }
}
