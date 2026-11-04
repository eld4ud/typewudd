<?php
namespace App\Http\Controllers;
use App\Models\TypingContent;
use App\Models\TypingResult;
use Illuminate\Http\Request;
class TypingController {
 public function index(){return view('typing');}
 public function content(Request $r){$d=$r->validate(['difficulty'=>'required|in:easy,normal,hard,expert','language'=>'required|in:id,en']);$c=TypingContent::where($d)->where('is_active',true)->inRandomOrder()->first();return $c?response()->json($c):response()->json(['message'=>'Konten belum tersedia'],404);}
 public function save(Request $r){$d=$r->validate(['typing_content_id'=>'required|exists:typing_contents,id','mode'=>'required|in:time,words,practice','difficulty'=>'required|in:easy,normal,hard,expert','language'=>'required|in:id,en','target'=>'required|integer|min:1|max:120','elapsed_ms'=>'required|integer|min:1000|max:7200000','typed'=>'required|string|max:5000','input_method'=>'required|in:touch,physical,unknown']);$c=TypingContent::findOrFail($d['typing_content_id']);abort_unless($c->is_active && $c->difficulty===$d['difficulty'] && $c->language===$d['language'],422);
 $expected=$c->body;$typed=$d['typed'];$len=mb_strlen($typed);$correct=0;for($i=0;$i<$len;$i++)if(mb_substr($typed,$i,1)===mb_substr($expected,$i,1))$correct++;
 $errors=$len-$correct;$minutes=$d['elapsed_ms']/60000;$raw=round(($len/5)/$minutes,2);$net=round(($correct/5)/$minutes,2);$accuracy=$len?round($correct/$len*100,2):0;$words=count(array_filter(preg_split('/\s+/u',trim(mb_substr($expected,0,$correct)))));
 // Limit obviously implausible results; final scores are recalculated on server.
 abort_if($raw>300 || $len>mb_strlen($expected),422,'Hasil tidak valid.');
 $competitive=$d['mode']!=='practice' && $d['elapsed_ms']>=10000 && $accuracy>=50;
 $result=TypingResult::create(['user_id'=>$r->user()->id,'typing_content_id'=>$c->id,'mode'=>$d['mode'],'difficulty'=>$d['difficulty'],'language'=>$d['language'],'input_method'=>$d['input_method'],'target'=>$d['target'],'elapsed_ms'=>$d['elapsed_ms'],'total_chars'=>$len,'correct_chars'=>$correct,'errors'=>$errors,'wpm'=>$net,'raw_wpm'=>$raw,'accuracy'=>$accuracy,'correct_words'=>$words,'consistency'=>0,'is_competitive'=>$competitive]);return response()->json(['saved'=>true,'id'=>$result->id,'wpm'=>$net,'accuracy'=>$accuracy,'errors'=>$errors]);
 }
 public function leaderboard(Request $r){$q=TypingResult::with('user')->where('is_competitive',true);foreach(['mode','difficulty','language','input_method','target'] as $key)if($r->filled($key))$q->where($key,$r->input($key));$metric=in_array($r->input('sort'),['wpm','accuracy','correct_words'])?$r->input('sort'):'wpm';$results=$q->orderByDesc($metric)->limit(100)->get()->unique('user_id')->take(30);return view('leaderboard',compact('results'));}
 public function dashboard(Request $r){$results=$r->user()->results()->latest()->limit(30)->get();$best=$r->user()->results()->max('wpm')??0;$avg=round($r->user()->results()->avg('wpm')??0,1);$goal=\DB::table('typing_goals')->where('user_id',$r->user()->id)->value('target_wpm')??60;return view('dashboard',compact('results','best','avg','goal'));}
 public function goal(Request $r){$d=$r->validate(['target_wpm'=>'required|integer|min:10|max:250']);\DB::table('typing_goals')->updateOrInsert(['user_id'=>$r->user()->id],['target_wpm'=>$d['target_wpm'],'updated_at'=>now(),'created_at'=>now()]);return back()->with('ok','Target disimpan.');}
 public function report(Request $r){$d=$r->validate(['subject'=>'required|string|max:150','message'=>'required|string|min:10|max:2000']);\DB::table('problem_reports')->insert($d+['user_id'=>$r->user()?->id,'status'=>'open','created_at'=>now(),'updated_at'=>now()]);return back()->with('ok','Laporan terkirim.');}
}
