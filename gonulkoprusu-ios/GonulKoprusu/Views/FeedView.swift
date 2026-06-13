import SwiftUI

/// Instagram-like feed.
///   Left  : username
///   Right : city · district (bounded box)
///   Action: Like only. Comments are intentionally NOT rendered (disabled).
struct FeedView: View {
    @State private var posts: [Post] = []

    var body: some View {
        NavigationStack {
            ScrollView {
                LazyVStack(spacing: 16) {
                    ForEach(posts) { post in PostCard(post: post) }
                }
                .padding()
            }
            .background(Theme.cream.ignoresSafeArea())
            .navigationTitle("Akış")
            .task { await load() }
        }
    }

    private func load() async {
        posts = (try? await APIClient.shared.feed()) ?? []
    }
}

private struct PostCard: View {
    let post: Post

    var body: some View {
        VStack(spacing: 0) {
            HStack {
                Text(post.author?.username ?? "")
                    .font(.headline)
                    .foregroundColor(Theme.textMain)
                Spacer()
                LocationBox(city: post.author?.city ?? "", district: post.author?.district ?? "")
            }
            .padding(12)

            AsyncImage(url: URL(string: post.imageUrl)) { image in
                image.resizable().scaledToFill()
            } placeholder: {
                Theme.cream2
            }
            .frame(maxWidth: .infinity)
            .frame(height: 320)
            .clipped()

            HStack {
                Button {
                    Task { try? await APIClient.shared.like(postId: post.id) }
                } label: {
                    Label("Beğen (\(post.likesCount))", systemImage: "heart")
                        .foregroundColor(Theme.roseDeep)
                }
                Spacer()
                // No comment affordance: comments are closed platform-wide.
            }
            .padding(12)

            if let caption = post.caption {
                Text(caption)
                    .font(.subheadline)
                    .foregroundColor(Theme.textSoft)
                    .frame(maxWidth: .infinity, alignment: .leading)
                    .padding([.horizontal, .bottom], 12)
            }
        }
        .background(Theme.card)
        .clipShape(RoundedRectangle(cornerRadius: 16))
        .shadow(color: Theme.beige.opacity(0.6), radius: 8, y: 4)
    }
}
