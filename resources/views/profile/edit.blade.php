@extends('layouts.app')

@section('title', 'Profile')

@section('content')
    <div class="mx-auto max-w-4xl space-y-6">
        <div class="rounded-xl bg-white p-6 shadow">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            @include('profile.partials.update-password-form')
        </div>

        <div class="rounded-xl bg-white p-6 shadow">
            @include('profile.partials.delete-user-form')
        </div>
    </div>
@endsection
