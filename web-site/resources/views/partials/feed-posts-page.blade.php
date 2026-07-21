@foreach($posts as $post)
    @include('partials.feed-post-card', [
        'post' => $post,
        'viewer' => $viewer,
        'likedPostIds' => $likedPostIds,
        'index' => ($pageOffset ?? 0) + $loop->index,
        'eager' => false,
    ])
@endforeach
