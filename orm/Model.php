<?php
namespace ORM;

abstract class Model
{
    protected string $table;
    protected array $fillable = [];
    protected QueryBuilder $query;
    protected array $attributes = [];
    protected array $relations = [];

    public function __construct(array $data = [])
    {
        if (!isset($this->table)) {
            throw new \Exception("Table name must be defined.");
        }

        $this->query = new QueryBuilder($this->table, static::class);

        if ($data) {
            $this->fill($data);
        }
    }

    // fill() fonksiyonu


  public function fill(array $data): void
  {

      foreach ($this->fillable as $key) {
          if (array_key_exists($key, $data)) {
              $this->attributes[$key] = $data[$key];


              if (property_exists($this, $key)) {
                  $this->{$key} = $data[$key];
              }
          }
      }


      if (array_key_exists('id', $data)) {
          $this->attributes['id'] = (int)$data['id'];
          if (property_exists($this, 'id')) {
              $this->id = (int)$data['id'];
          }
      }

      if (array_key_exists('created_at', $data)) {
          $this->attributes['created_at'] = $data['created_at'];
          if (property_exists($this, 'created_at')) {
              $this->created_at = $data['created_at'];
          }
      }
  }

public function setRelation(string $key, $value): void
{
    $this->relations[$key] = $value;
}


  public function __get($key)
  {
// Önce ilişkileri kontrol et
    if (isset($this->relations[$key])) {
        return $this->relations[$key];
    }

    // Normal attribute veya property
    if (property_exists($this, $key) && isset($this->{$key})) {
        return $this->{$key};
    }

    return $this->attributes[$key] ?? null;
  }


  public function __set($key, $value)
  {
      if (in_array($key, $this->fillable) || in_array($key, ['id', 'created_at'])) {
          $this->attributes[$key] = $value;

          if (property_exists($this, $key)) {

              if ($key === 'id') {
                  $this->{$key} = (int)$value;
              } else {
                  $this->{$key} = $value;
              }
          }
      }
  }

public function getTable(): string
{
    return $this->table ?? static::$table ?? strtolower(static::class) . 's';
}
    // QueryBuilder döndür
    public static function query(): QueryBuilder
    {
        $instance = new static();
        return new QueryBuilder($instance->getTable(), static::class);
    }

    public static function where(string $column, string $operator, $value): QueryBuilder
    {
        return static::query()->where($column, $operator, $value);
    }

    // CRUD: create
   public static function create(array $data): self
   {
       // Eğer ilişki modeli gönderilm ,id gelsin
       foreach ($data as $key => $val) {
           if ($val instanceof Model) {
               $data[$key] = (int)$val->id; // cast
           }
       }

       $instance = new static();
       $pdo = Database::getConnection();
       $columns = implode(',', array_keys($data));
       $placeholders = ':' . implode(',:', array_keys($data));
       $stmt = $pdo->prepare("INSERT INTO {$instance->table} ($columns) VALUES ($placeholders)");
       foreach ($data as $key => $val) {
           $stmt->bindValue(":$key", $val);
       }
       $stmt->execute();

       $data['id'] = (int)$pdo->lastInsertId();
       return new static($data);
   }


    // CRUD: find
    public static function find(int $id): ?self
    {
        $result = static::query()->where('id', '=', $id)->limit(1)->get();
        return $result[0] ?? null;
    }

    // CRUD: update
    public static function update(int $id, array $data): int
    {
        foreach ($data as $key => $val) {
            if ($val instanceof Model) {
                if (!$val->id) throw new \Exception("Related model must be saved first.");
                $data[$key] = $val->id;
            }
        }

        $instance = new static();
        $pdo = Database::getConnection();
        $set = [];
        foreach ($data as $key => $val) {
            $set[] = "$key = :$key";
        }
        $setStr = implode(',', $set);
        $stmt = $pdo->prepare("UPDATE {$instance->table} SET $setStr WHERE id = :id");
        $stmt->bindValue(':id', $id);
        foreach ($data as $key => $val) {
            $stmt->bindValue(":$key", $val);
        }
        $stmt->execute();
        return $stmt->rowCount();
    }

    // CRUD: delete
    public static function delete(int $id): int
    {
        $instance = new static();
        $pdo = Database::getConnection();
        $stmt = $pdo->prepare("DELETE FROM {$instance->table} WHERE id = :id");
        $stmt->bindValue(':id', $id);
        $stmt->execute();
        return $stmt->rowCount();
    }

    // belongsTo ilişkisi
public function belongsTo(string $relatedClass, string $foreignKey = null, string $ownerKey = 'id'): ?Model
{
    $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($relatedClass))->getShortName()) . '_id';
    $related = new $relatedClass();
    $result = $related::where($ownerKey, '=', $this->$foreignKey)->first();

    // property olarak sakla ki eager loading çalışsın
    $relationName = strtolower((new \ReflectionClass($related))->getShortName());
    $this->{$relationName} = $result;

    return $result;
}


    // hasOne ilişkisi
    public function hasOne(string $relatedClass, string $foreignKey = null, string $localKey = 'id'): ?Model
    {
        $instance = new $relatedClass();
        $foreignKey = $foreignKey ?? strtolower((new \ReflectionClass($this))->getShortName()) . '_id';
        return $instance::where($foreignKey, '=', $this->$localKey)->first();
    }

    // Eager loading
    public static function with(string ...$relations): QueryBuilder
    {
        return static::query()->with(...$relations);
    }

    // Yardımcı metotlar
    public function toArray(): array
    {
        return $this->attributes;
    }

    public function toJson(): string
    {
        return json_encode($this->attributes);
    }
}
