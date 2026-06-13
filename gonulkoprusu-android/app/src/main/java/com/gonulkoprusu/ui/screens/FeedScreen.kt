package com.gonulkoprusu.ui.screens

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.aspectRatio
import androidx.compose.foundation.layout.fillMaxSize
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.foundation.lazy.LazyColumn
import androidx.compose.foundation.lazy.items
import androidx.compose.foundation.shape.RoundedCornerShape
import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.filled.FavoriteBorder
import androidx.compose.material3.Card
import androidx.compose.material3.CardDefaults
import androidx.compose.material3.Icon
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Surface
import androidx.compose.material3.Text
import androidx.compose.material3.TextButton
import androidx.compose.runtime.Composable
import androidx.compose.runtime.LaunchedEffect
import androidx.compose.runtime.getValue
import androidx.compose.runtime.mutableStateListOf
import androidx.compose.runtime.remember
import androidx.compose.ui.Alignment
import androidx.compose.ui.Modifier
import androidx.compose.ui.draw.clip
import androidx.compose.ui.graphics.Color
import androidx.compose.ui.text.font.FontWeight
import androidx.compose.ui.unit.dp
import androidx.navigation.NavController
import coil.compose.AsyncImage
import com.gonulkoprusu.data.api.ApiService
import com.gonulkoprusu.data.model.Post
import com.gonulkoprusu.ui.theme.Beige
import com.gonulkoprusu.ui.theme.Cream2
import com.gonulkoprusu.ui.theme.RoseDeep

/**
 * Instagram-like feed.
 *   Left  : username
 *   Right : city · district (bounded box)
 *   Action: Like only. Comments are intentionally NOT rendered (disabled).
 */
@Composable
fun FeedScreen(api: ApiService, nav: NavController) {
    val posts = remember { mutableStateListOf<Post>() }

    LaunchedEffect(Unit) {
        runCatching { api.feed() }.onSuccess {
            posts.clear(); posts.addAll(it.data)
        }
    }

    LazyColumn(
        modifier = Modifier.fillMaxSize().padding(12.dp),
        verticalArrangement = Arrangement.spacedBy(16.dp),
    ) {
        items(posts) { post -> PostCard(post) }
    }
}

@Composable
private fun PostCard(post: Post) {
    Card(
        shape = RoundedCornerShape(16.dp),
        colors = CardDefaults.cardColors(containerColor = MaterialTheme.colorScheme.surface),
    ) {
        Column {
            Row(
                modifier = Modifier.fillMaxWidth().padding(14.dp),
                horizontalArrangement = Arrangement.SpaceBetween,
                verticalAlignment = Alignment.CenterVertically,
            ) {
                Text(post.author?.username ?: "", fontWeight = FontWeight.Bold)
                LocationBox(post.author?.city.orEmpty(), post.author?.district.orEmpty())
            }

            AsyncImage(
                model = post.imageUrl,
                contentDescription = null,
                modifier = Modifier.fillMaxWidth().aspectRatio(1f),
            )

            Row(verticalAlignment = Alignment.CenterVertically, modifier = Modifier.padding(8.dp)) {
                TextButton(onClick = { /* call api.like(post.id) in viewModel */ }) {
                    Icon(Icons.Filled.FavoriteBorder, contentDescription = "Beğen", tint = RoseDeep)
                    Text("  Beğen (${post.likesCount})")
                }
                // No comment affordance: comments are closed platform-wide.
            }
            post.caption?.let {
                Text(it, modifier = Modifier.padding(start = 14.dp, end = 14.dp, bottom = 14.dp),
                    style = MaterialTheme.typography.bodyMedium)
            }
        }
    }
}

@Composable
fun LocationBox(city: String, district: String) {
    Surface(color = Cream2, shape = RoundedCornerShape(10.dp)) {
        Row(modifier = Modifier.padding(horizontal = 10.dp, vertical = 5.dp)) {
            Text(city, style = MaterialTheme.typography.labelSmall)
            Text("  ·  ", color = RoseDeep, fontWeight = FontWeight.Bold)
            Text(district, style = MaterialTheme.typography.labelSmall)
        }
    }
}
