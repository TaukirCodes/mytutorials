CREATE DATABASE IF NOT EXISTS devdocs CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE devdocs;

CREATE TABLE IF NOT EXISTS admins (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS courses (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    slug VARCHAR(120) NOT NULL UNIQUE,
    title VARCHAR(190) NOT NULL,
    category VARCHAR(100) NOT NULL DEFAULT 'Development',
    description TEXT NOT NULL,
    level VARCHAR(40) NOT NULL DEFAULT 'Beginner',
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS lessons (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    course_id BIGINT UNSIGNED NOT NULL,
    slug VARCHAR(120) NOT NULL,
    topic VARCHAR(190) NOT NULL,
    title VARCHAR(190) NOT NULL,
    summary TEXT NOT NULL,
    body LONGTEXT NOT NULL,
    code_sample LONGTEXT NOT NULL,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    is_published TINYINT(1) NOT NULL DEFAULT 0,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_lesson_course_slug (course_id, slug),
    KEY idx_lesson_published_position (is_published, position),
    CONSTRAINT fk_lessons_course FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS quiz_questions (
    id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    lesson_id BIGINT UNSIGNED NOT NULL,
    question TEXT NOT NULL,
    option_a VARCHAR(500) NOT NULL,
    option_b VARCHAR(500) NOT NULL,
    option_c VARCHAR(500) NOT NULL,
    correct_option ENUM('a', 'b', 'c') NOT NULL,
    explanation TEXT NOT NULL,
    position INT UNSIGNED NOT NULL DEFAULT 0,
    CONSTRAINT fk_quiz_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE
);

INSERT IGNORE INTO courses (slug, title, category, description, level, is_published) VALUES
    ('php', 'PHP 8 & Modern MySQL', 'Backend Architecture', 'Learn server-side execution, REST APIs, and relational database integrations.', 'Beginner', 1),
    ('python', 'Python 3 Advanced', 'Data & Systems', 'Master algorithms, object-oriented concepts, and automated backend scripts.', 'Intermediate', 1),
    ('javascript', 'JavaScript Modern ES6+', 'Frontend Systems', 'Build browser workflows using asynchronous patterns and state operations.', 'Intermediate', 1);

INSERT INTO lessons (course_id, slug, topic, title, summary, body, code_sample, position, is_published)
SELECT courses.id, 'indexed-arrays', 'Arrays', 'Indexed Arrays',
       'Create indexed arrays, read values by position, and iterate safely.',
       'An indexed array stores values under numeric keys. PHP assigns zero-based indexes when values are added without explicit keys. Use count() to determine how many values are available before looping.',
       '<?php
$cars = ["Volvo", "BMW", "Toyota"];

echo "I like " . $cars[0] . ", " . $cars[1] . " and " . $cars[2] . ".";

foreach ($cars as $car) {
    echo $car . "<br>";
}
?>',
       1, 1
FROM courses
WHERE courses.slug = 'php'
  AND NOT EXISTS (
      SELECT 1 FROM lessons WHERE lessons.course_id = courses.id AND lessons.slug = 'indexed-arrays'
  );

INSERT INTO lessons (course_id, slug, topic, title, summary, body, code_sample, position, is_published)
SELECT courses.id, 'python-lists', 'Data Structures', 'Python Lists',
             'Create a list, access values by index, and iterate over its items.',
             'A Python list is an ordered, mutable collection. Indexes start at zero, and for loops provide a clear way to process every item without managing an index manually.',
             'languages = ["Python", "PHP", "JavaScript"]
print(languages[0])

for language in languages:
        print(language)',
             1, 1
FROM courses
WHERE courses.slug = 'python'
    AND NOT EXISTS (
            SELECT 1 FROM lessons WHERE lessons.course_id = courses.id AND lessons.slug = 'python-lists'
    );

INSERT INTO lessons (course_id, slug, topic, title, summary, body, code_sample, position, is_published)
SELECT courses.id, 'modern-javascript-basics', 'ES6+', 'Modern JavaScript Basics',
             'Use const, arrow functions, and template literals in a small example.',
             'Modern JavaScript includes concise syntax that makes common operations easier to read. Use const when a binding will not be reassigned, and use template literals when combining text with values.',
             'const learner = "DevDocs";
const greet = (name) => `Welcome, ${name}!`;

console.log(greet(learner));',
             1, 1
FROM courses
WHERE courses.slug = 'javascript'
    AND NOT EXISTS (
            SELECT 1 FROM lessons WHERE lessons.course_id = courses.id AND lessons.slug = 'modern-javascript-basics'
    );

INSERT INTO quiz_questions (lesson_id, question, option_a, option_b, option_c, correct_option, explanation, position)
SELECT lessons.id,
       'What is the index of the first value in a standard PHP indexed array?',
       '0', '1', '-1', 'a',
       'PHP indexed arrays use zero-based indexes, so the first value is at index 0.',
       1
FROM lessons
WHERE lessons.slug = 'indexed-arrays'
  AND NOT EXISTS (
      SELECT 1 FROM quiz_questions WHERE quiz_questions.lesson_id = lessons.id
  );