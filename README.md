# Mini ORM Projesi

Bu proje, PHP ve PDO tabanlı basit bir ORM sistemini Docker ortamında çalıştırmak için hazırlanmıştır. `Users` ve `Posts` tabloları üzerinden temel CRUD ve ilişkileri test edebilirsiniz.

---

## 👂 Proje Yapısı

```
mini-orm/
├─ docker-compose.yml
├─ init.sql
├─ src/
│  ├─ orm/
│  │  ├─ Database.php
│  │  ├─ Model.php
│  │  ├─ QueryBuilder.php
│  │  └─ Models/
│  │     ├─ User.php
│  │     └─ Post.php
├─ vendor/  # composer ile yüklenen kütüphaneler
├─ example.php
├─ test.php
└─ README.md
```

---

## 🐳 Docker ile Çalıştırma

1. Projeyi çek veya klasöre gir:

```bash
cd mini-orm
```

2. Docker container’ları başlat:

```bash
docker-compose up -d --build
```

> Bu komut `app` (PHP) ve `db` (MySQL) container’larını ayağa kaldırır ve `init.sql` dosyasını çalıştırarak veritabanını hazırlar.

3. Container’ların çalıştığını kontrol et:

```bash
docker ps
```

---

## ⚡ PHP Scriptlerini Çalıştırma

### a) `example.php` Dosyası

ORM fonksiyonlarını test etmek için:

```bash
docker exec -it mini-orm-app php /var/www/html/example.php
```

---

## 🛠 Veritabanı Bilgileri

- Host: `db` (Docker network üzerinden)
- Database: `miniorm`
- User: `root`
- Password: `root`
- Port: `3306`

### Örnek Tablo Yapısı

**Users**

| id | name | email | status | created\_at |
| -- | ---- | ----- | ------ | ----------- |

**Posts**

| id | title | body | user\_id | created\_at |
| -- | ----- | ---- | -------- | ----------- |

---

## 🔹 Temel Kullanım

### Yeni kullanıcı oluşturma

```php
$user = User::create([
    'name' => 'Ahmet',
    'email' => 'ahmet@example.com',
    'status' => 'active'
]);
```

### Yeni post oluşturma

```php
$post = Post::create([
    'title' => 'İlk Gönderim',
    'body'  => 'Bu benim ORM sistemiyle oluşturduğum ilk gönderi.',
    'user_id' => $user->id
]);
```

### İlişkileri kullanma

```php
$userOfPost = $post->user(); // belongsTo ilişkisi
```

### Eager loading

```php
$posts = Post::with('user')->get();
foreach ($posts as $p) {
    $ownerName = $p->user ? $p->user->name : 'Bilinmiyor';
    echo "{$p->title} — Yazar: {$ownerName}\n";
}
```

### Sorgu metotları

- `count()`, `exists()`, `first()`, `toArray()`, `toJson()`

---

## 🦚 Temizlik / Reset

- Docker container’larını durdurup silmek:

```bash
docker-compose down -v
```

> `-v` parametresi ile veritabanı verisi de silinir. Sonra `docker-compose up -d --build` ile baştan temiz bir ortam oluşturabilirsin.

---

## ⚡️ İpuçları

- `user_id` gibi foreign key alanlarına **Model objesi değil ID değeri** atayın.
- Eager loading (`with`) ilişkileri doğru çalıştırmak için modelin `__get` ve `belongsTo` metodları güncel olmalı.
- QueryBuilder tek başına da çalıştırılabilir:

```php
$users = User::query()->where('status', '=', 'active')->get();
```

### 🔒 SQL Güvenliği

- Tüm kullanıcı verileri **PDO prepared statements** ile SQL’e bind ediliyor.
- `create()`, `update()`, `delete()` ve `where()` metotlarında doğrudan veri SQL’e gömülmüyor.
- Kolon ve tablo isimleri sabit olduğundan SQL injection riski minimum.
- Dinamik kolon veya tablo isimlerini kullanıcıdan alırken ekstra kontrol eklenmeli.

Bu sayede Mini ORM projende SQL injection riskini büyük ölçüde önlemiş olursun.

