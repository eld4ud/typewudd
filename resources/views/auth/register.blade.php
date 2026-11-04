@extends('layout')
@section('title','Register')
@section('content')
    <section class="panel narrow">
        <div class="eyebrow">JOIN TYPEWUD</div>
        <h1>Create account.</h1>
        <form method="post" action="/register">
            @csrf
            <label>
                Username
                <input name="name" required minlength="3" maxlength="40" value="{{old('name')}}">
            </label>
            <label>
                Email
                <input name="email" type="email" required value="{{old('email')}}">
            </label>
            <label>
                Password (minimal 8 karakter)
                <input name="password" type="password" required minlength="8">
            </label>
            <label>
                Confirm Password
                <input name="password_confirmation" type="password" required minlength="8">
            </label>
            <button class="btn full">Register</button>
        </form>
    </section>
@endsection
