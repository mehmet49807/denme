@extends('layouts.app-with-sidebar')

@section('title', 'Hobiler — ' . __('app.brand'))

@section('app-content')
<div class="profile-settings-page feed-container">
    @include('partials.settings-page-header', ['title' => 'Hobiler'])

    <div class="profile-settings-page-body">
        <form method="POST" action="{{ route('profile.update') }}" class="profile-settings-form">
            @csrf
            @method('PUT')
            <input type="hidden" name="settings_panel" value="hobbies">

            @include('partials.hobbies-picker', ['selectedHobbies' => old('hobbies', $user->hobbies ?? [])])

            <button type="submit" class="btn btn-primary btn-full">Hobileri Kaydet</button>
        </form>
    </div>
</div>
@endsection

@push('page-scripts')
<script src="{{ asset('js/hobbies-picker.js') }}?v=hobbies-1"></script>
@endpush
