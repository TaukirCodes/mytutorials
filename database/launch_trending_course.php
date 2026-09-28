<?php
declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';

$course = [
    'slug' => 'ai-agents-mcp-with-python',
    'title' => 'AI Agents & MCP with Python',
    'category' => 'AI Engineering',
    'description' => 'Build tool-using AI agents, create an MCP server in Python, and add practical safety checks before connecting tools to real workflows.',
    'level' => 'Beginner',
];

$lessons = [
    [
        'slug' => 'how-ai-agents-use-tools',
        'topic' => 'Agent fundamentals',
        'title' => 'How an AI Agent Uses Tools',
        'summary' => 'Trace the controlled loop between a model, your application, and a tool.',
        'body' => <<<'TEXT'
An AI agent combines a model with an application that can perform bounded actions. The model reads the conversation and tool descriptions, then may propose a tool name and arguments. Your application checks that proposal, runs the matching function, and sends the result back so the model can continue.

The model does not execute Python just because it returned code or a tool call. Your program owns execution. Keep a registry of known tools, validate each argument, and reject unknown names. This boundary makes it possible to add permissions and review steps.

Agent loops need clear limits. Set a maximum number of tool steps, a request deadline, and a useful response for tool errors. If a task needs a sensitive action such as sending a message or changing a record, pause for human approval before the side effect.

Start with read-only tools such as searching a help centre. Add write actions only when the workflow, permissions, and approval path are clear.
TEXT,
        'code_sample' => <<<'PY'
def lookup_course(course_id: int) -> str:
    # Read from an approved course catalogue.
    return f"Course {course_id}: Python foundations"

TOOLS = {"lookup_course": lookup_course}
MAX_TOOL_STEPS = 4

def run_tool(name: str, course_id: int) -> str:
    handler = TOOLS.get(name)
    if handler is None:
        raise ValueError("Tool is not available")
    if not isinstance(course_id, int) or course_id < 1:
        raise ValueError("course_id must be a positive integer")
    return handler(course_id)
PY,
        'quiz' => [
            'question' => 'Who is responsible for validating a model-proposed tool call before it runs?',
            'a' => 'The application that owns the tool registry',
            'b' => 'The model, without application checks',
            'c' => 'The operating system after arbitrary code runs',
            'correct' => 'a',
            'explanation' => 'The application must check the tool name, arguments, permissions, and approval requirements before executing a tool.',
        ],
    ],
    [
        'slug' => 'mcp-host-client-server',
        'topic' => 'MCP foundations',
        'title' => 'MCP Architecture: Host, Client, Server',
        'summary' => 'Learn how MCP standardizes the connection between AI apps and reusable capabilities.',
        'body' => <<<'TEXT'
The Model Context Protocol (MCP) is an open protocol for connecting AI applications to external context and capabilities. It gives applications and servers a shared way to discover and exchange information instead of requiring a custom integration for every pair.

The host is the user-facing AI application. It manages the conversation and its policy. An MCP client inside the host maintains a connection to an MCP server. The server exposes capabilities and performs the work it owns. One host can connect to multiple servers, and a server can be used by compatible hosts.

MCP servers commonly expose tools (actions), resources (readable context), and prompts (reusable templates). These are different capabilities: a resource is not automatically an action, and a prompt is not a permission grant.

MCP standardizes communication; it does not decide whether an action is safe for a particular user. Hosts and servers still need to apply permissions, authentication, and approval rules. Treat each server as a separate trust boundary and connect only to servers you understand.
TEXT,
        'code_sample' => <<<'PY'
# A mental model for a connected AI application
host = "chat app, model, and user policy"
client = "protocol connection managed by the host"
server = {
    "tools": ["search_courses"],
    "resources": ["course_catalogue"],
    "prompts": ["explain_a_lesson"],
}

# The host decides whether a proposed action is allowed.
PY,
        'quiz' => [
            'question' => 'Which MCP capability represents an action the server can perform?',
            'a' => 'A resource',
            'b' => 'A tool',
            'c' => 'A prompt template',
            'correct' => 'b',
            'explanation' => 'Tools represent callable actions. Resources provide readable context, while prompts provide reusable templates.',
        ],
    ],
    [
        'slug' => 'build-mcp-server-python',
        'topic' => 'Python MCP server',
        'title' => 'Build an MCP Server in Python',
        'summary' => 'Expose a small, read-only search tool using the current Python MCP SDK.',
        'body' => <<<'TEXT'
This example uses the Python MCP SDK 2.x API. The official quickstart requires Python 3.10 or higher. In a project managed by uv, install the SDK with: uv add "mcp[cli]".

MCPServer reads Python type hints and the tool docstring to build the tool definition. Keep the tool focused: this one searches a small in-memory collection and returns text. In a real project, replace the sample collection with a read-only data source and validate query length before sending work downstream.

The server uses standard input and output (stdio) to communicate with a local host. Do not print debug messages to stdout in this mode; stdout is reserved for protocol messages. Use Python logging for diagnostics. For remote deployments, select and secure an HTTP transport appropriate to your host and authentication setup.
TEXT,
        'code_sample' => <<<'PY'
from mcp.server import MCPServer

mcp = MCPServer("study-notes")
notes = {
    "agents": "An agent delegates bounded work to approved tools.",
    "mcp": "MCP connects AI hosts to tools, resources, and prompts.",
}

@mcp.tool()
def search_notes(query: str) -> str:
    """Search short notes about agents and MCP."""
    term = query.strip().lower()
    if not term or len(term) > 80:
        return "Enter a search term of 1 to 80 characters."
    matches = [text for title, text in notes.items() if term in title]
    return "\n".join(matches) if matches else "No matching note found."

if __name__ == "__main__":
    mcp.run(transport="stdio")
PY,
        'quiz' => [
            'question' => 'Why should a stdio MCP server avoid printing debug output to stdout?',
            'a' => 'The host uses stdout for protocol messages',
            'b' => 'Python does not support stdout logging',
            'c' => 'The server can expose only one tool',
            'correct' => 'a',
            'explanation' => 'Extra stdout text can corrupt the protocol stream. Send diagnostics to stderr through a logger.',
        ],
    ],
    [
        'slug' => 'design-safe-agent-tool-calls',
        'topic' => 'Tool calling and validation',
        'title' => 'Design Safe Agent Tool Calls',
        'summary' => 'Turn model suggestions into constrained, reviewable application actions.',
        'body' => <<<'TEXT'
A model can suggest an action, but the application should treat that suggestion as untrusted input. Parse a structured tool name and argument object, look up the name in an allowlist, validate every field, and enforce the current user's permissions. Reject extra or malformed data instead of trying to guess what was intended.

Choose small tools with one clear purpose, such as search_courses(query) or get_lesson(lesson_id). Avoid powerful catch-all tools such as run_shell(command), execute_sql(query), or browse_any_path(path). Narrow interfaces make validation and auditing easier.

Separate read actions from actions that change data. For a write, show the person the exact proposed change and ask for confirmation. Do not let content returned by a webpage, file, or tool override system policy; external text may contain prompt injection instructions. Tool output is data to inspect, not authority to follow.

Record which user requested a tool, what validated action ran, and whether it succeeded. Keep secrets out of model context and tool results.
TEXT,
        'code_sample' => <<<'PY'
ALLOWED_TOOLS = {"search_courses", "get_lesson"}

def validate_call(call: dict) -> tuple[str, dict]:
    name = call.get("name")
    arguments = call.get("arguments")
    if name not in ALLOWED_TOOLS:
        raise ValueError("Unknown tool")
    if not isinstance(arguments, dict):
        raise ValueError("Tool arguments must be an object")
    return name, arguments

# Validate each tool's fields and user permissions before execution.
PY,
        'quiz' => [
            'question' => 'What should an application do with a model-proposed write action?',
            'a' => 'Execute it immediately if the JSON is valid',
            'b' => 'Validate permissions and request approval before the side effect',
            'c' => 'Convert it into arbitrary SQL',
            'correct' => 'b',
            'explanation' => 'Valid structure is not sufficient authorization. Check permissions and get human confirmation for consequential changes.',
        ],
    ],
    [
        'slug' => 'prepare-mcp-for-production',
        'topic' => 'Safety and operations',
        'title' => 'Prepare an MCP Project for Production',
        'summary' => 'Add least privilege, reliable limits, safe logging, and a clear deployment boundary.',
        'body' => <<<'TEXT'
Before connecting an MCP server to a real workflow, decide exactly which data and actions it needs. Give it the smallest useful permissions, keep credentials in server-side secret storage, and authenticate remote connections. Never place API keys in prompts or tool descriptions.

Set timeouts on network and database operations. Bound response sizes and agent steps so a bad request cannot consume unlimited resources. Return a clear, minimal error instead of leaking stack traces, private records, or credentials.

Ask for approval before consequential or irreversible actions. Keep an audit record with the actor, tool, validated arguments (with secrets removed), and result. Review server dependencies and updates, and remove tools that are no longer needed.

For stdio servers, reserve stdout for protocol traffic and send diagnostics to stderr. For HTTP servers, use a deliberate authentication and transport configuration. MCP provides a common interface; your deployment still owns identity, access control, and operational safety.
TEXT,
        'code_sample' => <<<'PY'
import logging

logger = logging.getLogger(__name__)
MAX_AGENT_STEPS = 4
TOOL_TIMEOUT_SECONDS = 10

def require_confirmation(action: str, approved: bool) -> None:
    if not approved:
        logger.info("Action needs user approval: %s", action)
        raise PermissionError("Confirm this action before continuing")

# Store credentials outside source code and model-visible messages.
PY,
        'quiz' => [
            'question' => 'Where should an MCP server keep its API credentials?',
            'a' => 'In a prompt so the model can reuse them',
            'b' => 'In server-side secret storage outside source code',
            'c' => 'In a tool name so it is easy to discover',
            'correct' => 'b',
            'explanation' => 'Credentials belong in protected server-side secret storage and should never be exposed to prompts or source control.',
        ],
    ],
];

