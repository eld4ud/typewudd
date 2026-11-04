@extends('layout')
@section('title','Admin')
@section('content')
    <section class="pagehead">
        <div class="eyebrow">TYPEWUD MANAGEMENT</div>
        <h1>Admin Dashboard.</h1>
    </section>
    <div class="cards">
        <article class="card">
            <span class="number">USERS</span>
            <h2>{{$counts['users']}}</h2>
        </article>
        <article class="card">
            <span class="number">TESTS</span>
            <h2>{{$counts['tests']}}</h2>
        </article>
        <article class="card">
            <span class="number">DUEL ROOMS</span>
            <h2>{{$counts['rooms']}}</h2>
        </article>
    </div>
    <section class="panel">
        <h2>Add Typing Content</h2>
        <form method="post" action="/admin/content">
            @csrf
            <label>
                Title
                <input name="title" required>
            </label>
            <label>
                Text
                <textarea name="body" required minlength="20"></textarea>
            </label>
            <div class="settings">
                <label>
                    Language
                    <select name="language">
                        <option value="en">English</option>
                        <option value="id">Indonesia</option>
                    </select>
                </label>
                <label>
                    Difficulty
                    <select name="difficulty">
                        @foreach(['easy','normal','hard','expert'] as $d)
                            <option>{{$d}}</option>
                        @endforeach
                    </select>
                </label>
            </div>
            <button class="btn">Add Content</button>
        </form>
    </section>
    <section class="panel tablewrap">
        <h2>Typing Contents</h2>
        <table>
            <tr>
                <th>Title</th>
                <th>Language</th>
                <th>Difficulty</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            @foreach($contents as $c)
                <tr>
                    <td>{{$c->title}}</td>
                    <td>{{$c->language}}</td>
                    <td>{{$c->difficulty}}</td>
                    <td>{{$c->is_active?'Active':'Disabled'}}</td>
                    <td>
                        <form method="post" action="/admin/content/{{$c->id}}/toggle">
                            @csrf
                            <button class="link">Toggle</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </table>
    </section>
    <section class="panel tablewrap">
        <h2>Users</h2>
        <table>
            <tr>
                <th>Username</th>
                <th>Email</th>
                <th>Role</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            @foreach($users as $u)
                <tr>
                    <td>{{$u->name}}</td>
                    <td>{{$u->email}}</td>
                    <td>{{$u->role}}</td>
                    <td>{{$u->is_active?'Active':'Disabled'}}</td>
                    <td>
                        @if($u->id!==auth()->id())
                            <form method="post" action="/admin/users/{{$u->id}}/toggle">
                                @csrf
                                <button class="link">Toggle</button>
                            </form>
                        @endif
                    </td>
                </tr>
            @endforeach
        </table>
    </section>
    <section class="panel tablewrap">
        <h2>Problem Reports</h2>
        <table>
            <tr>
                <th>Date</th>
                <th>Subject</th>
                <th>Message</th>
            </tr>
            @foreach($reports as $report)
                <tr>
                    <td>{{$report->created_at}}</td>
                    <td>{{$report->subject}}</td>
                    <td>{{$report->message}}</td>
                </tr>
            @endforeach
        </table>
    </section>
@endsection
