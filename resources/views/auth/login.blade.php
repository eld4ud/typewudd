@extends('layout')
@section('title','Login')
@section('content')
    <section class="panel narrow">
        <div class="eyebrow">WELCOME BACK</div>
        <h1>Login.</h1>
        <form method="post" action="/login">
            @csrf
            <label>
                Email
                <input name="email" type="email" required value="{{old('email')}}">
            </label>
            <label>
                Password
                <input name="password" type="password" required>
            </label>
            <button class="btn full">Login</button>
        </form>
        <p>
            Belum punya akun?
            <a href="/register">Register</a>
        </p>
    </section>
@endsection
