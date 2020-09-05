<div class="col-md-12">
  <div class="form-group">
    {!! Form::label('title', 'Tittel: ') !!}
    {!! Form::text('title', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-12">
  <div class="form-group">
    {!! Form::label('content', 'Innhold: ') !!}
    {!! Form::textarea('content', null, ['class' => 'form-control']) !!}
  </div>
</div>
<script>
  // Replace the <textarea id="content"> with a CKEditor
  // instance, using default configuration.
  CKEDITOR.replace('content');
</script>

<div class="col-md-12">
  {!! Form::submit('Lagre') !!}
</div>