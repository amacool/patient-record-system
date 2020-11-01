@extends('layout')

@section('content')

@include('partials.clientsidebar')

<div class="col-md-9 col-md-offset-3">

  <h4>Endre personlig info for {{$client->firstname}} {{$client->lastname}} - ({{$client->born->format('d.m.Y')}} {{$client->ssn}})</h4>
  <hr />

  {!! Form::model($client, array('route' => array('clients.update', $client->id), 'method' => 'put', 'autocomplete' => 'off')) !!}

  @if ($user->role === 2)
  <div class="col-md-12">
    <div class="form-group">
      {{--firstname--}}
      {!! Form::label('dhjwhq3v7j', 'Fornavn: ') !!}
      {!! Form::text('dhjwhq3v7j', $client->firstname, ['class' => 'form-control']) !!}

    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {{--lastname--}}
      {!! Form::label('6x93mscfgo', 'Etternavn: ') !!}
      {!! Form::text('6x93mscfgo', $client->lastname, ['class' => 'form-control']) !!}

    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      {{--born--}}
      {!! Form::label('i2hmibi8a5', 'Født: ') !!}
      {!! Form::text('i2hmibi8a5', $client->born->format('d.m.Y'), ['class' => 'form-control']) !!}

    </div>
  </div>
  @endif

  @if ($client->ssn == "11111" OR $user->role === 2)
  <div class="col-md-12">
    <div class="form-group">
      {{--ssn--}}
      {!! Form::label('0rpk6x0uoe', 'Fødselsnummer: ') !!}
      {!! Form::text('0rpk6x0uoe', $client->ssn, ['class' => 'form-control']) !!}

    </div>
  </div>
  @endif

  <div class="col-md-6">
    <div class="form-group">
      {{--street_address--}}
      {!! Form::label('gvdd85c01k', 'Gateadresse: ') !!}
      {!! Form::text('gvdd85c01k', $client->street_address, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--postal_code--}}
      {!! Form::label('esrc80j3sc', 'Postnummer: ') !!}
      {!! Form::text('esrc80j3sc', $client->postal_code, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--city--}}
      {!! Form::label('753lqcsbk4', 'By: ') !!}
      {!! Form::text('753lqcsbk4', $client->city, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--phone--}}
      {!! Form::label('s7tjrdoliy', 'Telefonnummer: ') !!}
      {!! Form::text('s7tjrdoliy', $client->phone, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--civil_status--}}
      {!! Form::label('g9npeyap1v', 'Sivilstatus: ') !!}
      {!! Form::text('g9npeyap1v', $client->civil_status, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--work_status--}}
      {!! Form::label('vzjvte5v96', 'Arbeidsstatus: ') !!}
      {!! Form::text('vzjvte5v96', $client->work_status, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--medication--}}
      {!! Form::label('ulij51r2f9', 'Medisiner: ') !!}
      {!! Form::text('ulij51r2f9', $client->medication, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--closest_relative--}}
      {!! Form::label('3p1jm4zdyp', 'Nærmeste pårørende: ') !!}
      {!! Form::text('3p1jm4zdyp', $client->closest_relative, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--closest_relative_phone--}}
      {!! Form::label('feucqwf7cx', 'Nærmeste pårørende (tlf-nr): ') !!}
      {!! Form::text('feucqwf7cx', $client->closest_relative_phone, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--children--}}
      {!! Form::label('7hvwzk7f7t', 'Barn: ') !!}
      {!! Form::text('7hvwzk7f7t', $client->children, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-6">
    <div class="form-group">
      {{--gp--}}
      {!! Form::label('241i88imq9', 'Fastlege: ') !!}
      {!! Form::text('241i88imq9', $client->gp, ['class' => 'form-control']) !!}
    </div>
  </div>

  {{--<div class="col-md-6">
            <div class="form-group">
                --}}{{--individual_plan--}}{{--
                {!! Form::label('wlj5betr3c', 'Individuell plan: ') !!}
                {!! Form::text('wlj5betr3c', $client->individual_plan, ['class' => 'form-control']) !!}
            </div>
        </div>--}}

  <div class="col-md-12">
    <div class="form-group">
      {{--other_info--}}
      {!! Form::label('cya9753ajt', 'Annen viktig info: ') !!}
      {!! Form::textarea('cya9753ajt', $client->other_info, ['class' => 'form-control']) !!}
    </div>
  </div>

  <div class="col-md-12">
    {!! Form::submit('Lagre') !!}
  </div>

  {!! Form::close() !!}

</div>


@stop