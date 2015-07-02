<?php
/**
 * lite
 *
 * An open source application development framework for PHP 5.4.16 or newer
 *
 * @package		lite	
 * @author		Linhaoye
 * @copyright	Copyright (c) 2015 - ?, eLab, Inc.
 * @license		MIT license
 * @link		no link
 * @since		Version 1.0
 * @filesource
 */

namespace lite;

// ------------------------------------------------------------------------

/**
 * DbAction Class
 *
 * The sql builder for database
 *
 * @package		lite	
 * @author		Linhaoye
 */
class BbAction{

	/*
	 * @the errors array
	 */
	public $errors = array();

	/*
	 * @The sql query
	 */
	private $sql;

	/**
	 * @The name=>value pairs
	 */
	private $values = array();


	/**
	 * @The db pdo
	 */
	private $db = null;

// ------------------------------------------------------------------------

	/**
	 *
	 * @add a value to the values array
	 *
	 * @access public
	 *
	 * @param string $key the array key
	 *
	 * @param string $value The value
	 *
	 */
	public function addValue($key, $value)
	{
		$this->values[$key] = $value;
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * @set the values
	 *
	 * @access public
	 *
	 * @param array
	 *
	 */
	public function setValues($array)
	{
		$this->values = $array;
	}

// ------------------------------------------------------------------------

	/**
	 * @set the db pdo
	 *
	 * @access public
	 *
	 * @param \Db $db 
	 *
	 */
	public function setDb(\Db $db)
	{
		$this->db = $db;
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * @delete a recored from a table
	 *
	 * @access public
	 *
	 * @param string $table The table name
	 *
	 * @param int ID
	 *
	 */
	public function delete($table, $id)
	{
		try
		{
			// get the primary key name
			$pk   = $this->getPrimaryKey($table);
			$sql  = "DELETE FROM $table WHERE $pk=:$pk";
			$stmt = $this->db->conn->prepare($sql);

			$stmt->bindParam(":$pk", $id);
			$stmt->execute();
		}
		catch(\Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * @insert a record into a table
	 *
	 * @access public
	 *
	 * @param string $table The table name
	 *
	 * @param array $values An array of fieldnames and values
	 *
	 * @return int The last insert ID
	 *
	 */
	public function insert($table, $values=null)
	{
		$values = is_null($values) ? $this->values : $values;
		$sql = "INSERT INTO $table SET ";

		$obj = new \CachingIterator(new \ArrayIterator($values));

		try
		{
			foreach( $obj as $field=>$val)
			{
				$sql .= "$field = :$field";
				$sql .=  $obj->hasNext() ? ',' : '';
				$sql .= "\n";
			}
			$stmt = $this->db->conn->prepare($sql);

			// bind the params
			foreach($values as $k=>$v)
			{
				$stmt->bindParam(':'.$k, $v);
			}
			$stmt->execute($values);
			// return the last insert id
			return $this->db->lastInsertId();
		}
		catch(\Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}

// ------------------------------------------------------------------------

	/**
	 * @update a table
	 *
	 * @access public
	 * 
	 * @param string $table The table name
	 *
	 * @param int $id
	 *
	 */
	public function update($table, $id, $values=null)
	{
		$values = is_null($values) ? $this->values : $values;
		try
		{
			// get the primary key/
			$pk = $this->getPrimaryKey($table);
	
			// set the primary key in the values array
			$values[$pk] = $id;

			$obj = new \CachingIterator(new \ArrayIterator($values));

			$sql = "UPDATE $table SET \n";
			foreach( $obj as $field=>$val)
			{
				$sql .= "$field = :$field";
				$sql .= $obj->hasNext() ? ',' : '';
				$sql .= "\n";
			}
			$sql .= " WHERE $pk=$id";
			$stmt = $this->db->conn->prepare($sql);

			// bind the params
			foreach($values as $k=>$v)
			{
				$stmt->bindParam(':'.$k, $v);
			}
			// bind the primary key and the id
			$stmt->bindParam($pk, $id);
			$stmt->execute($values);

			// return the affected rows
			return $stmt->rowCount();
		}
		catch(\Exception $e)
		{
			$this->errors[] = $e->getMessage();
		}
	}

// ------------------------------------------------------------------------

	/**
	 * @get the name of the field that is the primary key
	 *
	 * @access private
	 *
	 * @param string $table The name of the table
	 *
	 * @return string
	 *
	 */
	private function getPrimaryKey($table)
	{
		$pk = '';

		foreach ($this->query("SHOW COLUMNS FROM $table") as $rows)
		{
			if ($rows['Key'] == 'PRI' && $rows['Extra'] == 'auto_increment')	
			{
				$pk = $rows['Key'];
				break;
			}
		}

		return $pk;
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * Fetch all records from table
	 *
	 * @access public
	 *
	 * @param $table The table name
	 *
	 * @return array
	 *
	 */
	public function query()
	{
		$res = $this->db->conn->query( $this->sql );
		return $res;
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * @select statement
	 *
	 * @access public
	 *
	 * @param string $table
	 *
	 */
	public function select($table)
	{
		$this->sql = "SELECT * FROM $table";
	}

// ------------------------------------------------------------------------

	/**
	 * @where clause
	 *
	 * @access public
	 *
	 * @param string $field
	 *
	 * @param string $value
	 *
	 */
	public function where($field, $value)
	{
		$this->sql .= " WHERE $field=$value";
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * @set limit
	 *
	 * @access public
	 *
	 * @param int $offset
	 *
	 * @param int $limit
	 *
	 * @return string
	 *
	 */
	public function limit($offset, $limit)
	{
		$this->sql .= " LIMIT $offset, $limit";
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * @add an AND clause
	 *
	 * @access public
	 *
	 * @param string $field
	 *
	 * @param string $value
	 *
	 */
	public function andClause($field, $value)
	{
		$this->sql .= " AND $field=$value";
	}

// ------------------------------------------------------------------------

	/**
	 *
	 * Add and order by
	 *
	 * @param string $fieldname
	 *
	 * @param string $order
	 *
	 */
	public function orderBy($fieldname, $order='ASC')
	{
		$this->sql .= " ORDER BY $fieldname $order";
	}
}
// end of class

$dbact = new DbAction();
$dbact->setDb(new Db([]));
$dbact->select("users");
$dbact->where("name", "kate");
$dbact->andClause("age", 3);
$dbact->andClause("sex", 2);
$dbact->orderBy("id");
$dbact->query();

?>
