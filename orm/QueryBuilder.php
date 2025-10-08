<?php
namespace ORM;

class QueryBuilder
{
    protected string $table;
    protected string $modelClass;
    protected array $wheres = [];
    protected array $orders = [];
    protected ?int $limit = null;
    protected array $withRelations = [];

    public function __construct(string $table, string $modelClass = null)
    {
        $this->table = $table;
        $this->modelClass = $modelClass;
    }

    public function where(string $column, string $operator, $value): self
    {
        $this->wheres[] = [$column, $operator, $value];
        return $this;
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $this->orders[] = [$column, $direction];
        return $this;
    }

    public function limit(int $limit): self
    {
        $this->limit = $limit;
        return $this;
    }

    public function with(string ...$relations): self
    {
       $this->withRelations = array_merge($this->withRelations, $relations);
        return $this;
    }

    public function get(): array
    {
        $pdo = Database::getConnection();
        $sql = "SELECT * FROM {$this->table}";

        if (!empty($this->wheres)) {
            $clauses = [];
            foreach ($this->wheres as $i => [$col, $op, $val]) {
                $clauses[] = "$col $op :where$i";
            }
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }

        if (!empty($this->orders)) {
            $orders = array_map(fn($o) => "{$o[0]} {$o[1]}", $this->orders);
            $sql .= " ORDER BY " . implode(', ', $orders);
        }

        if ($this->limit) {
            $sql .= " LIMIT {$this->limit}";
        }

        $stmt = $pdo->prepare($sql);

        if (!empty($this->wheres)) {
            foreach ($this->wheres as $i => [$col, $op, $val]) {
                $stmt->bindValue(":where$i", $val);
            }
        }

        $stmt->execute();
        $rows = $stmt->fetchAll(\PDO::FETCH_ASSOC);

        $results = [];
        foreach ($rows as $row) {
            $model = new $this->modelClass($row);
            $model->fill($row);
            // Eager loading
            foreach ($this->withRelations as $relation) {
                if (method_exists($model, $relation)) {
                    $relatedModel = $model->$relation();
                    if ($relatedModel) {
                        // PHP'nin __get/__set bypass etmemesi için attributes içinde saklayalım
                        $model->setRelation($relation, $relatedModel);
                    }
                }
            }

            $results[] = $model;
        }

        return $results;
    }

    public function first(): ?object
    {
        $this->limit(1);
        $results = $this->get();
        return $results[0] ?? null;
    }

    public function exists(): bool
    {
        return $this->first() !== null;
    }

    public function count(): int
    {
        $pdo = Database::getConnection();
        $sql = "SELECT COUNT(*) as cnt FROM {$this->table}";
        if (!empty($this->wheres)) {
            $clauses = [];
            foreach ($this->wheres as $i => [$col, $op, $val]) {
                $clauses[] = "$col $op :where$i";
            }
            $sql .= " WHERE " . implode(" AND ", $clauses);
        }
        $stmt = $pdo->prepare($sql);
        if (!empty($this->wheres)) {
            foreach ($this->wheres as $i => [$col, $op, $val]) {
                $stmt->bindValue(":where$i", $val);
            }
        }
        $stmt->execute();
        $res = $stmt->fetch(\PDO::FETCH_ASSOC);
        return (int) $res['cnt'];
    }
}
