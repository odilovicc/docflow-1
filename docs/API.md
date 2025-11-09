# DocsFlow API Documentation

## Authentication

DocsFlow API uses Laravel Sanctum for authentication. All API requests must include authentication credentials.

### Authentication Methods

1. **Session-based (Web)**: Automatic for web interface users
2. **Token-based (API)**: Using Bearer tokens for external applications

### Getting API Token

```php
// For authenticated users
$token = $user->createToken('api-token')->plainTextToken;
```

## Base URL

```
https://yourdomain.com/api/v1
```

## Response Format

All API responses follow a consistent JSON format:

```json
{
    "success": true,
    "data": {
        // Response data
    },
    "message": "Operation completed successfully",
    "meta": {
        "timestamp": "2024-11-09T10:30:00Z",
        "version": "1.0.0"
    }
}
```

## Error Responses

```json
{
    "success": false,
    "error": {
        "code": "VALIDATION_ERROR",
        "message": "The given data was invalid.",
        "details": {
            "field": ["Error message"]
        }
    },
    "meta": {
        "timestamp": "2024-11-09T10:30:00Z"
    }
}
```

## Rate Limiting

- **General API**: 60 requests per minute per user
- **Search API**: 30 requests per minute per user
- **Upload API**: 10 requests per minute per user

## Documents API

### List Documents

```http
GET /api/v1/documents
```

**Parameters:**
- `page` (int): Page number (default: 1)
- `limit` (int): Items per page (default: 15, max: 100)
- `department_id` (int): Filter by department
- `category_id` (int): Filter by category
- `status` (string): Filter by status
- `search` (string): Search in title and content

**Response:**
```json
{
    "success": true,
    "data": {
        "documents": [
            {
                "id": 1,
                "title": "Sample Document",
                "content": "Document content...",
                "status": "pending",
                "department": {
                    "id": 1,
                    "name": "HR"
                },
                "category": {
                    "id": 1,
                    "name": "Policy"
                },
                "creator": {
                    "id": 1,
                    "name": "John Doe"
                },
                "created_at": "2024-11-09T10:30:00Z",
                "updated_at": "2024-11-09T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 5,
            "total_items": 75,
            "items_per_page": 15
        }
    }
}
```

### Get Document

```http
GET /api/v1/documents/{id}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "document": {
            "id": 1,
            "title": "Sample Document",
            "content": "Full document content...",
            "status": "pending",
            "workflow_data": {
                "current_step": "manager_approval",
                "steps_completed": ["created", "submitted"]
            },
            "attachments": [
                {
                    "id": 1,
                    "filename": "attachment.pdf",
                    "size": 1024576,
                    "mime_type": "application/pdf",
                    "url": "https://yourdomain.com/storage/documents/1/attachment.pdf"
                }
            ],
            "tasks": [
                {
                    "id": 1,
                    "title": "Review Document",
                    "status": "pending",
                    "assignee": {
                        "id": 2,
                        "name": "Manager"
                    }
                }
            ],
            "department": {
                "id": 1,
                "name": "HR"
            },
            "category": {
                "id": 1,
                "name": "Policy"
            },
            "creator": {
                "id": 1,
                "name": "John Doe"
            },
            "created_at": "2024-11-09T10:30:00Z",
            "updated_at": "2024-11-09T10:30:00Z"
        }
    }
}
```

### Create Document

```http
POST /api/v1/documents
```

**Request Body:**
```json
{
    "title": "New Document",
    "content": "Document content...",
    "department_id": 1,
    "category_id": 1,
    "attachments": ["file1.pdf", "file2.docx"]
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "document": {
            "id": 2,
            "title": "New Document",
            "status": "draft",
            // ... other fields
        }
    },
    "message": "Document created successfully"
}
```

### Update Document

```http
PUT /api/v1/documents/{id}
```

**Request Body:**
```json
{
    "title": "Updated Document Title",
    "content": "Updated content..."
}
```

### Submit Document for Approval

```http
POST /api/v1/documents/{id}/submit
```

**Response:**
```json
{
    "success": true,
    "data": {
        "document": {
            "id": 1,
            "status": "pending",
            "workflow_data": {
                "current_step": "manager_approval"
            }
        },
        "tasks_created": [
            {
                "id": 5,
                "title": "Review Document",
                "assignee_id": 2
            }
        ]
    },
    "message": "Document submitted for approval"
}
```

### Delete Document

```http
DELETE /api/v1/documents/{id}
```

## Tasks API

### List Tasks

```http
GET /api/v1/tasks
```

**Parameters:**
- `page` (int): Page number
- `limit` (int): Items per page
- `status` (string): Filter by status
- `assignee_id` (int): Filter by assignee
- `document_id` (int): Filter by document

