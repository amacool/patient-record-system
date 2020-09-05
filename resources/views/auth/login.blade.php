@extends('layout')

@section('content')
<div class="container">
  <div class="row">
    <div class="col-sm-6 col-md-4 col-md-offset-4">
      <h1 class="text-center login-title">{{ __('Innlogging') }}</h1>
      <hr />

      <form method="POST" action="{{ route('login-users') }}">
        @csrf

        <div>
          <label for="email">{{ __('Brukernavn') }}</label>
          <input type="text" name="email" class="form-control" value="{{ old('email') }}">
          <!-- @error('email')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
          @enderror -->
        </div>
        <hr />
        <div>
          <label for="password">{{ __('Passord') }}</label>
          <input type="password" name="password" id="password" class="form-control">
          <!-- @error('password')
            <span class="invalid-feedback" role="alert">
              <strong>{{ $message }}</strong>
            </span>
          @enderror -->
        </div>
        <hr />
        <div>
          <button type="submit" class="btn btn-lg btn-primary btn-block">{{ __('Logg inn') }}</button>
        </div>
      </form>
    </div>
  </div>
</div>
@stop