<?php

// New mobile endpoints exist behind auth (401 JSON, never 404/HTML):
// directory, finance reports, budget, letters, quiz questions.

$routes = [
    '/api/v1/directory/students',
    '/api/v1/directory/staff',
    '/api/v1/directory/class-sections',
    '/api/v1/directory/subjects',
    '/api/v1/directory/semesters',
    '/api/v1/reports/cash-summary',
    '/api/v1/reports/aging',
    '/api/v1/reports/outstanding',
    '/api/v1/budget/dashboard',
    '/api/v1/letters',
    '/api/v1/letters/templates',
    '/api/v1/lms/quizzes/1/questions',
];

foreach ($routes as $route) {
    test("{$route} is wired (401 JSON without token)", function () use ($route) {
        $response = $this->get($route);

        $response->assertUnauthorized();
        expect($response->headers->get('Content-Type'))->toContain('application/json');
    });
}
