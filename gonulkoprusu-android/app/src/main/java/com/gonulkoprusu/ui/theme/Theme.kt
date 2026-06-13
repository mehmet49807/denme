package com.gonulkoprusu.ui.theme

import androidx.compose.foundation.isSystemInDarkTheme
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.lightColorScheme
import androidx.compose.runtime.Composable

private val GonulColorScheme = lightColorScheme(
    primary = Rose,
    onPrimary = CardSurface,
    secondary = Sage,
    tertiary = Lavender,
    background = Cream,
    onBackground = TextMain,
    surface = CardSurface,
    onSurface = TextMain,
    surfaceVariant = Cream2,
    outline = Beige,
)

@Composable
fun GonulKoprusuTheme(
    // We intentionally always use the warm light palette (no dark/black theme).
    darkTheme: Boolean = isSystemInDarkTheme(),
    content: @Composable () -> Unit,
) {
    MaterialTheme(
        colorScheme = GonulColorScheme,
        typography = Typography,
        content = content,
    )
}
