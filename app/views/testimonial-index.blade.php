<main id="content" class="sub col-xs-12 col-sm-8 left-column" style="padding-top: 0;" role="main">

    <h1 class="title-chapters">Testimonials</h1>

    <div class="page">
        @foreach ($testimonials as $testimonial)
            <div class="dsq-widget-comment">
                <p class="dsq-comment-content">&ldquo;{{ $testimonial->message }}&rdquo;&mdash;{{ $testimonial->author->name }}</p>
            </div>
        @endforeach
        @if (isset($prev))
            <a id="nav-left-disqus" class="btn btn-default fa fa-angle-left" href="{{ $app_url }}/testimonials"></a>
        @else
            <a id="nav-left-disqus" class="btn btn-default fa fa-angle-left disabled" href="#"></a>
        @endif
        @if (isset($next))
            <a id="nav-right-disqus" class="btn btn-default fa fa-angle-right" href="{{ $app_url }}/testimonials"></a>
        @else
            <a id="nav-right-disqus" class="btn btn-default fa fa-angle-right disabled" href="#"></a>
        @endif
    </div>

    @if (isset($message))
        <div class="alert alert-success" role="alert">
            {{{$message}}}
        </div>
    @endif

    @if (isset($errors))
        @foreach ($errors->all() as $error)
            <div class="alert alert-danger" role="alert">
                {{{$error}}}
            </div>
        @endforeach
    @endif

    <hr>

    <h3 class="title-chapters">Submit Your Testimonial</h3>

    {{ Form::open(array('url' => $app_url . '/testimonials/submit', 'action' => 'TestimonialController@SubmitTestimonialForm')) }}

        {{ Form::openGroup('title', 'Full Name') }}
            {{ Form::text('full_name', (!empty($input_data['full_name'])) ? $input_data['full_name'] : '', array('placeholder' => 'Full Name', 'id' => 'full_name')) }}
        {{ Form::closeGroup() }}

        {{ Form::openGroup('title', 'Email') }}
            {{ Form::text('email', (!empty($input_data['email'])) ? $input_data['email'] : '', array('placeholder' => 'Email', 'id' => 'email')) }}
        {{ Form::closeGroup() }}

        {{ Form::openGroup('title', 'Phone Number') }}
            {{ Form::text('phone_number', (!empty($input_data['phone_number'])) ? $input_data['phone_number'] : '', array('placeholder' => 'Phone Number', 'id' => 'phone_number')) }}
        {{ Form::closeGroup() }}

        {{ Form::openGroup('title', 'Testimonial') }}
            {{ Form::textarea ('body', (!empty($input_data['body'])) ? $input_data['body'] : '', array('placeholder' => 'Testimonial', 'class' => 'form-control', 'id' => 'body', 'rows' => '4' )) }}
        {{ Form::closeGroup() }}

        <div class="modal-footer">
            {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
        </div>

    {{ Form::close() }}
</main>
@include('asides.bible')