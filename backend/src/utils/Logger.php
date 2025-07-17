<?php
namespace App\Utils;

use PDO;

class Logger
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function log($actionBy, $actionType, $description)
{
    $stmt = $this->db->prepare("INSERT INTO system_logs (action_by, action_type, description, created_at, reviewed, status)
                                VALUES (:action_by, :action_type, :description, NOW(), 0, 'Pending')");
    $stmt->execute([
        ':action_by' => $actionBy,
        ':action_type' => $actionType,
        ':description' => $description,
    ]);
}
}
