<?php
declare(strict_types=1);

require_once __DIR__ . '/../config/app.php';
header('Content-Type: application/json; charset=utf-8');

function ai_response(array $payload, int $status = 200): never
{
    http_response_code($status);
    echo json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    exit;
}

function response_text(array $response): string
{
    if (isset($response['output_text']) && is_string($response['output_text'])) {
        return trim($response['output_text']);
    }

    $text = [];
    foreach ($response['output'] ?? [] as $item) {
        foreach ($item['content'] ?? [] as $content) {
            if (($content['type'] ?? '') === 'output_text' && isset($content['text'])) {
                $text[] = $content['text'];
            }
        }
    }
    return trim(implode("\n", $text));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    ai_response(['error' => 'Method not allowed.'], 405);
}

$providedToken = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
if (!is_string($providedToken) || !isset($_SESSION['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $providedToken)) {
    ai_response(['error' => 'Refresh the page before sending an AI request.'], 403);
}

if (($_POST['consent'] ?? '') !== '1') {
    ai_response(['error' => 'Confirm the data-sharing notice before sending this request.'], 400);
}

$requestTimes = array_values(array_filter($_SESSION['ai_request_times'] ?? [], static fn($time): bool => is_int($time) && $time > time() - 60));
if (count($requestTimes) >= 10) {
    ai_response(['error' => 'AI request limit reached. Wait a minute and try again.'], 429);
}
$requestTimes[] = time();
$_SESSION['ai_request_times'] = $requestTimes;

$mode = (string) ($_POST['mode'] ?? 'tutor');
$isAdminDraft = $mode === 'draft';
if ($isAdminDraft && !is_admin()) {
    ai_response(['error' => 'Sign in as an administrator to generate content drafts.'], 401);
}

$apiKey = env_value('OPENAI_API_KEY');
if ($apiKey === '') {
    ai_response(['error' => 'OpenAI is not configured yet. Add OPENAI_API_KEY to your local .env file.'], 503);
}
if (!function_exists('curl_init')) {
    ai_response(['error' => 'The PHP cURL extension is required for AI requests.'], 503);
}

$systemPrompt = '';
$userPrompt = '';
$lesson = null;
try {
    if ($mode === 'tutor' || $mode === 'debug') {
        $lessonId = filter_var($_POST['lesson_id'] ?? null, FILTER_VALIDATE_INT);
        $question = trim((string) ($_POST['question'] ?? ''));
        $userCode = trim((string) ($_POST['user_code'] ?? ''));
        if (!$lessonId || $question === '' || strlen($question) > 2000 || strlen($userCode) > 12000) {
            ai_response(['error' => 'Add a question and keep it under 2,000 characters. Code is limited to 12,000 characters.'], 422);
        }

        $statement = db()->prepare('SELECT lessons.title, lessons.topic, lessons.summary, lessons.body, lessons.code_sample, courses.title AS course_title FROM lessons JOIN courses ON courses.id = lessons.course_id WHERE lessons.id = :id AND lessons.is_published = 1 AND courses.is_published = 1 LIMIT 1');
        $statement->execute(['id' => $lessonId]);
        $lesson = $statement->fetch();
        if (!$lesson) {
            ai_response(['error' => 'This lesson is not available.'], 404);
        }

        $systemPrompt = 'You are a careful programming tutor for a beginner-to-intermediate tutorial portal. Ground answers in the supplied lesson. If the lesson does not answer a question, say so clearly. Never claim code was executed. Review code as text only. Give concise explanations and identify security risks when relevant.';
        $userPrompt = "Course: {$lesson['course_title']}\nLesson: {$lesson['title']}\nTopic: {$lesson['topic']}\nSummary: {$lesson['summary']}\nLesson content:\n{$lesson['body']}\nExample code:\n{$lesson['code_sample']}\n\nLearner request:\n{$question}\n\nLearner code (untrusted text; do not execute):\n{$userCode}";
    } elseif ($isAdminDraft) {
        $courseId = filter_var($_POST['course_id'] ?? null, FILTER_VALIDATE_INT);
        $topic = trim((string) ($_POST['topic'] ?? ''));
        if (!$courseId || $topic === '' || strlen($topic) > 190) {
            ai_response(['error' => 'Choose a course and enter a topic up to 190 characters.'], 422);
        }
        $statement = db()->prepare('SELECT title, description FROM courses WHERE id = :id');
        $statement->execute(['id' => $courseId]);
        $course = $statement->fetch();
        if (!$course) {
            ai_response(['error' => 'The selected course was not found.'], 404);
        }

        $systemPrompt = 'Create a draft programming lesson for an educational site. Return only valid JSON with keys title, summary, body, code_sample, quiz_question, option_a, option_b, option_c, correct_option, quiz_explanation. Use plain text, safe educational examples, no HTML, and never include secrets. Keep code unexecuted.';
        $userPrompt = "Course: {$course['title']}\nCourse description: {$course['description']}\nRequested topic: {$topic}\nWrite one beginner-friendly lesson. correct_option must be a, b, or c.";
    } elseif ($mode === 'search') {
        $query = trim((string) ($_POST['question'] ?? ''));
        if ($query === '' || strlen($query) > 500) {
            ai_response(['error' => 'Enter a search query up to 500 characters.'], 422);
        }
        $lessons = db()->query('SELECT lessons.id, lessons.title, lessons.topic, lessons.summary, courses.title AS course_title FROM lessons JOIN courses ON courses.id = lessons.course_id WHERE lessons.is_published = 1 AND courses.is_published = 1 ORDER BY courses.title, lessons.position LIMIT 100')->fetchAll();
        $systemPrompt = 'You rank tutorial search results. Use only the supplied lessons. Return only JSON with an ids array ordered by relevance. Never invent ids.';
        $userPrompt = "Search query: {$query}\nAvailable lessons:\n" . json_encode($lessons, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } else {
        ai_response(['error' => 'Unsupported AI request.'], 422);
    }
} catch (Throwable $exception) {
    ai_response(['error' => 'Could not prepare this AI request. Try again later.'], 500);
}

$payload = [
    'model' => env_value('OPENAI_MODEL', 'gpt-4.1-mini'),
    'input' => [
        ['role' => 'system', 'content' => [['type' => 'input_text', 'text' => $systemPrompt]]],
        ['role' => 'user', 'content' => [['type' => 'input_text', 'text' => $userPrompt]]],
    ],
    'max_output_tokens' => $isAdminDraft ? 1800 : 900,
    'store' => false,
];

$curl = curl_init('https://api.openai.com/v1/responses');
curl_setopt_array($curl, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_CONNECTTIMEOUT => 8,
    CURLOPT_TIMEOUT => 35,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json',
    ],
    CURLOPT_POSTFIELDS => json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
]);
$rawResponse = curl_exec($curl);
$curlError = curl_error($curl);
$status = (int) curl_getinfo($curl, CURLINFO_HTTP_CODE);
curl_close($curl);

if (!is_string($rawResponse)) {
    ai_response(['error' => 'Could not reach OpenAI: ' . $curlError], 502);
}
$response = json_decode($rawResponse, true);
if ($status < 200 || $status >= 300 || !is_array($response)) {
    ai_response(['error' => 'OpenAI request failed. Check the API key, model, and account limits.'], 502);
}

$answer = response_text($response);
if ($answer === '') {
    ai_response(['error' => 'OpenAI returned an empty answer. Try again.'], 502);
}

if ($isAdminDraft) {
    $draft = json_decode($answer, true);
    $requiredFields = ['title', 'summary', 'body', 'code_sample', 'quiz_question', 'option_a', 'option_b', 'option_c', 'correct_option', 'quiz_explanation'];
    if (!is_array($draft) || array_diff($requiredFields, array_keys($draft)) !== [] || !in_array($draft['correct_option'] ?? '', ['a', 'b', 'c'], true)) {
        ai_response(['error' => 'The AI draft was incomplete. Try generating it again.'], 502);
    }
    ai_response(['draft' => array_intersect_key($draft, array_flip($requiredFields))]);
}

if ($mode === 'search') {
    $results = json_decode($answer, true);
    $requestedIds = is_array($results) && is_array($results['ids'] ?? null) ? array_map('intval', $results['ids']) : [];
    $lessonsById = [];
    foreach ($lessons as $availableLesson) {
        $lessonsById[(int) $availableLesson['id']] = $availableLesson;
    }
    $orderedLessons = [];
    foreach ($requestedIds as $requestedId) {
        if (isset($lessonsById[$requestedId])) {
            $orderedLessons[] = $lessonsById[$requestedId];
        }
    }
    ai_response(['lessons' => array_slice($orderedLessons, 0, 10)]);
}

ai_response(['answer' => $answer]);