@extends('layout')
@section('title', 'Duel Room')

@section('content')
    <section class="pagehead">
        <div class="eyebrow">PRIVATE FRIENDLY DUEL</div>
        <h1>Room {{ $room->code }}</h1>
        <p>Bagikan kode room ini ke teman. Kedua pemain harus menekan Ready.</p>
    </section>

    <section class="panel">
        <div class="metrics">
            <div>
                <b id="hostName">{{ $room->host->name }}</b>
                <small>HOST · <span id="hostState">WAITING</span></small>
            </div>

            <div>
                <b>VS</b>
                <small id="clock">WAITING</small>
            </div>

            <div>
                <b id="guestName">{{ $room->guest?->name ?? 'Waiting...' }}</b>
                <small>GUEST · <span id="guestState">WAITING</span></small>
            </div>
        </div>

        <div class="progressgrid">
            <div>
                <small>HOST PROGRESS</small>
                <div class="bar"><div id="hostBar"></div></div>
            </div>
            <div>
                <small>GUEST PROGRESS</small>
                <div class="bar"><div id="guestBar"></div></div>
            </div>
        </div>

        <div class="typing-stage" id="duelStage" tabindex="0" aria-label="Klik teks untuk mengetik saat duel dimulai">
            <div class="inline-typing-text" id="duelPrompt" aria-hidden="true"></div>
            <textarea
                id="duelInput"
                class="typing-capture"
                spellcheck="false"
                autocomplete="off"
                autocorrect="off"
                autocapitalize="off"
                aria-label="Ketik teks duel"
                disabled
            ></textarea>
        </div>

        <p class="typing-hint">Teks dapat diketik setelah kedua pemain siap dan hitungan mundur selesai.</p>

        <div class="actions">
            <button class="btn" id="ready" type="button">I'm Ready</button>
            <a href="/duel" class="btn outline">Leave Room</a>
        </div>

        <div id="duelResult" class="result" role="status" aria-live="polite" hidden></div>
    </section>

    <script>
        window.TYPEWUD_DUEL = {
            code: @json($room->code),
            side: @json($side),
            duration: {{ $room->duration }},
            text: @json($room->content->body),
        };
    </script>
@endsection

@section('scripts')
    <script src="{{ asset('js/inline-typing.js') }}" defer></script>
    <script src="{{ asset('js/duel.js') }}" defer></script>
@endsection
