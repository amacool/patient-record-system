{!! Form::hidden('client_id', $client->id) !!}

{!! Form::hidden('category_id', '1') !!}

{{--<div class="col-md-12">
  <div class="form-group">
      {!! Form::label('category_id', 'Journal note: ') !!}
      {!! Form::radio('category_id', '1', true) !!}
      {!! Form::label('category_id', 'Treatment Plan: ') !!}
      {!! Form::radio('category_id', '2') !!}
      {!! Form::label('category_id', 'Report: ') !!}
      {!! Form::radio('category_id', '3') !!}
  </div>
</div>--}}

<div class="col-md-6">
  <div class="form-group">
    {!! Form::label('date', 'Dato for avtalen: (dd-mm-åååå)') !!}
    {!! Form::text('app_date', \Carbon\Carbon::now()->format('d-m-Y'), ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {!! Form::label('title', 'Tittel: ') !!}
    {!! Form::text('title', $user->standard_title, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-12">
  <div class="form-group">
    {!! Form::label('content', 'Innhold: ') !!}
    {!! Form::textarea('content', $template->content, ['class' => 'form-control']) !!}
  </div>
  <script>
    // Replace the <textarea id="content"> with a CKEditor
    // instance, using default configuration.
    CKEDITOR.replace('content');
  </script>
</div>



<div class="col-md-12">
  {!! Form::submit('Lagre') !!}
</div>