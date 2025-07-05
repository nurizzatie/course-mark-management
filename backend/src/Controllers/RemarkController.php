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
        $this->uploadDir = __DIR__ . '/../../uploads/remarks';
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }
    }

    public function submitRemarkRequest(Request $request, Response $response): Response
    {
        $parsed = $request->getParsedBody();
        $studentId = $parsed['student_id'] ?? null;
        $assessmentId = $parsed['assessment_id'] ?? null;
        $justification = $parsed['justification'] ?? '';
        $supportingLink = $parsed['supporting_link'] ?? null;

        // File handling
        $uploadedFiles = $request->getUploadedFiles();
        $file = $uploadedFiles['file'] ?? null;
        $filePath = null;

        if ($file && $file->getError() === UPLOAD_ERR_OK) {
            $filename = uniqid() . '_' . $file->getClientFilename();
            $file->moveTo("{$this->uploadDir}/$filename");
            $filePath = 'uploads/remarks/' . $filename;
        }

        // Validate required
        if (!$studentId || !$assessmentId || !$justification || (!$file && !$supportingLink)) {
            return $response
                ->withStatus(400)
                ->withHeader('Content-Type', 'application/json')
                ->withBody(stream_for(json_encode(['error' => 'Missing required fields'])));
        }

        // Save to DB
        $stmt = $this->db->prepare("
            INSERT INTO remark_requests (student_id, assessment_id, justification, supporting_link, created_at, status)
            VALUES (:student_id, :assessment_id, :justification, :supporting_link, NOW(), 'pending')
        ");
        $stmt->execute([
            ':student_id' => $studentId,
            ':assessment_id' => $assessmentId,
            ':justification' => $justification,
            ':supporting_link' => $supportingLink
        ]);

        return $response->withHeader('Content-Type', 'application/json')
            ->withStatus(200)
            ->write(json_encode(['message' => 'Remark request submitted']));
    }
}