**Response:**
```json
{
    "success": true,
    "data": {
        "tasks": [
            {
                "id": 1,
                "title": "Review Document",
                "description": "Please review and approve/reject",
                "status": "pending",
                "priority": "high",
                "due_date": "2024-11-15T23:59:59Z",
                "document": {
                    "id": 1,
                    "title": "Sample Document"
                },
                "assignee": {
                    "id": 2,
                    "name": "Manager"
                },
                "created_at": "2024-11-09T10:30:00Z",
                "updated_at": "2024-11-09T10:30:00Z"
            }
        ],
        "pagination": {
            "current_page": 1,
            "total_pages": 3,
            "total_items": 42,
            "items_per_page": 15
        }
    }
}
```

### Get Task

```http
GET /api/v1/tasks/{id}
```

### Complete Task

```http
POST /api/v1/tasks/{id}/complete
```

**Request Body:**
```json
{
    "action": "approve",
    "comment": "Approved with minor suggestions",
    "data": {
        "suggestions": ["Fix typo on page 2", "Update conclusion"]
    }
}
```

**Response:**
```json
{
    "success": true,
    "data": {
        "task": {
            "id": 1,
            "status": "completed",
            "completed_at": "2024-11-09T12:00:00Z"
        },
        "document_updated": true,
        "next_tasks_created": [
            {
                "id": 6,
                "title": "Final Approval",
                "assignee_id": 3
            }
        ]
    },
    "message": "Task completed successfully"
}
```

## Search API

### Global Search

```http
GET /api/v1/search/global
```

**Parameters:**
- `q` (string, required): Search query
- `limit` (int): Max results (default: 10, max: 50)
- `type` (string): Filter by type (documents, tasks, users)
- `status` (string): Filter by status

**Response:**
```json
{
    "success": true,
    "data": {
        "results": {
            "documents": [
                {
                    "id": 1,
                    "title": "Sample Document",
                    "excerpt": "...highlighted text...",
                    "type": "document",
                    "score": 0.95
                }
            ],
            "tasks": [
                {
                    "id": 1,
                    "title": "Review Document",
                    "excerpt": "...highlighted text...",
                    "type": "task",
                    "score": 0.87
                }
            ]
        },
        "total_results": 15,
        "search_time_ms": 42
    }
}
```

### Autocomplete

```http
GET /api/v1/search/autocomplete
```

**Parameters:**
- `q` (string, required): Search query
- `type` (string): Filter by type
- `limit` (int): Max suggestions (default: 5)

**Response:**
```json
{
    "success": true,
    "data": {
        "suggestions": [
            "document management",
            "document approval",
            "document workflow"
        ]
    }
}
```

## Users API

### List Users

```http
GET /api/v1/users
```

**Parameters:**
- `page` (int): Page number
- `limit` (int): Items per page
- `department_id` (int): Filter by department
- `role` (string): Filter by role

### Get User Profile

```http
GET /api/v1/users/profile
```

**Response:**
```json
{
    "success": true,
    "data": {
        "user": {
            "id": 1,
            "name": "John Doe",
            "email": "john@example.com",
            "department": {
                "id": 1,
                "name": "HR"
            },
            "roles": [
                {
                    "id": 1,
                    "name": "employee"
                }
            ],
            "permissions": [
                "view_documents",
                "create_documents"
            ],
            "stats": {
                "documents_created": 15,
                "tasks_completed": 28,
                "pending_tasks": 3
            }
        }
    }
}
```

### Update Profile

```http
PUT /api/v1/users/profile
```

## File Upload API

### Upload File

```http
POST /api/v1/files/upload
```

**Request:** Multipart form data
- `file`: File to upload
- `type`: File type (document_attachment, profile_picture)
- `document_id`: Associated document ID (if applicable)

**Response:**
```json
{
    "success": true,
    "data": {
        "file": {
            "id": "abc123",
            "filename": "document.pdf",
            "size": 1024576,
            "mime_type": "application/pdf",
            "url": "https://yourdomain.com/storage/files/abc123.pdf"
        }
    },
    "message": "File uploaded successfully"
}
```

## Notifications API

### List Notifications

```http
GET /api/v1/notifications
```

**Response:**
```json
{
    "success": true,
    "data": {
        "notifications": [
            {
                "id": 1,
                "type": "task_assigned",
                "title": "New Task Assigned",
                "message": "You have been assigned a new task: Review Document",
                "data": {
                    "task_id": 5,
                    "document_id": 1
                },
                "read": false,
                "created_at": "2024-11-09T10:30:00Z"
            }
        ],
        "unread_count": 3
    }
}
```

### Mark as Read

```http
POST /api/v1/notifications/{id}/read
```

### Mark All as Read

```http
POST /api/v1/notifications/read-all
```

## Admin API

### System Statistics

```http
GET /api/v1/admin/stats
```