$database = db();
$database->beginTransaction();

try {
    $existing = $database->prepare('SELECT id FROM courses WHERE slug = :slug LIMIT 1');
    $existing->execute(['slug' => $course['slug']]);
    if ($existing->fetch()) {
        throw new RuntimeException('The course slug already exists; no existing content was changed.');
    }

    $insertCourse = $database->prepare(
        'INSERT INTO courses (slug, title, category, description, level, is_published)
         VALUES (:slug, :title, :category, :description, :level, 1)'
    );
    $insertCourse->execute($course);
    $courseId = (int) $database->lastInsertId();

    $insertLesson = $database->prepare(
        'INSERT INTO lessons (course_id, slug, topic, title, summary, body, code_sample, position, is_published)
         VALUES (:course_id, :slug, :topic, :title, :summary, :body, :code_sample, :position, 1)'
    );
    $insertQuiz = $database->prepare(
        'INSERT INTO quiz_questions (lesson_id, question, option_a, option_b, option_c, correct_option, explanation, position)
         VALUES (:lesson_id, :question, :option_a, :option_b, :option_c, :correct_option, :explanation, 1)'
    );

    foreach ($lessons as $index => $lesson) {
        $quiz = $lesson['quiz'];
        unset($lesson['quiz']);
        $insertLesson->execute([
            'course_id' => $courseId,
            'slug' => $lesson['slug'],
            'topic' => $lesson['topic'],
            'title' => $lesson['title'],
            'summary' => $lesson['summary'],
            'body' => $lesson['body'],
            'code_sample' => $lesson['code_sample'],
            'position' => $index + 1,
        ]);
        $insertQuiz->execute([
            'lesson_id' => (int) $database->lastInsertId(),
            'question' => $quiz['question'],
            'option_a' => $quiz['a'],
            'option_b' => $quiz['b'],
            'option_c' => $quiz['c'],
            'correct_option' => $quiz['correct'],
            'explanation' => $quiz['explanation'],
        ]);
    }

    $database->commit();
    fwrite(STDOUT, "Published course: {$course['title']} (ID {$courseId}); " . count($lessons) . " lessons and quizzes.\n");
} catch (Throwable $exception) {
    if ($database->inTransaction()) {
        $database->rollBack();
    }
    fwrite(STDERR, $exception->getMessage() . "\n");
    exit(1);
}
