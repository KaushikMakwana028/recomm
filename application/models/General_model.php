<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * General Model
 * 
 * Reusable database operations model
 * All common database queries should use this model
 */
class General_model extends CI_Model 
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
    }

    /**
     * Insert data into table
     * 
     * @param string $table Table name
     * @param array $data Data to insert
     * @return int Insert ID
     */
    public function insert($table, $data)
    {
        $this->db->insert($table, $data);
        return $this->db->insert_id();
    }

    /**
     * Update data in table
     * 
     * @param string $table Table name
     * @param array $data Data to update
     * @param array $where Where conditions
     * @return bool
     */
    public function update($table, $data, $where)
    {
        $this->db->where($where);
        return $this->db->update($table, $data);
    }

    /**
     * Delete data from table
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @return bool
     */
    public function delete($table, $where)
    {
        $this->db->where($where);
        return $this->db->delete($table);
    }

    /**
     * Get single row
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @param string $select Select fields
     * @return object|null
     */
    public function getOne($table, $where = [], $select = '*')
    {
        $this->db->select($select);
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get($table, 1);
        return $query->row();
    }

    /**
     * Get all rows
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @param string $select Select fields
     * @param string $order_by Order by
     * @param int $limit Limit
     * @param int $offset Offset
     * @return array
     */
    public function getAll($table, $where = [], $select = '*', $order_by = '', $limit = 0, $offset = 0)
    {
        $this->db->select($select);
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        if (!empty($order_by)) {
            $this->db->order_by($order_by);
        }
        
        if ($limit > 0) {
            $this->db->limit($limit, $offset);
        }
        
        $query = $this->db->get($table);
        return $query->result();
    }

    /**
     * Get single row as array
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @return array|null
     */
    public function getRow($table, $where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get($table, 1);
        return $query->row_array();
    }

    /**
     * Get result as array
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @param string $select Select fields
     * @return array
     */
    public function getResult($table, $where = [], $select = '*')
    {
        $this->db->select($select);
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get($table);
        return $query->result_array();
    }

    /**
     * Count rows
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @return int
     */
    public function countRows($table, $where = [])
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        return $this->db->count_all_results($table);
    }

    /**
     * Check if record exists
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @return bool
     */
    public function exists($table, $where)
    {
        $this->db->where($where);
        $query = $this->db->get($table);
        return $query->num_rows() > 0;
    }

    /**
     * Get record by ID
     * 
     * @param string $table Table name
     * @param int $id Record ID
     * @return object|null
     */
    public function getById($table, $id)
    {
        return $this->getOne($table, ['id' => $id]);
    }

    /**
     * Execute custom query
     * 
     * @param string $sql SQL query
     * @param array $params Parameters for binding
     * @return object Query result
     */
    public function customQuery($sql, $params = [])
    {
        $query = $this->db->query($sql, $params);
        return $query;
    }

    /**
     * Paginate results
     * 
     * @param string $table Table name
     * @param int $limit Records per page
     * @param int $offset Offset
     * @param array $where Where conditions
     * @param string $order_by Order by
     * @return array
     */
    public function paginate($table, $limit, $offset, $where = [], $order_by = 'id DESC')
    {
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        if (!empty($order_by)) {
            $this->db->order_by($order_by);
        }
        
        $query = $this->db->get($table, $limit, $offset);
        return $query->result();
    }

    /**
     * Get records with join
     * 
     * @param string $table Main table
     * @param array $joins Join configuration
     * @param array $where Where conditions
     * @param string $select Select fields
     * @return array
     */
    public function getWithJoin($table, $joins = [], $where = [], $select = '*')
    {
        $this->db->select($select);
        $this->db->from($table);
        
        foreach ($joins as $join) {
            $this->db->join($join['table'], $join['condition'], $join['type'] ?? 'left');
        }
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        $query = $this->db->get();
        return $query->result();
    }

    /**
     * Bulk insert
     * 
     * @param string $table Table name
     * @param array $data Array of data
     * @return bool
     */
    public function bulkInsert($table, $data)
    {
        return $this->db->insert_batch($table, $data);
    }

    /**
     * Get last inserted ID
     * 
     * @return int
     */
    public function getLastInsertId()
    {
        return $this->db->insert_id();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        $this->db->trans_start();
    }

    /**
     * Commit transaction
     */
    public function commitTransaction()
    {
        $this->db->trans_complete();
        return $this->db->trans_status();
    }

    /**
     * Rollback transaction
     */
    public function rollbackTransaction()
    {
        $this->db->trans_rollback();
    }

    /**
     * Search in table
     * 
     * @param string $table Table name
     * @param array $fields Fields to search
     * @param string $keyword Search keyword
     * @param array $where Additional where conditions
     * @return array
     */
    public function search($table, $fields, $keyword, $where = [])
    {
        $this->db->group_start();
        foreach ($fields as $field) {
            $this->db->or_like($field, $keyword);
        }
        $this->db->group_end();
        
        if (!empty($where)) {
            $this->db->where($where);
        }
        
        $query = $this->db->get($table);
        return $query->result();
    }

    /**
     * Get sum of column
     * 
     * @param string $table Table name
     * @param string $column Column name
     * @param array $where Where conditions
     * @return float
     */
    public function getSum($table, $column, $where = [])
    {
        $this->db->select_sum($column);
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get($table);
        $result = $query->row();
        return $result->$column ?? 0;
    }

    /**
     * Get maximum value
     * 
     * @param string $table Table name
     * @param string $column Column name
     * @param array $where Where conditions
     * @return mixed
     */
    public function getMax($table, $column, $where = [])
    {
        $this->db->select_max($column);
        if (!empty($where)) {
            $this->db->where($where);
        }
        $query = $this->db->get($table);
        $result = $query->row();
        return $result->$column;
    }

    /**
     * Soft delete (update is_active = 0)
     * 
     * @param string $table Table name
     * @param array $where Where conditions
     * @return bool
     */
    public function softDelete($table, $where)
    {
        return $this->update($table, ['is_active' => 0], $where);
    }
}