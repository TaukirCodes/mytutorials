<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
header('Content-Type: application/json; charset=utf-8');

function quiz_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    quiz_response(['error' => 'Method not allowed.'], 405);
}

$providedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($providedToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $providedToken)) {
    quiz_response(['error' => 'Refresh the lesson before submitting your answer.'], 403);
}

$questionId = filter_var($_POST['question_id'] ?? null, FILTER_VALIDATE_INT);
$answer = strtolower(trim((string) ($_POST['answer'] ?? '')));
if (!$questionId || !in_array($answer, ['a', 'b', 'c'], true)) {
    quiz_response(['error' => 'Choose one answer and try again.'], 422);
}

try {
    $statement = db()->prepare('SELECT quiz_questions.correct_option, quiz_questions.explanation, lessons.id AS lesson_id FROM quiz_questions JOIN lessons ON lessons.id = quiz_questions.lesson_id JOIN courses ON courses.id = lessons.course_id WHERE quiz_questions.id = :id AND lessons.is_published = 1 AND courses.is_published = 1 LIMIT 1');
    $statement->execute(['id' => $questionId]);
    $question = $statement->fetch();
    if (!$question) {
        quiz_response(['error' => 'This quiz question is no longer available.'], 404);
    }

    quiz_response([
        'correct' => hash_equals($question['correct_option'], $answer),
        'lesson_id' => (int) $question['lesson_id'],
        'explanation' => $question['explanation'],
    ]);
} catch (Throwable $exception) {
    quiz_response(['error' => 'Could not check this answer. Try again later.'], 500);
}