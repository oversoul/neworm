<?php
require_once 'Database.php';
require_once 'Processor.php';
require_once 'Builder.php';
require_once 'hasMany.php';
require_once 'belongsTo.php';
require_once 'hasOne.php';
require_once 'Query.php';
require_once 'Model.php';

Database::config('localhost', 'root', 'root', 'dbs');

class Posts extends Model {

	
	public $id;
	public $title;
	public $slug;
	public $user_id;

	public function user()
	{
		return $this->belongsTo(Users::class, 'user_id');
	}
}

class Users extends Model {

	public $id;
	public $username;
	public $email;
	protected $password;

	public function posts()
	{
		return $this->hasMany(Posts::class, 'user_id');
	}

	public function avatar()
	{
		return $this->hasOne(Media::class, 'user_id');
	}
}

class Media extends Model {

	public $id;
	public $link;
	public $user_id;
}



echo '<pre>';
print_r(
	(new Posts)->first()
);