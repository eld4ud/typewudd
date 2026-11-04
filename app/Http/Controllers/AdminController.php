<?php

namespace App\Http\Controllers;

use App\Models\TypingContent;
use App\Models\TypingResult;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController
{
    private function guard(Request $request): void
    {
        abort_unless($request->user()?->role === 'admin', 403);
    }

    public function index(Request $request)
    {
        $this->guard($request);

        $users = User::latest()->limit(30)->get();
        $contents = TypingContent::latest()->get();
        $reports = DB::table('problem_reports')->latest()->limit(30)->get();
        $counts = [
            'users' => User::count(),
            'tests' => TypingResult::count(),
            'rooms' => DB::table('duel_rooms')->count(),
        ];

        return view('admin', compact('users', 'contents', 'reports', 'counts'));
    }

    public function content(Request $request)
    {
        $this->guard($request);

        $data = $request->validate([
            'title' => 'required|max:150',
            'body' => 'required|min:20|max:5000',
            'language' => 'required|in:id,en',
            'difficulty' => 'required|in:easy,normal,hard,expert',
        ]);

        TypingContent::create($data + [
            'kind' => 'words',
            'is_active' => true,
        ]);

        return back()->with('ok', 'Konten ditambahkan.');
    }

    public function toggle(Request $request, TypingContent $content)
    {
        $this->guard($request);
        $content->update(['is_active' => !$content->is_active]);

        return back()->with('ok', 'Status konten diperbarui.');
    }

    public function user(Request $request, User $user)
    {
        $this->guard($request);

        abort_if(
            $user->id === $request->user()->id,
            422,
            'Tidak bisa menonaktifkan akun sendiri.'
        );

        $user->update(['is_active' => !$user->is_active]);

        return back()->with('ok', 'Status pengguna diperbarui.');
    }
}
