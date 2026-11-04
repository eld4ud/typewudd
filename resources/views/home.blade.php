@extends('layout')

@section('content')
<section class="hero">
    <div class="eyebrow">INTERACTIVE TYPING PLATFORM / 2026</div>

    <h1>
        MAKE EVERY<br>
        <em>KEYSTROKE</em> COUNT.
    </h1>

    <p>
        Latih kecepatan dan akurasi mengetik. Pantau progresmu,
        bersaing secara sehat, atau tantang teman dalam duel santai.
    </p>

    <div class="actions">
        <a class="btn" href="/typing-test">Start Typing ↗</a>
        <a class="btn outline" href="/login">Login</a>
        <a class="btn outline" href="/leaderboard">Explore Leaderboard</a>
    </div>
</section>

<section class="cards">
    <article class="card">
        <span class="number">01 /</span>
        <h2>Typing Test</h2>
        <p>
            Easy hingga Expert, Time dan Words Mode,
            dengan hasil WPM dan accuracy.
        </p>
        <a href="/typing-test">Try now ↗</a>
    </article>

    <article class="card">
        <span class="number">02 /</span>
        <h2>Track Progress</h2>
        <p>
            Riwayat tes, personal best, dan target mengetik
            untuk akun member.
        </p>
        <a href="/dashboard">My dashboard ↗</a>
    </article>

    <article class="card">
        <span class="number">03 /</span>
        <h2>Friendly Duel</h2>
        <p>
            Buat room pribadi, undang teman, dan adu kecepatan.
            Tanpa ranking duel.
        </p>
        <a href="/duel">Challenge friend ↗</a>
    </article>
</section>
@endsection
