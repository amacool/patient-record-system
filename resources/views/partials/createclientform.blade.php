<div class="col-md-6">
  <div class="form-group">
    {{--street_address--}}
    {!! Form::label('gvdd85c01k', 'Gateadresse: ') !!}
    {!! Form::text('gvdd85c01k', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--postal_code--}}
    {!! Form::label('esrc80j3sc', 'Postnummer: ') !!}
    {!! Form::text('esrc80j3sc', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--city--}}
    {!! Form::label('753lqcsbk4', 'By: ') !!}
    {!! Form::text('753lqcsbk4', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--phone--}}
    {!! Form::label('s7tjrdoliy', 'Telefonnummer: ') !!}
    {!! Form::text('s7tjrdoliy', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--civil_status--}}
    {!! Form::label('g9npeyap1v', 'Sivilstatus: ') !!}
    {!! Form::text('g9npeyap1v', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--work_status--}}
    {!! Form::label('vzjvte5v96', 'Arbeidsstatus: ') !!}
    {!! Form::text('vzjvte5v96', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--medication--}}
    {!! Form::label('ulij51r2f9', 'Medisiner: ') !!}
    {!! Form::text('ulij51r2f9', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--closest_relative--}}
    {!! Form::label('3p1jm4zdyp', 'Nærmeste pårørende: ') !!}
    {!! Form::text('3p1jm4zdyp', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--closest_relative_phone--}}
    {!! Form::label('feucqwf7cx', 'Nærmeste pårørende (tlf-nr): ') !!}
    {!! Form::text('feucqwf7cx', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--children--}}
    {!! Form::label('7hvwzk7f7t', 'Barn: ') !!}
    {!! Form::text('7hvwzk7f7t', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-6">
  <div class="form-group">
    {{--gp--}}
    {!! Form::label('241i88imq9', 'Fastlege: ') !!}
    {!! Form::text('241i88imq9', null, ['class' => 'form-control']) !!}
  </div>
</div>

{{--<div class="col-md-6">
    <div class="form-group">
        --}}{{--individual_plan--}}{{--
        {!! Form::label('wlj5betr3c', 'Individuell plan: ') !!}
        {!! Form::text('wlj5betr3c', null, ['class' => 'form-control']) !!}
    </div>
</div>--}}

<div class="col-md-12">
  <div class="form-group">
    {{--other_info--}}
    {!! Form::label('cya9753ajt', 'Annen viktig info: ') !!}
    {!! Form::textarea('cya9753ajt', null, ['class' => 'form-control']) !!}
  </div>
</div>

<div class="col-md-12">
  {!! Form::submit('Lagre') !!}
</div>
