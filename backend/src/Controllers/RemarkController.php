<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Slim\Psr7\UploadedFile;

class RemarkController
{
    protected $db;
    protected $uploadDir;

    public function __construct($container)
    {
        $this->db = $container->get('db');

        // Prefer public uploads dir if writable
        $preferred = __DIR__ . '/../../public/uploads/remarks';
        $fallback = sys_get_temp_dir() . '/uploads/remarks';

        $this->uploadDir = is_writable(dirname($preferred)) ? $preferred : $fallback;

        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    // ==============================
    // Submit New Remark Request
    // ==============================
    public function submitRemarkRequest(Request $request, Response $response): Response
{
    $parsed = $request->getParsedBody();
    $studentId = $parsed['student_id'] ?? null;
    $assessmentId = $parsed['assessment_id'] ?? null;
    $justification = $parsed['justification'] ?? '';
    $supportingLink = $parsed['supporting_link'] ?? null;

    $uploadedFiles = $request->getUploadedFiles();
    $file = $uploadedFiles['file'] ?? null;
    $filePath = $file ? $this->handleFileUpload($file) : null;

    if (!$studentId || !$assessmentId || !$justification || (!$filePath && !$supportingLink)) {
        return $this->json($response, ['error' => 'Missing required fields.'], 400);
    }

    $stmt = $this->db->prepare("
        INSERT INTO remark_requests (student_id, assessment_id, justification, supporting_link, created_at, status)
        VALUES (:student_id, :assessment_id, :justification, :supporting_link, NOW(), 'pending')
    ");
    $stmt->execute([
        ':student_id' => $studentId,
        ':assessment_id' => $assessmentId,
        ':justification' => $justification,
        ':supporting_link' => $filePath ?? $supportingLink
    ]);

    $newId = $this->db->lastInsertId();

    $stmt = $this->db->prepare("
        SELECT rr.id, rr.justification, rr.status, rr.created_at,
               rr.supporting_link,
               a.title AS assessment_title,
               c.course_code,
               u.name AS student_name,
               u.matric_number
        FROM remark_requests rr
        JOIN assessments a ON rr.assessment_id = a.id
        JOIN courses c ON a.course_id = c.id
        JOIN users u ON rr.student_id = u.id
        WHERE rr.id = :id
    ");
    $stmt->execute([':id' => $newId]);
    $detail = $stmt->fetch(\PDO::FETCH_ASSOC);

    // Return the full inserted remark detail
    return $this->json($response, $detail, 201);
}


    // ==============================
    // Submit Appeal After Rejection
    // ==============================
    public function submitAppeal(Request $request, Response $response): Response
{
    $parsed = $request->getParsedBody();
    $uploadedFiles = $request->getUploadedFiles();

    $studentId = $parsed['student_id'] ?? null;
    $assessmentId = $parsed['assessment_id'] ?? null;
    $justification = $parsed['justification'] ?? '';
    $supportingLink = $parsed['supporting_link'] ?? null;

    if (!$studentId || !$assessmentId || !$justification) {
        return $this->json($response, ['error' => 'Missing fields.'], 400);
    }

    // Check appeal eligibility
    $check = $this->db->prepare("
        SELECT appeal_count FROM remark_requests
        WHERE student_id = :student_id AND assessment_id = :assessment_id AND status = 'rejected'
    ");
    $check->execute([
        ':student_id' => $studentId,
        ':assessment_id' => $assessmentId
    ]);

    $row = $check->fetch(\PDO::FETCH_ASSOC);
    if (!$row) {
        return $this->json($response, ['error' => 'No rejected remark found to appeal.'], 404);
    }

    if ($row['appeal_count'] >= 2) {
        return $this->json($response, ['error' => 'You have reached the maximum number of appeals (2).'], 403);
    }

    // Optional file upload
    $uploadedPath = null;
    if (isset($uploadedFiles['supporting_file'])) {
        $uploadedPath = $this->handleFileUpload($uploadedFiles['supporting_file']);
    }

    // Update remark request
    $stmt = $this->db->prepare("
        UPDATE remark_requests
        SET 
            justification = :justification,
            supporting_link = :supporting_link,
            status = 'pending',
            is_appeal = 1,
            appeal_count = appeal_count + 1,
            updated_at = NOW()
        WHERE student_id = :student_id 
          AND assessment_id = :assessment_id 
          AND status = 'rejected'
    ");
    $stmt->execute([
        ':justification' => $justification,
        ':supporting_link' => $uploadedPath ?? $supportingLink,
        ':student_id' => $studentId,
        ':assessment_id' => $assessmentId   
    ]);

    return $this->json($response, ['message' => 'Appeal submitted']);
}


    public function getAppealCount(Request $request, Response $response): Response
    {
        $queryParams = $request->getQueryParams();
        $studentId = $queryParams['student_id'] ?? null;
        $assessmentId = $queryParams['assessment_id'] ?? null;

        if (!$studentId || !$assessmentId) {
        return $this->json($response, ['error' => 'Missing student_id or assessment_id'], 400);
    }

        $stmt = $this->db->prepare("
        SELECT appeal_count FROM remark_requests
        WHERE student_id = :student_id AND assessment_id = :assessment_id AND is_appeal = 1
    ");
        $stmt->execute([
            ':student_id' => $studentId,
            ':assessment_id' => $assessmentId
        ]);

        $row = $stmt->fetch();
        $count = $row ? $row['appeal_count'] : 0;

        $response->getBody()->write(json_encode(['appeal_count' => (int) $count]));
        return $response->withHeader('Content-Type', 'application/json');
    }


    // ==============================
    // Private: File Upload Handler
    // ==============================
    private function handleFileUpload(UploadedFile $file): ?string
    {
        if ($file->getError() === UPLOAD_ERR_OK) {
            $filename = uniqid() . '_' . $file->getClientFilename();
            $path = $this->uploadDir . '/' . $filename;
            $file->moveTo($path);
            return 'uploads/remarks/' . $filename;
        }
        return null;
    }

    public function downloadFile(Request $request, Response $response, array $args): Response
    {
        $filename = basename($args['filename']);
        $filePath = $this->uploadDir . '/' . $filename;

        if (!file_exists($filePath)) {
            $response->getBody()->write("File not found.");
            return $response->withStatus(404);
        }

        $stream = new \Slim\Psr7\Stream(fopen($filePath, 'rb'));

        return $response
            ->withBody($stream)
            ->withHeader('Content-Type', mime_content_type($filePath))
            ->withHeader('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->withHeader('Content-Length', filesize($filePath))
            ->withStatus(200);
    }


    // ==============================
    // Private: JSON Response Helper
    // ==============================
    private function json(Response $response, array $data, int $status = 200): Response
    {
        $response->getBody()->write(json_encode($data));
        return $response->withHeader('Content-Type', 'application/json')->withStatus($status);
    }


}
