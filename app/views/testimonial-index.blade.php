<main id="content" class="sub col-xs-12 col-sm-8 left-column" style="padding-top: 0;" role="main">
    <div class="page">
        @foreach ($testimonials as $testimonial)
            <div class="dsq-widget-comment">
                <p class="dsq-comment-content">&ldquo;{{ $testimonial->message }}&rdquo;&mdash;{{ $testimonial->author->name }}</p>
            </div>
        @endforeach
    </div>
</main>
@include('asides.bible')