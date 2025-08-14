<?php

namespace CNA\Models;

use CNA\Config\Database;
use CNA\Utils\Security;

abstract class BaseModel
{
    protected Database $db;
    protected string $table = '';
    protected string $primaryKey = 'id';
    protected array $fillable = [];
    protected array $hidden = [];
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    public function find(string $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $result = $this->db->fetch($sql, ['id' => $id]);
        
        return $result ? $this->hideFields($result) : null;
    }

    public function findBy(string $field, $value): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$field} = :value";
        $result = $this->db->fetch($sql, ['value' => $value]);
        
        return $result ? $this->hideFields($result) : null;
    }

    public function all(int $limit = 0, int $offset = 0): array
    {
        $sql = "SELECT * FROM {$this->table} ORDER BY created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT {$limit} OFFSET {$offset}";
        }
        
        $results = $this->db->fetchAll($sql);
        
        return array_map([$this, 'hideFields'], $results);
    }

    public function create(array $data): string
    {
        $filteredData = $this->filterFillable($data);
        $filteredData['id'] = Security::generateUUID();
        
        return $this->db->insert($this->table, $filteredData);
    }

    public function update(string $id, array $data): bool
    {
        $filteredData = $this->filterFillable($data);
        
        $updated = $this->db->update($this->table, $filteredData, [$this->primaryKey => $id]);
        return $updated > 0;
    }

    public function delete(string $id): bool
    {
        $deleted = $this->db->delete($this->table, [$this->primaryKey => $id]);
        return $deleted > 0;
    }

    public function count(): int
    {
        $result = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}");
        return (int) $result['count'];
    }

    public function search(string $query, array $fields = []): array
    {
        if (empty($fields)) {
            return [];
        }
        
        $whereConditions = [];
        $params = [];
        
        foreach ($fields as $field) {
            $whereConditions[] = "{$field} ILIKE :query";
        }
        
        $sql = "SELECT * FROM {$this->table} WHERE " . implode(' OR ', $whereConditions) . " ORDER BY created_at DESC";
        $params['query'] = "%{$query}%";
        
        $results = $this->db->fetchAll($sql, $params);
        
        return array_map([$this, 'hideFields'], $results);
    }

    public function paginate(int $page = 1, int $perPage = 15): array
    {
        $offset = ($page - 1) * $perPage;
        
        // Get total count
        $totalResult = $this->db->fetch("SELECT COUNT(*) as count FROM {$this->table}");
        $total = (int) $totalResult['count'];
        
        // Get items
        $items = $this->all($perPage, $offset);
        
        return [
            'data' => $items,
            'total' => $total,
            'per_page' => $perPage,
            'current_page' => $page,
            'last_page' => (int) ceil($total / $perPage),
            'from' => $offset + 1,
            'to' => min($offset + $perPage, $total),
        ];
    }

    protected function filterFillable(array $data): array
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }

    protected function hideFields(array $data): array
    {
        if (empty($this->hidden)) {
            return $data;
        }
        
        return array_diff_key($data, array_flip($this->hidden));
    }

    public function exists(string $field, $value, string $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$this->table} WHERE {$field} = :value";
        $params = ['value' => $value];
        
        if ($excludeId) {
            $sql .= " AND {$this->primaryKey} != :exclude_id";
            $params['exclude_id'] = $excludeId;
        }
        
        $result = $this->db->fetch($sql, $params);
        return (int) $result['count'] > 0;
    }

    public function beginTransaction(): bool
    {
        return $this->db->beginTransaction();
    }

    public function commit(): bool
    {
        return $this->db->commit();
    }

    public function rollback(): bool
    {
        return $this->db->rollback();
    }
}