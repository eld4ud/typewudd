<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class TypingContent extends Model {protected $fillable=['title','body','language','difficulty','kind','is_active']; protected function casts():array{return ['is_active'=>'boolean'];}}
