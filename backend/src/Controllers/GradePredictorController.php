<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use PDO;

class GradePredictorController
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function predict(Request $request, Response $response): Response
    {
        try {
            $input = json_decode($request->getBody()->getContents(), true);

            $studentId = $input['student_id'] ?? null;
            $courseId = $input['course_id'] ?? null;
            $predictedScores = $input['predicted_scores'] ?? [];

            if (!$studentId || !$courseId) {
                return $this->json($response, ['error' => 'Missing required fields.'], 400);
            }


            // Convert to map
            $predictedMap = [];
            foreach ($predictedScores as $p) {
                $predictedMap[$p['assessment_id']] = $p['expected_mark'];
            }

            // Fetch assessments
            $stmt = $this->db->prepare("SELECT id, title, max_mark, weight_percentage FROM assessments WHERE course_id = ?");
            $stmt->execute([$courseId]);
            $assessments = $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];

            if (empty($assessments)) {
                return $this->json($response, ['error' => 'No assessments found.'], 404);
            }

            // Fetch actual student marks
            $stmt2 = $this->db->prepare("
                SELECT assessment_id, obtained_mark 
                FROM student_assessments 
                WHERE student_id = ? AND assessment_id IN (
                    SELECT id FROM assessments WHERE course_id = ?
                )
            ");
            $stmt2->execute([$studentId, $courseId]);
            $actualScores = $stmt2->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];

            $totalScore = 0;
            $details = [];

            foreach ($assessments as $a) {
                $aid = $a['id'];
                $mark = $actualScores[$aid] ?? ($predictedMap[$aid] ?? null);
                $isPredicted = !isset($actualScores[$aid]) && isset($predictedMap[$aid]);

                if ($mark !== null && $a['max_mark'] > 0) {
                    $weighted = ($mark / $a['max_mark']) * $a['weight_percentage'];
                    $totalScore += $weighted;
                }

                $details[] = [
                    'title' => $a['title'],
                    'weight' => $a['weight_percentage'],
                    'mark' => $mark,
                    'max' => $a['max_mark'],
                    'isPredicted' => $isPredicted
                ];
            }

            $percent = round($totalScore, 2);
            $grade = $this->mapGrade($percent);
            $cgpa = $this->mapCGPA($percent); // 🆕 Added

            return $this->json($response, [
                'course_id' => $courseId,
                'student_id' => $studentId,
                'predicted_percentage' => $percent,
                'predicted_grade' => $grade,
                'predicted_cgpa' => number_format($cgpa, 2),
                'details' => $details
            ]);
        } catch (\Throwable $e) {
            return $this->json($response, ['error' => $e->getMessage()], 500);
        }
    }

    private function mapGrade($percent)
    {
        if ($percent >= 90)
            return 'A+';
        if ($percent >= 80)
            return 'A';
        if ($percent >= 75)
            return 'A-';
        if ($percent >= 70)
            return 'B+';
        if ($percent >= 65)
            return 'B';
        if ($percent >= 60)
            return 'B-';
        if ($percent >= 55)
            return 'C+';
        if ($percent >= 50)
            return 'C';
        if ($percent >= 45)
            return 'C-';
        if ($percent >= 40)
            return 'D+';
        if ($percent >= 35)
            return 'D';
        if ($percent >= 30)
            return 'D-';
        return 'E';
    }

    // 🆕 Added: Map Percentage to CGPA
    private function mapCGPA($percent)
    {
        if ($percent >= 90)
            return 4.00;
        if ($percent >= 80)
            return 4.00;
        if ($percent >= 75)
            return 3.67;
        if ($percent >= 70)
            return 3.33;
        if ($percent >= 65)
            return 3.00;
        if ($percent >= 60)
            return 2.67;
        if ($percent >= 55)
            return 2.33;
        if ($percent >= 50)
            return 2.00;
        if ($percent >= 45)
            return 1.67;
        if ($percent >= 40)
            return 1.33;
        if ($percent >= 35)
            return 1.00;
        if ($percent >= 30)
            return 0.67;
        return 0.00;
    }

    private function json(Response $response, $data, $status = 200)
    {
        $response->getBody()->write(json_encode($data));
        return $response->withStatus($status)->withHeader('Content-Type', 'application/json');
    }
}
