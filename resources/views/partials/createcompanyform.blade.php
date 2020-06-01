<div class="col-md-9">
    <div class="form-group">
        {!! Form::label('name', 'Firmanavn: ') !!}
        {!! Form::text('name', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('orgnr', 'Organisasjonsnummer: ') !!}
        {!! Form::text('orgnr', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="col-md-4">
    <div class="form-group">
        {!! Form::label('seats', 'Antall brukere: ') !!}
        {!! Form::text('seats', null, ['class' => 'form-control']) !!}
    </div>
</div>

<div class="col-md-12">
    {!! Form::submit('Lagre') !!}
</div>