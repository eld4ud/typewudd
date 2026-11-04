<?php

namespace App\Http\Controllers;

use App\Models\DuelRoom;
use App\Models\TypingContent;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DuelController
{
    private function participant(DuelRoom $room, Request $request): string
    {
        $userId = (int) $request->user()->id;
        $hostId = (int) $room->host_id;
        $guestId = $room->guest_id === null ? null : (int) $room->guest_id;

        abort_unless($userId === $hostId || $userId === $guestId, 403);

        return $userId === $hostId ? 'host' : 'guest';
    }

    public function index()
    {
        return view('duels.index');
    }

    public function create(Request $request)
    {
        $data = $request->validate([
            'difficulty' => 'required|in:easy,normal,hard,expert',
            'language' => 'required|in:id,en',
            'duration' => 'required|in:15,30,60,120',
        ]);

        $content = TypingContent::where([
            'difficulty' => $data['difficulty'],
            'language' => $data['language'],
            'is_active' => true,
        ])->firstOrFail();

        do {
            $code = strtoupper(Str::random(7));
        } while (DuelRoom::where('code', $code)->exists());

        $room = DuelRoom::create([
            'code' => $code,
            'host_id' => $request->user()->id,
            'typing_content_id' => $content->id,
        ] + $data);

        return redirect('/duel/' . $room->code);
    }

    public function join(Request $request)
    {
        $data = $request->validate([
            'code' => 'required|string|max:12',
        ]);

        $room = DB::transaction(function () use ($data, $request) {
            $room = DuelRoom::where('code', strtoupper($data['code']))
                ->lockForUpdate()
                ->first();

            if (
                !$room ||
                $room->status !== 'waiting' ||
                ($room->guest_id && (int) $room->guest_id !== (int) $request->user()->id)
            ) {
                return null;
            }

            if ((int) $room->host_id !== (int) $request->user()->id && !$room->guest_id) {
                $room->guest_id = $request->user()->id;
                $room->save();
            }

            return $room;
        });

        return $room
            ? redirect('/duel/' . $room->code)
            : back()->withErrors(['code' => 'Room tidak tersedia atau sudah penuh.']);
    }

    public function show(Request $request, string $code)
    {
        $room = DuelRoom::with(['content', 'host', 'guest'])
            ->where('code', $code)
            ->firstOrFail();

        $side = $this->participant($room, $request);

        return view('duels.show', compact('room', 'side'));
    }

    public function state(Request $request, string $code)
    {
        $room = DuelRoom::with(['host', 'guest'])
            ->where('code', $code)
            ->firstOrFail();
        $this->participant($room, $request);

        if (
            $room->status === 'playing' &&
            now()->greaterThan($room->ends_at->copy()->addSeconds(5))
        ) {
            $room->status = 'finished';
            $room->save();
        }

        return response()->json([
            'status' => $room->status,
            'host' => $room->host?->name,
            'guest' => $room->guest?->name,
            'host_ready' => $room->host_ready,
            'guest_ready' => $room->guest_ready,
            'host_progress' => $room->host_progress,
            'guest_progress' => $room->guest_progress,
            'starts_at' => $room->starts_at?->toIso8601String(),
            'ends_at' => $room->ends_at?->toIso8601String(),
            'host_result' => $room->host_result,
            'guest_result' => $room->guest_result,
        ]);
    }

    public function ready(Request $request, string $code)
    {
        DB::transaction(function () use ($request, $code) {
            $room = DuelRoom::where('code', $code)
                ->lockForUpdate()
                ->firstOrFail();

            $side = $this->participant($room, $request);
            abort_unless($room->status === 'waiting', 409);

            $room->{$side . '_ready'} = true;

            if ($room->guest_id && $room->host_ready && $room->guest_ready) {
                $room->status = 'playing';
                $room->starts_at = now()->addSeconds(4);
                $room->ends_at = $room->starts_at->copy()->addSeconds($room->duration);
            }

            $room->save();
        });

        return response()->json(['ok' => true]);
    }

    public function progress(Request $request, string $code)
    {
        $data = $request->validate(['typed' => 'present|nullable|string|max:5000']);
        $room = DuelRoom::with('content')->where('code', $code)->firstOrFail();
        $side = $this->participant($room, $request);

        abort_unless(
            $room->status === 'playing' &&
            now()->greaterThanOrEqualTo($room->starts_at) &&
            now()->lessThanOrEqualTo($room->ends_at->copy()->addSeconds(3)),
            409
        );

        $expected = $room->content->body;
        $actual = $data['typed'] ?? '';
        abort_if(mb_strlen($actual) > mb_strlen($expected), 422);

        $correct = $this->correctCharacters($actual, $expected);
        $room->{$side . '_progress'} = $correct;
        $room->save();

        return response()->json(['ok' => true]);
    }

    public function finish(Request $request, string $code)
    {
        $data = $request->validate(['typed' => 'present|nullable|string|max:5000']);

        DB::transaction(function () use ($request, $code, $data) {
            $room = DuelRoom::with('content')
                ->where('code', $code)
                ->lockForUpdate()
                ->firstOrFail();

            $side = $this->participant($room, $request);
            abort_unless(in_array($room->status, ['playing', 'finished'], true), 409);
            abort_unless(now()->greaterThanOrEqualTo($room->ends_at), 409, 'Waktu belum selesai.');

            if ($room->{$side . '_result'} !== null) {
                return;
            }

            $expected = $room->content->body;
            $actual = $data['typed'] ?? '';
            $length = mb_strlen($actual);
            abort_if($length > mb_strlen($expected), 422);

            $correct = $this->correctCharacters($actual, $expected);
            $wpm = round($correct / 5 / ($room->duration / 60), 2);

            $room->{$side . '_result'} = [
                'wpm' => $wpm,
                'accuracy' => $length ? round(100 * $correct / $length, 2) : 0,
                'correct_chars' => $correct,
            ];

            if ($room->host_result !== null && $room->guest_result !== null) {
                $room->status = 'finished';
            }

            $room->save();
        });

        return response()->json(['ok' => true]);
    }

    private function correctCharacters(string $actual, string $expected): int
    {
        $correct = 0;

        for ($index = 0; $index < mb_strlen($actual); $index++) {
            if (mb_substr($actual, $index, 1) === mb_substr($expected, $index, 1)) {
                $correct++;
            }
        }

        return $correct;
    }
}
