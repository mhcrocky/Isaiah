<main id="content" class="sub col-xs-12 col-sm-8 left-column" style="padding-top: 0;" role="main">
    <div class="page">

        <h1 class="title-chapters">Contact</h1>

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

        {{ Form::open(array('action' => 'ContactController@SubmitContactForm')) }}

        {{ Form::openGroup('title', 'Full Name') }}
        {{ Form::text('full_name', (!empty($input_data['full_name'])) ? $input_data['full_name'] : '', array('placeholder' => 'Full Name', 'id' => 'full_name')) }}
        {{ Form::closeGroup() }}

        {{ Form::openGroup('title', 'Email') }}
        {{ Form::text('email', (!empty($input_data['email'])) ? $input_data['email'] : '', array('placeholder' => 'Email', 'id' => 'email')) }}
        {{ Form::closeGroup() }}

        {{ Form::openGroup('title', 'Phone Number') }}
        {{ Form::text('phone_number', (!empty($input_data['phone_number'])) ? $input_data['phone_number'] : '', array('placeholder' => 'Phone Number', 'id' => 'phone_number')) }}
        {{ Form::closeGroup() }}

        {{ Form::openGroup('title', 'Message') }}
        {{ Form::textarea ('body', (!empty($input_data['body'])) ? $input_data['body'] : '', array('placeholder' => 'Message', 'class' => 'form-control', 'id' => 'body', 'rows' => '4' )) }}
        {{ Form::closeGroup() }}

        <div class="modal-footer">
        {{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
        </div>

        {{ Form::close() }}
    </div>
</main>
{{--{{> asides.resource-index}}--}}
{{--{{> asides.bible}}--}}
@include('asides.bible')