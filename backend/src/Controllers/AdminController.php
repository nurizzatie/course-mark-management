<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class AdminController {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    // ✅ Get all users
    public function getUsers(Request $request, Response $response): Response {
        $stmt = $this->db->query("SELECT id, name, email, role FROM users");
        $users = $stmt->fetchAll();
        $response->getBody()->write(json_encode($users));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // ✅ Create new user
    public function createUser(Request $request, Response $response): Response {
    $data = $request->getParsedBody();

    try {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, matric_number) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([
            $data['name'],
            $data['email'],
            password_hash($data['password'], PASSWORD_DEFAULT),
            $data['role'],
            $data['matric_number'] ?? null
        ]);

        // ✅ No echo/print here
        $response->getBody()->write(json_encode(['message' => 'User created']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(201);

    } catch (\PDOException $e) {
        $error = $e->getCode() == '23000' ? 'Matric number or email already exists' : 'Database error';
        $response->getBody()->write(json_encode(['error' => $error]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}

    // ✅ Delete user
    public function deleteUser(Request $request, Response $response, $args): Response {
    $userId = $args['id'];
    $adminId = 1; // Replace this with the logged-in admin's user ID

    // ✅ Log before deleting
    $this->logAction($adminId, 'Delete User', "Deleted user with ID: $userId");

    // ✅ Then delete
    $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$userId]);

    $response->getBody()->write(json_encode(['message' => 'User deleted']));
    return $response->withHeader('Content-Type', 'application/json');
}

    // ✅ Get all courses
    public function getAllCourses(Request $request, Response $response): Response {
        $stmt = $this->db->query("SELECT id, course_code, course_name, lecturer_id FROM courses");
        $courses = $stmt->fetchAll();
        $response->getBody()->write(json_encode($courses));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // ✅ Get all lecturers
    public function getAllLecturers(Request $request, Response $response): Response {
        $stmt = $this->db->query("SELECT id, name FROM users WHERE role = 'Lecturer'");
        $lecturers = $stmt->fetchAll();
        $response->getBody()->write(json_encode($lecturers));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // ✅ Assign lecturer directly to course (using lecturer_id in courses table)
    public function assignLecturer(Request $request, Response $response): Response {
    $data = $request->getParsedBody();

    $lecturerId = $data['lecturer_id'] ?? null;
    $courseId = $data['course_id'] ?? null;

    if (!$lecturerId || !$courseId) {
        $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
        return $response
            ->withHeader('Content-Type', 'application/json')
            ->withStatus(400); // <-- this is what's causing 400 in frontend
    }

    $stmt = $this->db->prepare("INSERT INTO course_lecturers (lecturer_id, course_id) VALUES (:lecturer_id, :course_id)");
    $stmt->bindParam(':lecturer_id', $lecturerId);
    $stmt->bindParam(':course_id', $courseId);

    try {
        $stmt->execute();
        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (PDOException $e) {
        $response->getBody()->write(json_encode(['error' => 'Database error: ' . $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}

// ✅ Assign lecturer using lecturer_course table (direct)
public function assignLecturerDirect(Request $request, Response $response): Response {
    $data = $request->getParsedBody();

    $lecturer_id = $data['lecturer_id'] ?? null;
    $course_id = $data['course_id'] ?? null;

    if (!$lecturer_id || !$course_id) {
        $response->getBody()->write(json_encode(['error' => 'Missing lecturer_id or course_id']));
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
    }

    try {
        $stmt = $this->db->prepare("INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (:lecturer_id, :course_id)");
        $stmt->bindParam(':lecturer_id', $lecturer_id);
        $stmt->bindParam(':course_id', $course_id);
        $stmt->execute();

        $response->getBody()->write(json_encode(['success' => true]));
        return $response->withHeader('Content-Type', 'application/json');
    } catch (\PDOException $e) {
        $response->getBody()->write(json_encode([
            'error' => 'Database error',
            'details' => $e->getMessage()
        ]));
        return $response->withStatus(500)->withHeader('Content-Type', 'application/json');
    }
}


    // ✅ Get both courses and lecturers for frontend use (dropdown/table)
    public function getCoursesAndLecturers(Request $request, Response $response): Response {
        $lecturers = $this->db->query("SELECT id AS lecturer_id, name FROM users WHERE role = 'Lecturer'")->fetchAll(PDO::FETCH_ASSOC);
        $courses = $this->db->query("SELECT id AS course_id, course_code, course_name FROM courses")->fetchAll(PDO::FETCH_ASSOC);

        $data = [
            'lecturers' => $lecturers,
            'courses' => $courses
        ];

        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // ✅ Assign lecturer to course using separate assignment table (course_assignments)
    public function assignLecturerToCourse(Request $request, Response $response): Response {
    $data = $request->getParsedBody();
    $lecturerId = $data['lecturer_id'] ?? null;
    $courseId = $data['course_id'] ?? null;

    if (!$lecturerId || !$courseId) {
        $response->getBody()->write(json_encode(['error' => 'Missing lecturer_id or course_id']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(400);
    }

    $stmt = $this->db->prepare("INSERT INTO lecturer_course (lecturer_id, course_id) VALUES (:lecturer_id, :course_id)");
    $stmt->bindParam(':lecturer_id', $lecturerId);
    $stmt->bindParam(':course_id', $courseId);

    try {
        $stmt->execute();
        $response->getBody()->write(json_encode(['message' => 'Lecturer assigned successfully']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    } catch (\PDOException $e) {
        $response->getBody()->write(json_encode(['error' => $e->getMessage()]));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(500);
    }
}


    // ✅ Dummy logs for activity
public function getLogs(Request $request, Response $response): Response {
    $stmt = $this->db->query("
        SELECT 
            l.id,
            u.name AS user_name,
            l.action_type AS action,
            l.description AS details,
            l.created_at AS timestamp
        FROM system_logs l
        LEFT JOIN users u ON u.id = l.action_by
        ORDER BY l.created_at DESC
    ");

    $logs = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response->getBody()->write(json_encode(['logs' => $logs]));
    return $response->withHeader('Content-Type', 'application/json');
}

    // ✅ Update user's role
    public function updateUserRole(Request $request, Response $response, array $args): Response {
        $id = $args['id'];
        $data = $request->getParsedBody();
        $newRole = $data['role'] ?? null;

        if (!$newRole) {
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                            ->write(json_encode(['error' => 'Missing role']));
        }

        $stmt = $this->db->prepare("UPDATE users SET role = :role WHERE id = :id");
        $stmt->execute([
            ':role' => $newRole,
            ':id' => $id
        ]);

        $adminId = 1; // ✅ Replace with real logged-in user ID later
$this->logAction($adminId, 'Update Role', "Updated role for user ID: $id to $newRole");

        $response->getBody()->write(json_encode(['message' => 'User role updated']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // ✅ Add new course
public function createCourse(Request $request, Response $response): Response {
    $data = $request->getParsedBody();

    $stmt = $this->db->prepare("INSERT INTO courses (course_code, course_name, semester, year) VALUES (:code, :name, :sem, :year)");
    $stmt->execute([
        ':code' => $data['course_code'],
        ':name' => $data['course_name'],
        ':sem' => $data['semester'],
        ':year' => $data['year']
    ]);

    $response->getBody()->write(json_encode(['message' => 'Course added']));
    return $response->withHeader('Content-Type', 'application/json');
}

// ✅ Reset user password
    public function resetPassword(Request $request, Response $response): Response {
    $data = $request->getParsedBody();

    $matric = $data['matric_number'] ?? null;
    $newPassword = $data['password'] ?? null;

    if (!$matric || !$newPassword) {
        return $response->withStatus(400)->withHeader('Content-Type', 'application/json')
                        ->write(json_encode(['error' => 'Missing matric number or password']));
    }

    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

    $stmt = $this->db->prepare("UPDATE users SET password = ? WHERE matric_number = ?");
    $stmt->execute([$hashedPassword, $matric]);

    // ✅ Log password reset
    $adminId = $this->getLoggedInUserId($request);
$this->logAction($adminId, 'Reset Password', "Reset password for user: $matric");

    $response->getBody()->write(json_encode(['message' => 'Password reset successful']));
    return $response->withHeader('Content-Type', 'application/json');
}

// ✅ Add logAction helper method here
private function logAction($actionBy, $actionType, $description) {
    $stmt = $this->db->prepare("INSERT INTO system_logs (action_by, action_type, description, created_at) VALUES (:by, :type, :desc, NOW())");
    $stmt->execute([
        ':by' => $actionBy,
        ':type' => $actionType,
        ':desc' => $description
    ]);
}
private function getLoggedInUserId(Request $request) {
    return $request->getAttribute('user_id');
}

}
