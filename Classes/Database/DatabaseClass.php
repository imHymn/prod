<?php


class DatabaseClass {
    private $connection = null;

    public function __construct() {
        try {
            $dsn = "mysql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'] . ";charset=utf8mb4";
            $this->connection = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASS']);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            die("Database connection failed: " . $e->getMessage());
        }
    }

    public function Select($sql, $params = []) {
        $stmt = $this->executeStatement($sql, $params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
public function Update($sql, $params = []) {
        $stmt = $this->executeStatement($sql, $params);
        return $stmt->rowCount();  // Number of affected rows
    }

 public function Insert($sql, $params = []) {
    $stmt = $this->executeStatement($sql, $params);
    
    // Check how many rows were affected (this would usually be 1 for a successful insert)
    if ($stmt->rowCount() > 0) {
        // If you need to retrieve the last inserted ID
        $lastInsertId = $this->connection->lastInsertId();
        return $lastInsertId;  // Optionally return the last inserted ID
    }
    return false;  // If no rows were inserted
}

    private function executeStatement($sql, $params = []) {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }
    public function beginTransaction() {
    return $this->connection->beginTransaction();
}
public function SelectOne($sql, $params = []) {
    $stmt = $this->executeStatement($sql, $params);
    return $stmt->fetch(PDO::FETCH_ASSOC); // Fetch one row as an associative array
}

public function commit() {
    return $this->connection->commit();
}

public function rollBack() {
    if ($this->connection->inTransaction()) {
        return $this->connection->rollBack();
    }
    // Optional: Log or silently ignore
    return false;
}
public function DuplicateAndModify($selectSql, $selectParams, callable $modifyCallback, $insertSql) {
    // Step 1: Select existing rows
    $rows = $this->Select($selectSql, $selectParams);
    if (empty($rows)) {
        return 0; // nothing to duplicate
    }
    
    $insertCount = 0;
    
    // Step 2: For each row, modify and insert
    foreach ($rows as $row) {
        $modifiedRow = $modifyCallback($row);

        // Prepare parameters for insert (keys starting with ':' for binding)
        $insertParams = [];
        // Assuming your insertSql uses named placeholders like :material_no, etc.
        foreach ($modifiedRow as $key => $value) {
            $insertParams[":$key"] = $value;
        }
        
        $this->Insert($insertSql, $insertParams);
        $insertCount++;
    }
    
    return $insertCount;
}



}
?>

