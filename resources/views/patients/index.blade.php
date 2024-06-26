@extends('layouts.app')
@section('title')
    {{ __('messages.patients') }}
@endsection
@section('content')
    <div class="container-fluid">
        <div class="d-flex flex-column">
            @include('flash::message')
            {{Form::hidden('patientUrl',url('patients'),['id'=>'indexPatientUrl'])}}
            {{ Form::hidden('patients', __('messages.advanced_payment.patient'), ['id' => 'Patients']) }}
            @if (getLoggedInUser()->hasRole("Doctor"))
                <livewire:doctor-patient-table docId="{{ getLoggedInUser()->owner_id }}"/>
            @else
                <livewire:patient-table/>
            @endif

            @include('accountants.templates.templates')
            @include('partials.page.templates.templates')
        </div>
    </div>
@endsection
{{-- JS File :- assets/js/patients/patients.js --}}
