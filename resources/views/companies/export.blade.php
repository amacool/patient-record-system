@extends('layout')

@section('content')

  <h3>Export company data</h3>

  <section>
    <div class="container">
      {!! Form::model($company, array('route' => array('companies.upload', $company->id), 'method' => 'post')) !!}

      <div class="col-md-12">
        <table class="table">
          <tr>
            <th></th>
            <th>Navn</th>
            <th>Email</th>
            <th>Telefon</th>
          </tr>
          @foreach ($company->user as $user)
            <tr>
              <td><input type="checkbox" name="check_{{$user->id}}" value="{{$user->id}}"></td>
              <td>{{ $user->name }}</td>
              <td>{{ $user->email }}</td>
              <td>{{ $user->phone }}</td>
            </tr>
          @endforeach
        </table>
      </div>

      <div class="col-md-12 export-section">
        <div class="form-check">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="export_csv" checked>
            CSV
          </label>
        </div>
        <div class="form-check">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="export_uploaded_pdf">
            Uploaded PDFs
          </label>
        </div>
        <div class="form-check">
          <label class="form-check-label">
            <input type="checkbox" class="form-check-input" name="export_records_pdf">
            PDFs with Client info and records
          </label>
        </div>
      </div>

      <div class="col-md-12">
        <button class="btn btn-danger">Export</button>
        @if (session('downloadLink'))
          <a class="btn-download" href="{{ route('companies.download', [$company->id, session('downloadLink')]) }}" target="_blank">Download</a>
        @endif
      </div>

      {!! Form::close() !!}

    </div>
  </section>

@stop
