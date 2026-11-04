@extends('layout')
@section('title', 'Home')

@section('content')
    <section class="hero-resend">
        <div class="hero-bg-glow"></div>

        <div class="hero-left">
            <div class="hero-badge">Typewud</div>

            <h1 class="hero-title">
                Typing for<br>
                developers
            </h1>

            <p class="hero-desc">
                The best way to improve your typing speed, accuracy,
                and consistency. Practice, track your progress, and
                enjoy friendly duels with your friends.
            </p>

            <div class="hero-buttons">
                <a href="/typing-test" class="hero-btn hero-btn-primary">Get Started</a>
                <a href="/leaderboard" class="hero-btn hero-btn-ghost">Leaderboard</a>
            </div>
        </div>

        <div class="hero-right">
    <div class="hero-model-wrap">
            <model-viewer
                src="{{ asset('models/typewud-keyboard.glb') }}"
                alt="Typewud 3D Mechanical Keyboard"
                auto-rotate
                auto-rotate-delay="1000"
                rotation-per-second="15deg"
                camera-controls
                disable-pan
                shadow-intensity="1"
                exposure="1.2"
                interaction-prompt="none"
                class="keyboard-model">
            </model-viewer>
        </div>
    </div>
    </section>

    <div class="hero-bottom-cta">
        <a href="/typing-test" class="see-similar-btn">
            Start typing
            <span>↓</span>
        </a>
    </div>
@endsection