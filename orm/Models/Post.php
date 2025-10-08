<?php
namespace ORM\Models;

use ORM\Model;

class Post extends Model
{
    protected string $table = 'posts';
    protected array $fillable = ['title', 'body', 'user_id'];

    // PHP 8.2+ için property’leri önceden tanımlanması laızm
    public ?int $id = null;
    public ?string $title = null;
    public ?string $body = null;
    public ?int $user_id = null;
    public ?string $created_at = null;

    // belongsT ilişksi
    public function user(): ?User
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
