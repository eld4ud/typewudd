<?php
namespace App\Http\Controllers;
use App\Models\User;
use App\Models\TypingContent;
use App\Models\TypingResult;
use Illuminate\Http\Request;
class AdminController {
 private function guard(Request $r){abort_unless($r->user()?->role==='admin',403);}
 public function index(Request $r){$this->guard($r);$users=User::latest()->limit(30)->get();$contents=TypingContent::latest()->get();$reports=\DB::table('problem_reports')->latest()->limit(30)->get();$counts=['users'=>User::count(),'tests'=>TypingResult::count(),'rooms'=>\DB::table('duel_rooms')->count()];return view('admin',compact('users','contents','reports','counts'));}
 public function content(Request $r){$this->guard($r);$d=$r->validate(['title'=>'required|max:150','body'=>'required|min:20|max:5000','language'=>'required|in:id,en','difficulty'=>'required|in:easy,normal,hard,expert']);TypingContent::create($d+['kind'=>'words','is_active'=>true]);return back()->with('ok','Konten ditambahkan.');}
 public function toggle(Request $r,TypingContent $content){$this->guard($r);$content->update(['is_active'=>!$content->is_active]);return back()->with('ok','Status konten diperbarui.');}
 public function user(Request $r,User $user){$this->guard($r);abort_if($user->id===$r->user()->id,422,'Tidak bisa menonaktifkan akun sendiri.');$user->update(['is_active'=>!$user->is_active]);return back()->with('ok','Status pengguna diperbarui.');}
}
