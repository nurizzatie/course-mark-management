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

    $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, matric_number) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([
        $data['name'],
        $data['email'],
        password_hash($data['password'], PASSWORD_DEFAULT),
        $data['role'],
        $data['matric_number'] ?? null
    ]);

    // ✅ Log the user creation
    $this->logAction('Admin', 'Create User', "Created user with email: {$data['email']}");

    $response->getBody()->write(json_encode(['message' => 'User created']));
    return $response->withHeader('Content-Type', 'application/json');
}

    // ✅ Delete user
    public function deleteUser(Request $request, Response $response, $args): Response {
        $id = $args['id'];

        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);

        $this->logAction('Admin', 'Delete User', "Deleted user with ID: $id");

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

        $stmt = $this->db->prepare("UPDATE courses SET lecturer_id = :lecturer_id WHERE id = :course_id");
        $stmt->execute([
            ':lecturer_id' => $data['lecturer_id'],
            ':course_id' => $data['course_id']
        ]);

        $response->getBody()->write(json_encode(['message' => 'Lecturer assigned']));
        return $response->withHeader('Content-Type', 'application/json');
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
        $courseId = $data['course_id'] ?? null;
        $lecturerId = $data['lecturer_id'] ?? null;

        if (!$courseId || !$lecturerId) {
            $response->getBody()->write(json_encode(['error' => 'Missing course_id or lecturer_id']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("REPLACE INTO course_assignments (course_id, lecturer_id) VALUES (:course_id, :lecturer_id)");
        $stmt->execute(['course_id' => $courseId, 'lecturer_id' => $lecturerId]);

        // ✅ Log the assignment action
        $this->logAction('Admin', 'Assign Lecturer', "Assigned lecturer_id $lecturerId to course_id $courseId");

        $response->getBody()->write(json_encode(['message' => 'Lecturer assigned to course']));
        return $response->withHeader('Content-Type', 'application/json')->withStatus(200);
    }

    // ✅ Dummy logs for activity
public function getLogs(Request $request, Response $response): Response {
    $logs = [
        [
            'id' => 1,
            'action' => 'Login',
            'user_name' => 'Admin',
            'details' => 'User logged in',
            'timestamp' => '2025-07-06 20:40'
        ],
        [
            'id' => 2,
            'action' => 'Assigned lecturer',
            'user_name' => 'Admin',
            'details' => 'Assigned to BIT2043',
            'timestamp' => '2025-07-06 20:45'
        ]
    ];

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

        $this->logAction('Admin', 'Update Role', "Updated role for user ID: $id to $newRole");

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
    $this->logAction('Admin', 'Reset Password', "Reset password for user: $email");

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


}
