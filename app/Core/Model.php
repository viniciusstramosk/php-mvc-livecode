<?php

namespace App\Core;

class Model
{
    protected $db;
    protected $table;
    protected $primaryKey = 'id';
    protected $fillable = [];
    
    public function __construct()
    {
        $this->db = Application::getInstance()->getDatabase();
    }
    
    public function all()
    {
        return $this->db->select("SELECT * FROM {$this->table}");
    }
    
    public function find($id)
    {
        return $this->db->selectOne(
            "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    public function where($column, $operator, $value = null)
    {
        if ($value === null) {
            $value = $operator;
            $operator = '=';
        }
        
        return $this->db->select(
            "SELECT * FROM {$this->table} WHERE {$column} {$operator} :value",
            ['value' => $value]
        );
    }
    
    public function create($data)
    {
        $data = $this->filterFillable($data);
        return $this->db->insert($this->table, $data);
    }
    
    public function update($id, $data)
    {
        $data = $this->filterFillable($data);
        return $this->db->update(
            $this->table,
            $data,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    public function delete($id)
    {
        return $this->db->delete(
            $this->table,
            "{$this->primaryKey} = :id",
            ['id' => $id]
        );
    }
    
    private function filterFillable($data)
    {
        if (empty($this->fillable)) {
            return $data;
        }
        
        return array_intersect_key($data, array_flip($this->fillable));
    }
}
