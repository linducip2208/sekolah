<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\View\View;

class ApiDocsController extends Controller
{
    public function index(): View
    {
        return view('api-docs.index');
    }

    public function spec(): JsonResponse
    {
        $baseUrl = rtrim(config('app.url', 'http://localhost'), '/');

        $spec = [
            'openapi' => '3.0.3',
            'info' => [
                'title' => 'Sikad Pro API',
                'version' => '1.0.0',
                'description' => "REST API untuk Sikad Pro — Sistem Manajemen Sekolah multi-tenant.\n\n"
                    . "## Authentication\n\n"
                    . "Login via `POST /api/v1/auth/login` → dapat Bearer token. Sertakan di header:\n\n"
                    . "```\nAuthorization: Bearer {token}\n```\n\n"
                    . "Setiap request otomatis di-scope ke `school_id` user yang login.",
                'contact' => ['name' => 'Sikad Pro Support', 'url' => 'https://whitelabel.co.id'],
                'license' => ['name' => 'Commercial'],
            ],
            'servers' => [
                ['url' => $baseUrl, 'description' => 'Current environment'],
            ],
            'components' => [
                'securitySchemes' => [
                    'bearerAuth' => [
                        'type' => 'http',
                        'scheme' => 'bearer',
                        'bearerFormat' => 'Sanctum Token',
                    ],
                ],
                'schemas' => [
                    'Error' => [
                        'type' => 'object',
                        'properties' => [
                            'message' => ['type' => 'string'],
                            'errors'  => ['type' => 'object'],
                        ],
                    ],
                    'User' => [
                        'type' => 'object',
                        'properties' => [
                            'id'        => ['type' => 'integer'],
                            'name'      => ['type' => 'string'],
                            'email'     => ['type' => 'string'],
                            'phone'     => ['type' => 'string', 'nullable' => true],
                            'role'      => ['type' => 'string'],
                            'school_id' => ['type' => 'integer', 'nullable' => true],
                            'locale'    => ['type' => 'string'],
                            'is_active' => ['type' => 'boolean'],
                        ],
                    ],
                ],
            ],
            'paths' => [
                '/api/v1/health' => [
                    'get' => [
                        'tags' => ['Health'],
                        'summary' => 'Shallow health check',
                        'responses' => [
                            '200' => ['description' => 'Server up'],
                        ],
                    ],
                ],
                '/api/v1/health/deep' => [
                    'get' => [
                        'tags' => ['Health'],
                        'summary' => 'Deep health (DB, cache, queue)',
                        'responses' => ['200' => ['description' => 'All services up']],
                    ],
                ],
                '/api/v1/auth/login' => [
                    'post' => [
                        'tags' => ['Auth'],
                        'summary' => 'Login & obtain bearer token',
                        'requestBody' => [
                            'required' => true,
                            'content' => [
                                'application/json' => [
                                    'schema' => [
                                        'type' => 'object',
                                        'required' => ['email', 'password', 'device_name'],
                                        'properties' => [
                                            'email'       => ['type' => 'string', 'example' => 'admin@sman1demo.sch.id'],
                                            'password'    => ['type' => 'string', 'example' => 'Admin123!'],
                                            'device_name' => ['type' => 'string', 'example' => 'iPhone 15'],
                                        ],
                                    ],
                                ],
                            ],
                        ],
                        'responses' => [
                            '200' => [
                                'description' => 'Login success',
                                'content' => ['application/json' => ['schema' => [
                                    'type' => 'object',
                                    'properties' => [
                                        'token' => ['type' => 'string'],
                                        'user'  => ['$ref' => '#/components/schemas/User'],
                                    ],
                                ]]],
                            ],
                            '422' => ['description' => 'Validation error'],
                        ],
                    ],
                ],
                '/api/v1/auth/logout' => [
                    'post' => [
                        'tags' => ['Auth'],
                        'summary' => 'Revoke current token',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => ['description' => 'Logged out']],
                    ],
                ],
                '/api/v1/me' => [
                    'get' => [
                        'tags' => ['Auth'],
                        'summary' => 'Get authenticated user info',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => ['description' => 'User profile']],
                    ],
                ],
                '/api/v1/students' => [
                    'get' => [
                        'tags' => ['Students'],
                        'summary' => 'List students (school-scoped)',
                        'security' => [['bearerAuth' => []]],
                        'parameters' => [
                            ['name' => 'page', 'in' => 'query', 'schema' => ['type' => 'integer']],
                            ['name' => 'class_section_id', 'in' => 'query', 'schema' => ['type' => 'integer']],
                        ],
                        'responses' => ['200' => ['description' => 'List of students']],
                    ],
                ],
                '/api/v1/attendance' => [
                    'post' => [
                        'tags' => ['Attendance'],
                        'summary' => 'Submit attendance batch',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['201' => ['description' => 'Attendance saved']],
                    ],
                ],
                '/api/v1/fee-invoices' => [
                    'get' => [
                        'tags' => ['Finance'],
                        'summary' => 'List fee invoices for school or student',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => ['description' => 'List of invoices']],
                    ],
                ],
                '/api/v1/lesson-plans' => [
                    'get' => [
                        'tags' => ['Teaching'],
                        'summary' => 'List teacher lesson plans',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => ['description' => 'List']],
                    ],
                ],
                '/api/v1/notices' => [
                    'get' => [
                        'tags' => ['Communication'],
                        'summary' => 'List notices visible to current user',
                        'security' => [['bearerAuth' => []]],
                        'responses' => ['200' => ['description' => 'List']],
                    ],
                ],
                '/api/v1/webhooks/payment/{provider}' => [
                    'post' => [
                        'tags' => ['Webhooks'],
                        'summary' => 'Payment gateway webhook callback',
                        'parameters' => [
                            ['name' => 'provider', 'in' => 'path', 'required' => true, 'schema' => ['type' => 'string'], 'description' => 'Provider slug (e.g. midtrans, xendit, tripay)'],
                        ],
                        'responses' => ['200' => ['description' => 'Webhook processed']],
                    ],
                ],
            ],
            'tags' => [
                ['name' => 'Health', 'description' => 'Server status endpoints'],
                ['name' => 'Auth', 'description' => 'Authentication & user info'],
                ['name' => 'Students', 'description' => 'Student CRUD'],
                ['name' => 'Attendance', 'description' => 'Daily attendance'],
                ['name' => 'Finance', 'description' => 'Fee invoices & payments'],
                ['name' => 'Teaching', 'description' => 'Lesson plans, materials, exams'],
                ['name' => 'Communication', 'description' => 'Notices, chat, notifications'],
                ['name' => 'Webhooks', 'description' => 'Inbound webhooks from payment gateways'],
            ],
        ];

        return response()->json($spec);
    }
}
