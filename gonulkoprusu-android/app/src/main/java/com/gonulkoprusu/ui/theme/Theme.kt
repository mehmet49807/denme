package com.gonulkoprusu.ui.theme

import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable

private val Palette = lightColorScheme(
    primary = Berry,
    secondary = Sage,
    tertiary = Apricot,
    background = CreamBackground,
    surface = LinenSurface,
    onPrimary = CreamBackground,
    onSecondary = CocoaText,
    onBackground = CocoaText,
    onSurface = CocoaText
)

@Composable
fun GonulKoprusuTheme(content: @Composable () -> Unit) {
    MaterialTheme(colorScheme = Palette, content = content)
}
