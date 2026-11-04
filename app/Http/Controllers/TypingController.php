<?php

namespace App\Http\Controllers;

use App\Models\TypingContent;
use App\Models\TypingResult;
use App\Support\TypingPromptBuilder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TypingController
{
    public function index()
    {
        return view('typing');
    }

    public function content(Request $request)
    {
        $data = $request->validate([
            'difficulty' => 'required|in:easy,normal,hard,expert',
            'language' => 'required|in:id,en',
            'mode' => 'required|in:time,words,practice',
            'target' => 'required|integer|min:1|max:120',
        ]);

        $content = TypingContent::query()
            ->where('difficulty', $data['difficulty'])
            ->where('language', $data['language'])
            ->where('is_active', true)
            ->inRandomOrder()
            ->first();

        if (!$content) {
            return response()->json(['message' => 'Konten belum tersedia.'], 404);
        }

        // Each request gets a fresh sentence order. The signed seed lets the
        // server reconstruct precisely the same text when saving the score.
        $seed = random_int(1, 2_000_000_000);
        $issuedAt = now()->timestamp;

        return response()->json([
            'id' => $content->id,
            'difficulty' => $content->difficulty,
            'language' => $content->language,
            'body' => TypingPromptBuilder::build(
                $content->body,
                $content->language,
                $content->difficulty,
                $data['mode'],
                (int) $data['target'],
                $seed
            ),
            'prompt_seed' => $seed,
            'prompt_issued_at' => $issuedAt,
            'prompt_signature' => $this->signPrompt(
                $content->id,
                $data['mode'],
                (int) $data['target'],
                $seed,
                $issuedAt
            ),
        ]);
    }

    /**
     * Bind a random prompt to the content ID, mode, and target using the
     * application's secret key. The signature is verified on score submission.
     */
    private function signPrompt(
        int $contentId,
        string $mode,
        int $target,
        int $seed,
        int $issuedAt
    ): string {
        $message = implode('|', [$contentId, $mode, $target, $seed, $issuedAt]);

        return hash_hmac('sha256', $message, (string) config('app.key'));
    }

    public function save(Request $request)
    {
        $data = $request->validate([
            'typing_content_id' => 'required|exists:typing_contents,id',
            'prompt_seed' => 'required|integer|min:1|max:2000000000',
            'prompt_issued_at' => 'required|integer|min:1',
            'prompt_signature' => 'required|string|size:64|regex:/^[a-f0-9]{64}$/',
            'mode' => 'required|in:time,words,practice',
            'difficulty' => 'required|in:easy,normal,hard,expert',
            'language' => 'required|in:id,en',
            'target' => 'required|integer|min:1|max:120',
            'elapsed_ms' => 'required|integer|min:1000|max:7200000',
            'typed' => 'present|nullable|string|max:5000',
            'input_method' => 'required|in:touch,physical,unknown',
        ]);

        $content = TypingContent::findOrFail($data['typing_content_id']);
        abort_unless(
            $content->is_active &&
            $content->difficulty === $data['difficulty'] &&
            $content->language === $data['language'],
            422,
            'Konten sudah tidak tersedia.'
        );

        $allowedTargets = $data['mode'] === 'words'
            ? [10, 25, 50, 100]
            : [15, 30, 60, 120];

        abort_unless(in_array((int) $data['target'], $allowedTargets, true), 422);

        $issuedAt = (int) $data['prompt_issued_at'];
        $age = now()->timestamp - $issuedAt;
        abort_unless($age >= -60 && $age <= 21600, 422, 'Sesi mengetik sudah kedaluwarsa.');

        $signature = $this->signPrompt(
            $content->id,
            $data['mode'],
            (int) $data['target'],
            (int) $data['prompt_seed'],
            $issuedAt
        );
        abort_unless(hash_equals($signature, $data['prompt_signature']), 422, 'Teks tes tidak valid.');

        $expected = TypingPromptBuilder::build(
            $content->body,
            $content->language,
            $content->difficulty,
            $data['mode'],
            (int) $data['target'],
            (int) $data['prompt_seed']
        );

        $typed = $data['typed'] ?? '';
        $length = mb_strlen($typed);
        $expectedLength = mb_strlen($expected);
        abort_if($length > $expectedLength, 422, 'Panjang hasil tidak valid.');

        $correct = 0;
        for ($index = 0; $index < $length; $index++) {
            if (mb_substr($typed, $index, 1) === mb_substr($expected, $index, 1)) {
                $correct++;
            }
        }

        $errors = $length - $correct;
        $minutes = $data['elapsed_ms'] / 60000;
        $rawWpm = round(($length / 5) / $minutes, 2);
        $netWpm = round(($correct / 5) / $minutes, 2);
        $accuracy = $length ? round($correct / $length * 100, 2) : 0;

        abort_if($rawWpm > 300, 422, 'Kecepatan tidak valid.');

        $correctWords = 0;
        preg_match_all('/\S+/u', $expected, $matches, PREG_OFFSET_CAPTURE);

        foreach ($matches[0] as [$word, $byteOffset]) {
            $position = mb_strlen(substr($expected, 0, $byteOffset));
            $wordLength = mb_strlen($word);

            if (
                $length >= $position + $wordLength &&
                mb_substr($typed, $position, $wordLength) === $word
            ) {
                $correctWords++;
            }
        }

        $finishedWords = $data['mode'] !== 'words' || $length === $expectedLength;
        $finishedTime = $data['mode'] !== 'time' ||
            $data['elapsed_ms'] >= ((int) $data['target'] - 1) * 1000;

        $competitive = $data['mode'] !== 'practice' &&
            $data['elapsed_ms'] >= 10000 &&
            $accuracy >= 50 &&
            $finishedWords &&
            $finishedTime;

        $result = TypingResult::create([
            'user_id' => $request->user()->id,
            'typing_content_id' => $content->id,
            'mode' => $data['mode'],
            'difficulty' => $data['difficulty'],
            'language' => $data['language'],
            'input_method' => $data['input_method'],
            'target' => $data['target'],
            'elapsed_ms' => $data['elapsed_ms'],
            'total_chars' => $length,
            'correct_chars' => $correct,
            'errors' => $errors,
            'wpm' => $netWpm,
            'raw_wpm' => $rawWpm,
            'accuracy' => $accuracy,
            'correct_words' => $correctWords,
            'consistency' => 0,
            'is_competitive' => $competitive,
        ]);

        return response()->json([
            'saved' => true,
            'id' => $result->id,
            'wpm' => $netWpm,
            'accuracy' => $accuracy,
            'errors' => $errors,
        ]);
    }

    public function leaderboard(Request $request)
    {
        $query = TypingResult::with('user')->where('is_competitive', true);

        foreach (['mode', 'difficulty', 'language', 'input_method', 'target'] as $key) {
            if ($request->filled($key)) {
                $query->where($key, $request->input($key));
            }
        }

        $metric = in_array($request->input('sort'), ['wpm', 'accuracy', 'correct_words'])
            ? $request->input('sort')
            : 'wpm';

        $results = $query->orderByDesc($metric)
            ->limit(100)
            ->get()
            ->unique('user_id')
            ->take(30);

        return view('leaderboard', compact('results'));
    }

    public function dashboard(Request $request)
    {
        $results = $request->user()->results()->latest()->limit(30)->get();
        $best = $request->user()->results()->max('wpm') ?? 0;
        $avg = round($request->user()->results()->avg('wpm') ?? 0, 1);
        $goal = DB::table('typing_goals')
            ->where('user_id', $request->user()->id)
            ->value('target_wpm') ?? 60;

        return view('dashboard', compact('results', 'best', 'avg', 'goal'));
    }

    public function goal(Request $request)
    {
        $data = $request->validate([
            'target_wpm' => 'required|integer|min:10|max:250',
        ]);

        DB::table('typing_goals')->updateOrInsert(
            ['user_id' => $request->user()->id],
            [
                'target_wpm' => $data['target_wpm'],
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );

        return back()->with('ok', 'Target disimpan.');
    }

    public function report(Request $request)
    {
        $data = $request->validate([
            'subject' => 'required|string|max:150',
            'message' => 'required|string|min:10|max:2000',
        ]);

        DB::table('problem_reports')->insert($data + [
            'user_id' => $request->user()?->id,
            'status' => 'open',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return back()->with('ok', 'Laporan terkirim.');
    }
}
