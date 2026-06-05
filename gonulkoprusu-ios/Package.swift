// swift-tools-version: 5.10

import PackageDescription

let package = Package(
    name: "GonulKoprusu",
    platforms: [.iOS(.v17)],
    products: [
        .library(name: "GonulKoprusu", targets: ["GonulKoprusu"])
    ],
    targets: [
        .target(name: "GonulKoprusu", path: "GonulKoprusu")
    ]
)
