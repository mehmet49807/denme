@extends('layouts.admin')

@section('content')
    <p class="section-copy">Right-side admin menu active</p>
    <h1>{{ $section ?? 'Admin Dashboard' }}</h1>
    <div class="admin-grid">
        <article class="admin-card">
            <h2>User Management</h2>
            <p>View, edit, ban, or delete registered users while preserving immutable usernames.</p>
        </article>
        <article class="admin-card">
            <h2>Message Auditor</h2>
            <p>Read user-to-user messages for safety and abuse investigations.</p>
        </article>
        <article class="admin-card">
            <h2>Complaints/Reports Dashboard</h2>
            <p>Process Sikayet tickets, update statuses, and add internal notes.</p>
        </article>
        <article class="admin-card">
            <h2>Premium Tracker</h2>
            <p>Track active male premium users, package distribution, and TRY revenue.</p>
        </article>
        <article class="admin-card">
            <h2>Admin Broadcast System</h2>
            <p>Send official system messages to all users or filtered audiences.</p>
        </article>
    </div>
@endsection
