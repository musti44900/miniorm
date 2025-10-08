<?php
namespace ORM\Models;

use ORM\Model;

class User extends Model
{
    protected string $table = 'users';
    protected array $fillable = ['name', 'email', 'status'];

    // PHP 8.2+ için property’leri önceden tanımlıyoruz
    public ?int $id = null;
    public ?string $name = null;
    public ?string $email = null;
    public ?string $status = null;
    public ?string $created_at = null;
}
