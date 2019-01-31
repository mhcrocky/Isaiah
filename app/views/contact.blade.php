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
{{ Form::text('full_name', '', array('placeholder' => 'Full Name', 'id' => 'full_name')) }}
{{ Form::closeGroup() }}

{{ Form::openGroup('title', 'Email') }}
{{ Form::text('email', '', array('placeholder' => 'Email', 'id' => 'email')) }}
{{ Form::closeGroup() }}

{{ Form::openGroup('title', 'Subject') }}
{{ Form::text('subject', '', array('placeholder' => 'Subject', 'id' => 'subject')) }}
{{ Form::closeGroup() }}

{{ Form::openGroup('title', 'Message') }}
{{ Form::textarea ('body', '', array('placeholder' => 'Message', 'class' => 'form-control', 'id' => 'body', 'rows' => '4' )) }}
{{ Form::closeGroup() }}

<div class="modal-footer">
{{ Form::submit('Submit', array('class' => 'btn btn-primary')) }}
</div>

{{ Form::close() }}