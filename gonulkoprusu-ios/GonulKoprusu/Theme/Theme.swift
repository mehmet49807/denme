import SwiftUI

/// Palette rules: NO black, NO gold, NO pure white.
/// Soft, warm, rich light tones (cream, beige, warm pastels).
enum Theme {
    static let cream      = Color(red: 0.984, green: 0.965, blue: 0.937) // #FBF6EF
    static let cream2     = Color(red: 0.957, green: 0.925, blue: 0.882) // #F4ECE1
    static let card       = Color(red: 1.000, green: 0.992, blue: 0.976) // #FFFDF9 (warm off-white)
    static let beige      = Color(red: 0.914, green: 0.867, blue: 0.800) // #E9DDCC

    static let rose       = Color(red: 0.890, green: 0.663, blue: 0.631) // #E3A9A1
    static let roseDeep   = Color(red: 0.812, green: 0.541, blue: 0.510) // #CF8A82
    static let terracotta = Color(red: 0.851, green: 0.627, blue: 0.400) // #D9A066
    static let sage       = Color(red: 0.655, green: 0.749, blue: 0.639) // #A7BFA3
    static let lavender   = Color(red: 0.725, green: 0.675, blue: 0.820) // #B9ACD1

    static let textMain   = Color(red: 0.353, green: 0.294, blue: 0.247) // #5A4B3F
    static let textSoft   = Color(red: 0.541, green: 0.478, blue: 0.420) // #8A7A6B
}

/// Reusable "City · District" bounded box.
struct LocationBox: View {
    let city: String
    let district: String
    var body: some View {
        HStack(spacing: 6) {
            Text(city)
            Text("·").foregroundColor(Theme.roseDeep).bold()
            Text(district)
        }
        .font(.caption)
        .foregroundColor(Theme.textSoft)
        .padding(.horizontal, 10).padding(.vertical, 5)
        .background(Theme.cream2)
        .clipShape(RoundedRectangle(cornerRadius: 10))
    }
}
