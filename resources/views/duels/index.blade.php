@extends('layout')
@section('title','Friendly Duel')
@section('content')
    <section class="pagehead">
        <div class="eyebrow">JUST FOR FUN · NO RANKING</div>
        <h1>Friendly Duel.</h1>
        <p>BY1 antar teman, tanpa leaderboard khusus. Dua pemain, satu teks, satu pemenang.</p>
    </section>
    <div class="cards two">
        <section class="panel">
            <h2>Create Room</h2>
            <form method="post" action="/duel/create">
                @csrf
                <label>
                    Difficulty
                    <select name="difficulty">
                        @foreach(['easy','normal','hard','expert'] as $d)
                            <option @selected($d==='normal')>{{$d}}</option>
                        @endforeach
                    </select>
                </label>
                <label>
                    Language
                    <select name="language">
                        <option value="en">English</option>
                        <option value="id">Indonesia</option>
                    </select>
                </label>
                <label>
                    Duration
                    <select name="duration">
                        <option>15</option>
                        <option>30</option>
                        <option selected>60</option>
                        <option>120</option>
                    </select>
                </label>
                <button class="btn full">Create Room ↗</button>
            </form>
        </section>
        <section class="panel">
            <h2>Join Room</h2>
            <form method="post" action="/duel/join">
                @csrf
                <label>
                    Room Code
                    <input name="code" required maxlength="12" placeholder="ABCD123">
                </label>
                <button class="btn full">Join Duel ↗</button>
            </form>
        </section>
    </div>
@endsection
