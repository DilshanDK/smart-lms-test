@extends('layouts.app')


@section('content')
    <div id="admin-home">
        <!-- Dynamic Content -->
        <div class="container">
            @if (request()->routeIs('admin.dashboard'))
                @include('users.admin.contents.dashboard')
            @elseif (request()->routeIs('admin.usermanage'))
                @include('users.admin.contents.user-management')
            @elseif (request()->routeIs('admin.course-management'))
                <h1>Course Management</h1>
                <p>Manage courses, approve new course requests, and more.</p>
            @elseif (request()->routeIs('admin.analytics'))
                <h1>Analytics</h1>
                <p>View platform analytics and usage statistics.</p>
            @else
                <h1>Welcome to Admin Dashboard</h1>
                <p>Select an option from the navigation bar to get started.</p>
            @endif
        </div>
    </div>
@endsection
