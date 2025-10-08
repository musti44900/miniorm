<?php
require_once __DIR__ . '/vendor/autoload.php';


use ORM\Database;
use ORM\Models\User;
use ORM\Models\Post;

// ---- 1. Veritabanı bağlantısı ----
Database::connect('mysql:host=mini-orm-db;dbname=miniorm;charset=utf8', 'root', 'root');

// ---- 2. Yeni kullanıcı oluştur ----
$user = User::create([
    'name' => 'Ahmet',
    'email' => 'ahmet@example.com',
    'status' => 'active'
]);
echo "✅ Yeni kullanıcı oluşturuldu: ID = {$user->id}, Name = {$user->name}\n";

// ---- 3. Yeni post oluştur ----
$post = Post::create([
    'title'   => 'İlk Gönderim',
    'body'    => 'Bu benim ORM sistemiyle oluşturduğum ilk gönderi.',
    'user_id' => $user->id,
]);
echo "✅ Yeni post oluşturuldu: ID = {$post->id}, Title = {$post->title}\n\n";

echo "find\n";
// ---- 4. Post’u doğrudan ID ile bul ----
$fetchedPost = Post::find(1); // Burada 4 ID'li postu çekiyoruz
if ($fetchedPost) {
    echo "📌 Post başlığı: {$fetchedPost->title}\n";

    // ---- 5. Post’a ait kullanıcıyı çek (belongsTo ilişkisi) ----
    $userOfPost = $fetchedPost->user();
    if ($userOfPost) {
        echo "🧑 Post sahibi: {$userOfPost->name}\n";
    } else {
        echo "⚠️ Post sahibi bulunamadı.\n";
    }
} else {
    echo "⚠️ ID = 1 olan post bulunamadı.\n";
}

// ---- 6. Eager loading örneği ----
    echo("\nEAGER LOADING \n");
$posts = Post::with('user')->get();

foreach ($posts as $p) {
    echo "📜 {$p->title} — Yazar: {$p->user?->name}\n";
}

// ---- 7. Yardımcı metotlar ----
echo "JSON çıktısı (ilk post): " . $fetchedPost->toJson() . "\n";
echo("\n toArray \n");
print_r($fetchedPost->toArray());


// ---- 8. Kısa sorgular ----
echo "(COUNT )Toplam post sayısı: " . Post::query()->count() . "\n";
$firstPost = Post::query()->first();
echo " (first) İlk post başlığı: " . ($firstPost ? $firstPost->title : 'Bulunamadı') . "\n";
$exists = Post::query()->where('id', '=', 1)->exists();
echo "(where )ID = 1 olan post var mı? " . ($exists ? 'Evet' : 'Hayır') . "\n";
