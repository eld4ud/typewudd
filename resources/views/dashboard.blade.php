@extends('layout')
@section('title','Dashboard')
@section('content')
    <section class="pagehead">
        <div class="eyebrow">YOUR PERFORMANCE</div>
        <h1>
            Hi, {{auth()->user()->name}}.
        </h1>
        <p>Setiap ketikan adalah progres.</p>
    </section>
    <div class="cards">
        <article class="card">
            <span class="number">PERSONAL BEST</span>
            <h2>
                {{$best}}
                <small>WPM</small>
            </h2>
        </article>
        <article class="card">
            <span class="number">AVERAGE SPEED</span>
            <h2>
                {{$avg}}
                <small>WPM</small>
            </h2>
        </article>
        <article class="card">
            <span class="number">TOTAL TESTS</span>
            <h2>
                {{auth()->user()->results()->count()}}
            </h2>
        </article>
    </div>
    <section class="panel">
        <h2>Typing Goal</h2>
        <form method="post" action="/goals" class="inlineform">
            @csrf
            <label>
                Target WPM
                <input type="number" name="target_wpm" min="10" max="250" value="{{$goal}}">
            </label>
            <button class="btn">Save Goal</button>
        </form>
    </section>
    <section class="panel tablewrap">
        <h2>Recent Results</h2>
        <table>
            <thead>
                <tr>
                    <th>Date</th>
                    <th>Mode</th>
                    <th>Difficulty</th>
                    <th>WPM</th>
                    <th>Accuracy</th>
                </tr>
            </thead>
            <tbody>
                @forelse($results as $result)
                    <tr>
                        <td>
                            {{$result->created_at->format('d M Y')}}
                        </td>
                        <td>{{$result->mode}}</td>
                        <td>{{$result->difficulty}}</td>
                        <td>{{$result->wpm}}</td>
                        <td>{{$result->accuracy}}%</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Belum ada riwayat tes.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </section>
    <section class="panel">
        <h2>Report a Problem</h2>
        <form method="post" action="/report">
            @csrf
            <label>
                Subject
                <input name="subject" required maxlength="150">
            </label>
            <label>
                Message
                <textarea name="message" minlength="10" required></textarea>
            </label>
            <button class="btn">Send Report</button>
        </form>
    </section>
@endsection
