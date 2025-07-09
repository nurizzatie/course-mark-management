<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class LecturerController
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    // Get all students in a course
    public function getCourseStudents(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];

        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.matric_number, u.email 
            FROM student_courses sc
            JOIN users u ON sc.student_id = u.id
            WHERE sc.course_id = ?
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->getBody()->write(json_encode(['students' => $students]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Add student to course
    public function addStudentToCourse(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];
        $data = json_decode($request->getBody()->getContents(), true);
        $matric = $data['matric_number'] ?? null;

        if (!$matric) {
            $response->getBody()->write(json_encode(['error' => 'Matric number is required']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        // Find student by matric number
        $stmt = $this->db->prepare("SELECT id FROM users WHERE matric_number = ? AND role = 'student'");
        $stmt->execute([$matric]);
        $student = $stmt->fetch();

        if (!$student) {
            $response->getBody()->write(json_encode(['error' => 'Student not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        $studentId = $student['id'];

        // Check duplicate
        $check = $this->db->prepare("SELECT * FROM student_courses WHERE student_id = ? AND course_id = ?");
        $check->execute([$studentId, $courseId]);
        if ($check->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'Student already enrolled']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $insert = $this->db->prepare("INSERT INTO student_courses (student_id, course_id) VALUES (?, ?)");
        $insert->execute([$studentId, $courseId]);

        $response->getBody()->write(json_encode(['message' => 'Student added']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Remove student from course
    public function removeStudentFromCourse(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];
        $studentId = $args['student_id'];

        $stmt = $this->db->prepare("DELETE FROM student_courses WHERE student_id = ? AND course_id = ?");
        $stmt->execute([$studentId, $courseId]);

        $response->getBody()->write(json_encode(['message' => 'Student removed successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get courses based on logged-in lecturer
    public function getMyCourses(Request $request, Response $response): Response
    {
        $userHeader = $request->getHeaderLine('X-User');
        if (!$userHeader) {
            $response->getBody()->write(json_encode(['error' => 'Missing X-User header']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $user = json_decode($userHeader);
        $lecturerId = $user->id ?? null;

        if (!$lecturerId) {
            $response->getBody()->write(json_encode(['error' => 'Invalid lecturer ID']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("
            SELECT c.id, c.course_code, c.course_name, c.semester, c.year
            FROM lecturer_courses lc
            JOIN courses c ON lc.course_id = c.id
            WHERE lc.lecturer_id = ?
        ");
        $stmt->execute([$lecturerId]);
        $courses = $stmt->fetchAll();

        $response->getBody()->write(json_encode(['courses' => $courses]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Create course
    public function createCourse(Request $request, Response $response): Response {
        $user = json_decode($request->getHeaderLine('X-User'), true);
        $lecturerId = $user['id'];

        $data = json_decode($request->getBody()->getContents(), true);

        if (!$data) {
            $response->getBody()->write(json_encode(['error' => 'Invalid JSON']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Insert into courses
        $stmt = $this->db->prepare("
            INSERT INTO courses (course_code, course_name, semester, year)
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $data['course_code'],
            $data['course_name'],
            $data['semester'],
            $data['year']
        ]);

        $courseId = $this->db->lastInsertId();

        // Assign lecturer to course
        $this->db->prepare("
            INSERT INTO lecturer_courses (lecturer_id, course_id) VALUES (?, ?)
        ")->execute([$lecturerId, $courseId]);

        $response->getBody()->write(json_encode(['message' => 'Course created']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Update course
    public function updateCourse(Request $request, Response $response, $args): Response {
        $user = json_decode($request->getHeaderLine('X-User'), true);
        $lecturerId = $user['id'];
        $courseId = $args['id'];

        // Check ownership via lecturer_courses
        $check = $this->db->prepare("SELECT * FROM lecturer_courses WHERE course_id = ? AND lecturer_id = ?");
        $check->execute([$courseId, $lecturerId]);

        if (!$check->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'Not authorized']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        $data = json_decode($request->getBody()->getContents(), true);

        $stmt = $this->db->prepare("
            UPDATE courses SET course_code = ?, course_name = ?, semester = ?, year = ? WHERE id = ?
        ");
        $stmt->execute([
            $data['course_code'],
            $data['course_name'],
            $data['semester'],
            $data['year'],
            $courseId
        ]);

        $response->getBody()->write(json_encode(['message' => 'Course updated']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Delete course
    public function deleteCourse(Request $request, Response $response, $args): Response {
        $userHeader = $request->getHeaderLine('X-User');
        $user = json_decode($userHeader, true);

        if (!$user || !isset($user['id'])) {
            $response->getBody()->write(json_encode(['error' => 'Unauthorized']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $lecturerId = $user['id'];
        $courseId = $args['id'];

        // Check ownership
        $check = $this->db->prepare("SELECT * FROM lecturer_courses WHERE course_id = ? AND lecturer_id = ?");
        $check->execute([$courseId, $lecturerId]);

        if (!$check->fetch()) {
            $response->getBody()->write(json_encode(['error' => 'Not authorized']));
            return $response->withStatus(403)->withHeader('Content-Type', 'application/json');
        }

        // Delete course (lecturer_courses uses ON DELETE CASCADE)
        $this->db->prepare("DELETE FROM courses WHERE id = ?")->execute([$courseId]);

        $response->getBody()->write(json_encode(['message' => 'Course deleted']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get selected courses
    public function getCourse(Request $request, Response $response, $args): Response
    {
        $stmt = $this->db->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$args['course_id']]);
        $course = $stmt->fetch();

        if (!$course) {
            $response->getBody()->write(json_encode(['error' => 'Course not found']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $response->getBody()->write(json_encode(['course' => $course]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get assessments
    public function getAssessments(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];

        $stmt = $this->db->prepare("SELECT * FROM assessments WHERE course_id = ? ORDER BY type, id");
        $stmt->execute([$courseId]);
        $assessments = $stmt->fetchAll();

        $response->getBody()->write(json_encode(['assessments' => $assessments]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Add assessments to course
    public function addAssessment(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];
        $body = json_decode($request->getBody()->getContents(), true);

        $title = $body['title'] ?? null;
        $type = $body['type'] ?? null;
        $maxMark = $body['max_mark'] ?? 100;
        $weight = $body['weight_percentage'] ?? 0;
        $createdBy = json_decode($request->getHeaderLine('X-User'))->id ?? null;

        if (!$title || !$type || !$createdBy) {
            $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("
            INSERT INTO assessments (course_id, title, type, max_mark, weight_percentage, created_by)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([$courseId, $title, $type, $maxMark, $weight, $createdBy]);

        $response->getBody()->write(json_encode(['message' => 'Assessment added successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Update assessment
    public function updateAssessment(Request $request, Response $response, $args): Response
    {
        $assessmentId = $args['id'];
        $body = json_decode($request->getBody()->getContents(), true);

        $title = $body['title'] ?? null;
        $type = $body['type'] ?? null;
        $maxMark = $body['max_mark'] ?? 100;
        $weight = $body['weight_percentage'] ?? 0;

        if (!$title || !$type) {
            $response->getBody()->write(json_encode(['error' => 'Missing required fields']));
            return $response->withStatus(401)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("
            UPDATE assessments
            SET title = ?, type = ?, max_mark = ?, weight_percentage = ?
            WHERE id = ?
        ");
        $stmt->execute([$title, $type, $maxMark, $weight, $assessmentId]);

        $response->getBody()->write(json_encode(['message' => 'Assessment updated successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get course marks
    public function getCourseMarks(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];

        // Get students
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.matric_number 
            FROM student_courses sc
            JOIN users u ON sc.student_id = u.id
            WHERE sc.course_id = ?
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll();

        // Get remarks
        $stmt = $this->db->prepare("
            SELECT student_id, remarks FROM student_courses
            WHERE course_id = ?
        ");
        $stmt->execute([$courseId]);
        $remarksData = $stmt->fetchAll(PDO::FETCH_KEY_PAIR); // student_id => remarks

        // Get assessments
        $stmt = $this->db->prepare("SELECT * FROM assessments WHERE course_id = ?");
        $stmt->execute([$courseId]);
        $assessments = $stmt->fetchAll();

        // Get marks
        $stmt = $this->db->prepare("SELECT * FROM student_assessments WHERE assessment_id IN (
            SELECT id FROM assessments WHERE course_id = ?
        )");
        $stmt->execute([$courseId]);
        $marks = $stmt->fetchAll();

        // Map marks for faster lookup
        $markMap = [];
        foreach ($marks as $m) {
            $markMap[$m['student_id']][$m['assessment_id']] = $m['obtained_mark'];
        }

        // Grade logic function
        function calculateGrade($total) {
            if ($total >= 90) return 'A+';
            if ($total >= 80) return 'A';
            if ($total >= 75) return 'A-';
            if ($total >= 70) return 'B+';
            if ($total >= 65) return 'B';
            if ($total >= 60) return 'B-';
            if ($total >= 55) return 'C+';
            if ($total >= 50) return 'C';
            if ($total >= 45) return 'C-';
            if ($total >= 40) return 'D+';
            if ($total >= 35) return 'D';
            if ($total >= 30) return 'D-';
            return 'E';
        }

        // Calculate total and grade
        $studentTotals = [];

        foreach ($students as $s) {
            $total = 0.0;

            foreach ($assessments as $a) {
                $mark = $markMap[$s['id']][$a['id']] ?? null;
                if ($mark !== null) {
                    $scorePercent = ($mark / $a['max_mark']) * $a['weight_percentage'];
                    $total += $scorePercent;
                }
            }

            $studentTotals[] = [
                'student_id' => $s['id'],
                'total_mark' => round($total, 2),
                'grade' => calculateGrade($total)
            ];
        }

        $response->getBody()->write(json_encode([
            'students' => $students,
            'remarks' => $remarksData,
            'assessments' => $assessments,
            'marks' => $marks,
            'totals' => $studentTotals
        ]));

        return $response->withHeader('Content-Type', 'application/json');
    }

    // Save marks of student
    public function saveCourseMarks(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];
        $data = json_decode($request->getBody()->getContents(), true);

        $marks = $data['marks'] ?? [];
        $remarksMap = $data['remarks'] ?? [];

        $insertStmt = $this->db->prepare("
            INSERT INTO student_assessments (student_id, assessment_id, obtained_mark)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE obtained_mark = VALUES(obtained_mark)
        ");

        $updateRemarkStmt = $this->db->prepare("
            UPDATE student_courses SET remarks = ? WHERE course_id = ? AND student_id = ?
        ");

        foreach ($remarksMap as $studentId => $remark) {
            $updateRemarkStmt->execute([$remark, $courseId, $studentId]);
        }

        // Get course name for message
        $courseStmt = $this->db->prepare("SELECT course_name FROM courses WHERE id = ?");
        $courseStmt->execute([$courseId]);
        $course = $courseStmt->fetch();
        $courseName = $course ? $course['course_name'] : '';


        foreach ($data['marks'] as $entry) {
            $studentId = $entry['student_id'];
            $assessmentId = $entry['assessment_id'];
            $newMark = $entry['obtained_mark'];

            // Check if mark already exists
            $stmt = $this->db->prepare("SELECT obtained_mark FROM student_assessments WHERE student_id = ? AND assessment_id = ?");
            $stmt->execute([$studentId, $assessmentId]);
            $existing = $stmt->fetchColumn();

            if ($existing !== false && floatval($existing) == floatval($newMark)) {
                continue; // unchanged
            }

            // Save new mark
            $stmt = $this->db->prepare("
                INSERT INTO student_assessments (student_id, assessment_id, obtained_mark)
                VALUES (?, ?, ?)
                ON DUPLICATE KEY UPDATE obtained_mark = VALUES(obtained_mark)
            ");
            $stmt->execute([$studentId, $assessmentId, $newMark]);

            // Get assessment title
            $stmt = $this->db->prepare("SELECT title FROM assessments WHERE id = ?");
            $stmt->execute([$assessmentId]);
            $assessment = $stmt->fetch();

            if ($assessment) {
                $message = "Your mark for '{$assessment['title']}' in {$courseName} has been updated.";

                $stmt = $this->db->prepare("
                    INSERT INTO notifications (user_id, message, seen, course_id)
                    VALUES (?, ?, 0, ?)
                ");
                $stmt->execute([$studentId, $message, $courseId]);
            }
        }

        $response->getBody()->write(json_encode(['message' => 'Marks saved successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function uploadMarksCsv(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];

        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'] ?? null;

        if (!$file || $file->getError() !== UPLOAD_ERR_OK) {
            $response->getBody()->write(json_encode(['message' => 'Invalid file upload']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $stream = $file->getStream()->getContents();
        $lines = explode(PHP_EOL, $stream);
        $rows = array_map('str_getcsv', $lines);
        $headerRow = array_shift($rows);
        $header = array_map(function ($h) {
            return strtolower(trim($h, "\" \t\n\r\0\x0B\xEF\xBB\xBF"));
        }, $headerRow);

        if (!in_array('matric_number', $header)) {
            $response->getBody()->write(json_encode(['message' => 'Missing matric_number column']));
            return $response->withHeader('Content-Type', 'application/json');
        }

        $remarksCol = array_search('remarks', $header);
        $assessmentTitles = array_diff($header, ['matric_number', 'Remarks']);

        // Map assessments
        $stmt = $this->db->prepare("SELECT id, title, max_mark FROM assessments WHERE course_id = ?");
        $stmt->execute([$courseId]);
        $assessments = $stmt->fetchAll();
        $titleToId = [];
        foreach ($assessments as $a) {
            $cleanTitle = strtolower(trim($a['title']));
            $titleToId[$cleanTitle] = $a['id'];
        }


        // Map matric to student_id
        $stmt = $this->db->prepare("SELECT id, matric_number FROM users");
        $stmt->execute();
        $matricToId = [];
        foreach ($stmt->fetchAll() as $user) {
            $matricToId[$user['matric_number']] = $user['id'];
        }

        // Insert/update marks
        $insertStmt = $this->db->prepare("
            INSERT INTO student_assessments (student_id, assessment_id, obtained_mark)
            VALUES (?, ?, ?)
            ON DUPLICATE KEY UPDATE obtained_mark = VALUES(obtained_mark)
        ");
        $updateRemarks = $this->db->prepare("
            UPDATE student_courses SET remarks = ? WHERE course_id = ? AND student_id = ?
        ");

        foreach ($rows as $row) {
            if (empty($row) || count($row) < 2) continue;

            $row = array_map('trim', $row);
            $data = array_combine($header, $row);
            $matric = $data['matric_number'] ?? null;
            $studentId = $matricToId[$matric] ?? null;
            if (!$studentId) continue;

            foreach ($assessmentTitles as $title) {
                if (!isset($data[$title]) || !is_numeric($data[$title])) continue;
                
                $normalizedTitle = strtolower(trim($title));
                $assessmentId = $titleToId[$normalizedTitle] ?? null;

                // 🔍 Debug output
                error_log("Processing: {$matric} - {$title} - Value: {$data[$title]}");
                if (!$assessmentId) {
                    error_log("❌ Assessment title not matched in DB: " . $title);
                }
                if (!$assessmentId) {
                    error_log("❌ Assessment not found: " . $normalizedTitle);
                }
                if ($assessmentId) {
                    $insertStmt->execute([$studentId, $assessmentId, $data[$title]]);
                }
            }

            // Update remark if provided
            if ($remarksCol !== false && !empty($data['remarks'])) {
                $updateRemarks->execute([$data['remarks'], $courseId, $studentId]);
                error_log("💬 Remark for $matric: " . ($data['remarks'] ?? 'null'));
            }
        }

        $response->getBody()->write(json_encode(['message' => 'CSV imported successfully']));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get course analytics
    public function getCourseAnalytics(Request $request, Response $response, $args): Response
    {
        $courseId = $args['course_id'];

        // Get enrolled students
        $stmt = $this->db->prepare("
            SELECT u.id, u.name, u.matric_number
            FROM student_courses sc
            JOIN users u ON sc.student_id = u.id
            WHERE sc.course_id = ?
        ");
        $stmt->execute([$courseId]);
        $students = $stmt->fetchAll();

        // Get assessments
        $stmt = $this->db->prepare("
            SELECT id, title, type, weight_percentage, max_mark FROM assessments
            WHERE course_id = ?
        ");
        $stmt->execute([$courseId]);
        $assessments = $stmt->fetchAll();

        // Get all marks
        $stmt = $this->db->prepare("
            SELECT * FROM student_assessments
            WHERE assessment_id IN (SELECT id FROM assessments WHERE course_id = ?)
        ");
        $stmt->execute([$courseId]);
        $marks = $stmt->fetchAll();

        // Group marks by student
        $studentData = [];
        foreach ($students as $s) {
            $sid = $s['id'];
            $studentData[$sid] = [
                'name' => $s['name'],
                'matric_number' => $s['matric_number'],
                'marks' => [],
                'total' => 0,
                'grade' => ''
            ];
        }

        foreach ($marks as $m) {
            $sid = $m['student_id'];
            $aid = $m['assessment_id'];
            $assessment = array_filter($assessments, fn($a) => $a['id'] == $aid);
            $assessment = array_values($assessment)[0] ?? null;

            if ($assessment) {
                $weight = floatval($assessment['weight_percentage']);
                $mark = floatval($m['obtained_mark']);
                $max = floatval($assessment['max_mark']);
                $percent = ($max > 0) ? ($mark / $max) * $weight : 0;

                $studentData[$sid]['marks'][] = [
                    'assessment' => $assessment['title'],
                    'type' => $assessment['type'],
                    'obtained' => $mark,
                    'max' => $max,
                    'weighted' => round($percent, 2)
                ];

                $studentData[$sid]['total'] = ($studentData[$sid]['total'] ?? 0) + $percent;
            }
        }

        // Calculate grade
        foreach ($studentData as &$s) {
            $total = $s['total'];
            $s['grade'] = match (true) {
                $total >= 90 => 'A+',
                $total >= 80 => 'A',
                $total >= 75 => 'A-',
                $total >= 70 => 'B+',
                $total >= 65 => 'B',
                $total >= 60 => 'B-',
                $total >= 55 => 'C+',
                $total >= 50 => 'C',
                $total >= 45 => 'C-',
                $total >= 40 => 'D+',
                $total >= 35 => 'D',
                $total >= 30 => 'D-',
                default => 'E'
            };
        }

        $response->getBody()->write(json_encode([
            'students' => array_values($studentData),
            'assessments' => $assessments
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Get lecturer info for profile view
    public function getProfile(Request $request, Response $response): Response
    {
        $user = json_decode($request->getHeaderLine('X-User'), true);
        $userId = $user['id'];

        // Get user details
        $stmt = $this->db->prepare("SELECT id, name, email, matric_number FROM users WHERE id = ?");
        $stmt->execute([$userId]);
        $profile = $stmt->fetch();

        // Get courses taught by this lecturer
        $stmt = $this->db->prepare("
            SELECT c.id, c.course_code, c.course_name, c.semester, c.year
            FROM lecturer_courses lc
            JOIN courses c ON lc.course_id = c.id
            WHERE lc.lecturer_id = ?
        ");
        $stmt->execute([$userId]);
        $courses = $stmt->fetchAll();

        $response->getBody()->write(json_encode([
            'profile' => $profile,
            'courses' => $courses
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function getRemarkRequests(Request $request, Response $response): Response
    {
        $user = json_decode($request->getHeaderLine('X-User'), true);
        $lecturerId = $user['id'];

        // Get all requests related to lecturer's courses
        $stmt = $this->db->prepare("
            SELECT rr.id, rr.justification, rr.supporting_link, rr.status, rr.created_at,
                a.title AS assessment_title,
                u.name AS student_name, u.matric_number,
                c.course_code
            FROM remark_requests rr
            JOIN assessments a ON rr.assessment_id = a.id
            JOIN courses c ON a.course_id = c.id
            JOIN lecturer_courses lc ON c.id = lc.course_id
            JOIN users u ON rr.student_id = u.id
            WHERE lc.lecturer_id = ?
            ORDER BY rr.created_at DESC
            LIMIT 10
        ");
        $stmt->execute([$lecturerId]);
        $requests = $stmt->fetchAll();

        $response->getBody()->write(json_encode(['requests' => $requests]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    public function updateRemarkRequest(Request $request, Response $response, array $args): Response
    {
        $id = $args['id'];
        $data = json_decode($request->getBody()->getContents(), true);
        $status = $data['status'] ?? 'pending';

        // Validate status
        if (!in_array($status, ['pending', 'reviewed', 'approved', 'rejected'])) {
            $response->getBody()->write(json_encode(['message' => 'Invalid status']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        // Check if remark request exists
        $stmt = $this->db->prepare("SELECT * FROM remark_requests WHERE id = ?");
        $stmt->execute([$id]);
        $existingRequest = $stmt->fetch();

        if (!$existingRequest) {
            $response->getBody()->write(json_encode(['message' => 'Remark request not found']));
            return $response->withStatus(404)->withHeader('Content-Type', 'application/json');
        }

        // Perform update
        $stmt = $this->db->prepare("UPDATE remark_requests SET status = ?, updated_at = NOW() WHERE id = ?");
        $stmt->execute([$status, $id]);

        // Fetch updated request
        $stmt = $this->db->prepare("SELECT * FROM remark_requests WHERE id = ?");
        $stmt->execute([$id]);
        $updatedRequest = $stmt->fetch();

        // Return JSON
        $response->getBody()->write(json_encode([
            'message' => 'Status updated',
            'remark' => $updatedRequest
        ]));
        return $response->withHeader('Content-Type', 'application/json');
    }

    // Profile update
    public function updateProfile(Request $request, Response $response): Response
    {
        $user = json_decode($request->getHeaderLine('X-User'), true);
        $id = $user['id'];

        $data = json_decode($request->getBody()->getContents(), true);
        $name = $data['name'] ?? null;
        $email = $data['email'] ?? null;

        if (!$name || !$email) {
            $response->getBody()->write(json_encode(['message' => 'Name and email are required']));
            return $response->withStatus(400)->withHeader('Content-Type', 'application/json');
        }

        $stmt = $this->db->prepare("UPDATE users SET name = ?, email = ? WHERE id = ?");
        $stmt->execute([$name, $email, $id]);

        $response->getBody()->write(json_encode(['message' => 'Profile updated']));
        return $response->withHeader('Content-Type', 'application/json');
    }

}