**Response:**
```json
{
    "success": true,
    "data": {
        "stats": {
            "documents": {
                "total": 150,
                "pending": 25,
                "approved": 100,
                "rejected": 15,
                "draft": 10
            },
            "tasks": {
                "total": 200,
                "pending": 45,
                "completed": 140,
                "overdue": 15
            },
            "users": {
                "total": 50,
                "active": 45,
                "departments": 8
            },
            "performance": {
                "avg_approval_time_hours": 24,
                "cache_hit_rate": 85.2,
                "system_load": 0.65
            }
        }
    }
}
```

### Cache Management

```http
GET /api/v1/admin/cache/stats
```

```http
POST /api/v1/admin/cache/clear
```

```http
POST /api/v1/admin/cache/warm
```

## Error Codes

| Code | Description |
|------|-------------|
| `VALIDATION_ERROR` | Request validation failed |
| `AUTHENTICATION_REQUIRED` | Authentication credentials missing |
| `AUTHORIZATION_FAILED` | Insufficient permissions |
| `RESOURCE_NOT_FOUND` | Requested resource not found |
| `RATE_LIMIT_EXCEEDED` | Too many requests |
| `SERVER_ERROR` | Internal server error |
| `MAINTENANCE_MODE` | System in maintenance mode |

## SDKs and Examples

### JavaScript/TypeScript

```javascript
class DocsFlowAPI {
    constructor(baseUrl, token) {
        this.baseUrl = baseUrl;
        this.token = token;
    }
    
    async request(endpoint, options = {}) {
        const url = `${this.baseUrl}/api/v1${endpoint}`;
        const headers = {
            'Authorization': `Bearer ${this.token}`,
            'Content-Type': 'application/json',
            ...options.headers
        };
        
        const response = await fetch(url, {
            ...options,
            headers
        });
        
        const data = await response.json();
        
        if (!data.success) {
            throw new Error(data.error.message);
        }
        
        return data.data;
    }
    
    async getDocuments(params = {}) {
        const query = new URLSearchParams(params).toString();
        return this.request(`/documents?${query}`);
    }
    
    async createDocument(documentData) {
        return this.request('/documents', {
            method: 'POST',
            body: JSON.stringify(documentData)
        });
    }
}

// Usage
const api = new DocsFlowAPI('https://yourdomain.com', 'your-api-token');
const documents = await api.getDocuments({ page: 1, limit: 10 });
```

### PHP

```php
class DocsFlowAPI {
    private $baseUrl;
    private $token;
    
    public function __construct($baseUrl, $token) {
        $this->baseUrl = rtrim($baseUrl, '/');
        $this->token = $token;
    }
    
    private function request($endpoint, $options = []) {
        $url = $this->baseUrl . '/api/v1' . $endpoint;
        
        $headers = [
            'Authorization: Bearer ' . $this->token,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        
        if (isset($options['method']) && $options['method'] === 'POST') {
            curl_setopt($ch, CURLOPT_POST, true);
            if (isset($options['body'])) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, $options['body']);
            }
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        $data = json_decode($response, true);
        
        if ($httpCode >= 400 || !$data['success']) {
            throw new Exception($data['error']['message'] ?? 'API Error');
        }
        
        return $data['data'];
    }
    
    public function getDocuments($params = []) {
        $query = http_build_query($params);
        return $this->request('/documents?' . $query);
    }
    
    public function createDocument($documentData) {
        return $this->request('/documents', [
            'method' => 'POST',
            'body' => json_encode($documentData)
        ]);
    }
}

// Usage
$api = new DocsFlowAPI('https://yourdomain.com', 'your-api-token');
$documents = $api->getDocuments(['page' => 1, 'limit' => 10]);
```

## Webhook Support

DocsFlow can send webhooks for important events:

### Webhook Events

- `document.created`
- `document.submitted`
- `document.approved`
- `document.rejected`
- `task.created`
- `task.completed`
- `user.registered`

### Webhook Configuration

Configure webhooks in your `.env` file:

```env
WEBHOOK_ENABLED=true
WEBHOOK_URL=https://your-app.com/webhooks/docflow
WEBHOOK_SECRET=your-webhook-secret
WEBHOOK_EVENTS=document.created,document.approved,task.completed
```

### Webhook Payload Example

```json
{
    "event": "document.approved",
    "timestamp": "2024-11-09T12:00:00Z",
    "data": {
        "document": {
            "id": 1,
            "title": "Sample Document",
            "status": "approved"
        },
        "approved_by": {
            "id": 2,
            "name": "Manager"
        }
    },
    "signature": "sha256=abc123..."
}
```

## Support

- **Documentation**: https://docs.yourdomain.com
- **API Status**: https://status.yourdomain.com
- **Support Email**: api-support@yourdomain.com