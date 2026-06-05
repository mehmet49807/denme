package com.gonulkoprusu.ui.theme

import androidx.compose.material3.Typography
import androidx.compose.ui.text.TextStyle
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.sp

val Typography = Typography(
    titleLarge = TextStyle(fontWeight = FontWeight.Bold, fontSize = 24.sp, color = TextMain),
    bodyLarge = TextStyle(fontWeight = FontWeight.Normal, fontSize = 16.sp, color = TextMain),
    bodyMedium = TextStyle(fontWeight = FontWeight.Normal, fontSize = 14.sp, color = TextSoft),
    labelSmall = TextStyle(fontWeight = FontWeight.Medium, fontSize = 12.sp, color = TextMuted),
)
