<?php
namespace App\Models;

use PDO;

class Assessment {
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getByCourseId($courseId, $type = null) {
        $query = "SELECT * FROM assessments WHERE course_id = ?";
        $params = [$courseId];

        if ($type) {
            $query .= " AND type = ?";
            $params[] = $type;
        }

        $stmt = $this->db->prepare($query);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
