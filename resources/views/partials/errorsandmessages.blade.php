@if (count($errors) > 0)
<div class="alert alert-danger">
  <strong>{{ __('Obs:') }}</strong> {{ __('Det er noen problemer med det du har fylt inn') }}<br><br>
  <ul>
    @foreach ($errors->all() as $error)
    <li>{{ $error }}</li>
    @endforeach
  </ul>
</div>
@endif

@if (Session::has('message'))
<div class="alert alert-info">
  <p>{{ Session::get('message') }}</p>
</div>
@endif