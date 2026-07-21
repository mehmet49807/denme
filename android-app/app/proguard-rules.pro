# Keep JS bridge for WebView
-keepclassmembers class com.gonulkoprusu.app.bridge.GonulNativeBridge {
    @android.webkit.JavascriptInterface <methods>;
}
