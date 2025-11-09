# DocFlow System Documentation for Claude

## System Overview
DocFlow is a comprehensive document workflow management system built with Laravel 12.37.0 and PHP 8.3.26. The system implements Symfony Workflow component for document state management and includes a complete administrative interface.

## Technology Stack

### Core Framework
- **Laravel**: 12.37.0 (Latest version with enhanced features)
- **PHP**: 8.3.26 (Modern PHP with performance improvements)
- **Database**: MySQL/MariaDB with Laravel Eloquent ORM
- **Frontend**: Blade templating with Tailwind CSS

### Key Dependencies
- **Symfony Workflow**: Document state management and transitions
- **Spatie Laravel-Permission**: Role-based access control
- **Spatie Laravel-ActivityLog**: Comprehensive system logging
- **Spatie Laravel-MediaLibrary**: File attachments and media management

## Database Architecture

### Core Models and Relationships

#### Users (`users` table)
```php
- id (primary key)
- name
- email 
- password
- department_id (foreign key to departments)
- email_verified_at
- remember_token
- created_at, updated_at
```

**Relationships:**
- `belongsTo(Department::class)`
- `hasMany(Document::class, 'author_id')`
- Uses Spatie Permission traits for roles/permissions

#### Departments (`departments` table)
```php
- id (primary key)
- name
- description
- created_at, updated_at
```

**Relationships:**
- `hasMany(User::class)`
- `hasMany(Document::class)`

#### Documents (`documents` table)
```php
- id (primary key)
- title
- content (text)
- status (varchar) - Current workflow state
- workflow_id (foreign key to workflows)
- author_id (foreign key to users)
- department_id (foreign key to departments)
- created_at, updated_at
```

**Relationships:**
- `belongsTo(Workflow::class)`
- `belongsTo(User::class, 'author_id')`
- `belongsTo(Department::class)`
- `hasMany(Task::class)`
- Uses Spatie MediaLibrary for file attachments

#### Workflows (`workflows` table)
```php
- id (primary key)
- name
- description
- states (JSON) - Array of workflow states
- transitions (JSON) - Array of workflow transitions
- is_active (boolean)
- created_at, updated_at
```

**JSON Structure for states:**
```json
[
    "draft",
    "review", 
    "approved",
    "published",
    "archived",
    "rejected"
]
```

**JSON Structure for transitions:**
```json
[
    {
        "name": "submit_for_review",
        "from": ["draft"],
        "to": ["review"]
    },
    {
        "name": "approve",
        "from": ["review"],
        "to": ["approved"]
    }
]
```

**Relationships:**
- `hasMany(Document::class)`

#### Tasks (`tasks` table)
```php
- id (primary key)
- title (not task_title - this was corrected)
- description
- document_id (foreign key to documents)
- assigned_to (foreign key to users)
- status (enum: pending, in_progress, completed)
- due_date
- created_at, updated_at
```

**Relationships:**
- `belongsTo(Document::class)`
- `belongsTo(User::class, 'assigned_to')`

## Core Services

### WorkflowService (`app/Services/WorkflowService.php`)

**Purpose**: Manages document workflow operations and transitions

**Key Methods:**

1. **`availableTransitions(Document $document, User $user)`**
   - Returns array of available workflow transitions for a user
   - Integrates Symfony Workflow with custom authorization
   - Includes comprehensive logging for debugging

2. **`canUserPerformTransition(User $user, Document $document, string $transitionName)`**
   - **CRITICAL**: Rewritten to use permission-based authorization
   - Checks: Admin role, document ownership, department membership
   - **FIXED**: Previously tried to use non-existent `workflow->steps()` 
   - Now uses proper role/permission system

3. **`performTransition(Document $document, string $transitionName, User $user)`**
   - Executes workflow transition with validation
   - Updates document status via Symfony Workflow
   - Logs all transition activities

**Authorization Logic:**
```php
// Admin users can perform any transition
if ($user->hasRole('Admin')) {
    return true;
}

// Document authors can perform transitions on their documents
if ($document->author_id === $user->id) {
    return true;
}

// Users from same department can perform transitions
if ($document->department_id === $user->department_id) {
    return true;
}

return false;
```

## Symfony Workflow Integration

### MethodMarkingStore Configuration
The Document model implements required methods for Symfony Workflow:

```php
public function getStatus(): string
{
    activity()->log('Document getStatus() called')->withProperties([
        'document_id' => $this->id,
        'current_status' => $this->status ?? 'draft'
    ]);
    
    return $this->status ?? 'draft';
}

public function setStatus(string $status): void
{
    activity()->log('Document setStatus() called')->withProperties([
        'document_id' => $this->id,
        'old_status' => $this->status,
        'new_status' => $status
    ]);
    
    $this->status = $status;
}
```

### Workflow Definition Creation
```php
// Create Symfony workflow from database configuration
$places = $workflow->states;
$transitions = [];

foreach ($workflow->transitions as $transitionData) {
    $transitions[] = new Transition(
        $transitionData['name'],
        $transitionData['from'],
        $transitionData['to']
    );
}

$definition = new Definition($places, $transitions);
$markingStore = new MethodMarkingStore(true, 'status');
$symfonyWorkflow = new SymfonyWorkflow($definition, $markingStore);
```

## Administrative Interface

### Workflow Management (`resources/views/admin/workflows/`)

**Features:**
- Complete CRUD operations for workflows
- Dynamic form building for states and transitions
- Real-time validation and preview
- Workflow visualization and testing

**Key Files:**
1. **`index.blade.php`** - Workflow listing with search/filter
2. **`create.blade.php`** - New workflow creation with dynamic forms
3. **`edit.blade.php`** - Workflow editing with state preservation
4. **`show.blade.php`** - Workflow details and document associations
5. **`history.blade.php`** - Workflow change history and audit trail

**JavaScript Features:**
- Dynamic state/transition management
- Real-time form validation
- AJAX form submissions
- Interactive workflow diagrams

### Navigation Structure
```php
// Admin navigation includes:
- Dashboard
- Documents (CRUD, workflow assignment)
- Workflows (CRUD, state management) 
- Users (role assignment, department management)
- Departments (organizational structure)
- Tasks (assignment, tracking, completion)
```

## Permission System

### Roles and Permissions (Spatie Laravel-Permission)

**Default Roles:**
- **Admin**: Full system access
- **Manager**: Department-level management
- **User**: Basic document operations

**Key Permissions:**
- `create documents`
- `edit documents` 
- `delete documents`
- `manage workflows`
- `assign tasks`
- `view reports`

### Authorization Flow
1. **User Authentication**: Laravel's built-in authentication
2. **Role Check**: Spatie Permission role verification
3. **Resource Authorization**: Policy-based access control
4. **Workflow Transitions**: Custom WorkflowService authorization

## File Structure

### Models (`app/Models/`)
```
Document.php     - Core document model with Symfony Workflow integration
User.php         - User model with roles/permissions
Department.php   - Organizational structure
Workflow.php     - Workflow configuration and state management
Task.php         - Task assignment and tracking
```

### Controllers (`app/Http/Controllers/`)
```
Admin/WorkflowController.php - Workflow management interface
DocumentController.php       - Document CRUD operations
TaskController.php          - Task management
UserController.php          - User administration
```

### Services (`app/Services/`)
```
WorkflowService.php - Core workflow business logic
DocumentService.php - Document operations and file management
TaskService.php     - Task assignment and notification
```

## Recent Fixes and Known Issues

### Resolved Issues ✅

1. **Symfony Workflow MethodMarkingStore Error**
   - **Problem**: "Cannot store marking: class should have getStatus() method"
   - **Solution**: Added required getStatus()/setStatus() methods to Document model
   - **Status**: ✅ Fixed

2. **Empty Available Transitions**
   - **Problem**: WorkflowService.availableTransitions() returned empty array
   - **Root Cause**: canUserPerformTransition() referenced non-existent workflow->steps()
   - **Solution**: Rewrote authorization logic to use role/permission system
   - **Status**: ✅ Fixed

3. **Task Model Field Naming**
   - **Problem**: Migration used 'task_title' but model expected 'title'
   - **Solution**: Corrected migration to use 'title' field
   - **Status**: ✅ Fixed

### Testing Status

**Completed Tests:**
- ✅ Document model Symfony Workflow integration
- ✅ Workflow data loading and integrity
- ✅ Admin interface functionality
- ✅ User authentication and roles

**Pending Validation:**
- 🔄 Complete workflow transition cycle testing
- 🔄 End-to-end document lifecycle validation
- 🔄 Multi-user workflow scenarios

## Development Workflow

### Laravel Tinker Testing Commands
```php
// Test basic functionality
$document = Document::find(1);
$user = User::find(1);
$workflowService = app(App\Services\WorkflowService::class);

// Test workflow integration
$document->getStatus();
$workflow = $document->workflow;
$transitions = $workflowService->availableTransitions($document, $user);

// Test authorization
foreach ($workflow->transitions as $transition) {
    $canPerform = $workflowService->canUserPerformTransition($user, $document, $transition['name']);
    echo "Can perform {$transition['name']}: " . ($canPerform ? 'Yes' : 'No') . "\n";
}
```

### Debugging Tools
- **Spatie ActivityLog**: Comprehensive system logging
- **Laravel Telescope**: Application debugging (if installed)
- **Laravel Tinker**: Interactive testing environment
- **Database Query Log**: SQL query analysis

## Configuration Files

### Important Configuration
- **`config/workflow.php`**: Symfony Workflow configuration
- **`config/permission.php`**: Spatie Permission settings
- **`config/media-library.php`**: File upload configuration
- **`config/activitylog.php`**: System logging configuration

### Environment Variables
```env
# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=docflow
DB_USERNAME=root
DB_PASSWORD=

# File Storage
FILESYSTEM_DISK=local
MEDIA_DISK=public

# Logging
LOG_CHANNEL=stack
LOG_LEVEL=debug
```

## API Endpoints (if applicable)

### RESTful Routes
```php
// Document routes
GET    /documents           - List documents
POST   /documents           - Create document  
GET    /documents/{id}      - Show document
PUT    /documents/{id}      - Update document
DELETE /documents/{id}      - Delete document

// Workflow routes  
GET    /workflows           - List workflows
POST   /workflows           - Create workflow
PUT    /workflows/{id}      - Update workflow
POST   /documents/{id}/transition/{name} - Execute transition
```

## Security Considerations

### Data Protection
- **CSRF Protection**: Laravel's built-in CSRF tokens
- **SQL Injection Prevention**: Eloquent ORM parameterized queries
- **XSS Prevention**: Blade template escaping
- **File Upload Security**: Validated file types and sizes

### Access Control
- **Authentication**: Laravel Sanctum/Passport for API access
- **Authorization**: Policy-based resource protection
- **Role-based Access**: Spatie Permission integration
- **Workflow Security**: Custom authorization in WorkflowService

## Maintenance and Monitoring

### Regular Tasks
- **Log Rotation**: Clean activity logs periodically
- **Database Optimization**: Index maintenance and query optimization
- **File Cleanup**: Remove orphaned media files
- **Cache Management**: Clear application caches regularly

### Performance Monitoring
- **Query Performance**: Monitor slow database queries
- **Memory Usage**: Track PHP memory consumption
- **File Storage**: Monitor disk space usage
- **User Activity**: Track system usage patterns

## Troubleshooting Guide

### Common Issues

1. **Workflow Transitions Not Available**
   - Check user permissions and roles
   - Verify document workflow assignment
   - Review WorkflowService authorization logic

2. **File Upload Failures**
   - Check filesystem permissions
   - Verify storage disk configuration
   - Review file size and type restrictions

3. **Permission Denied Errors**
   - Verify user role assignments
   - Check policy authorizations
   - Review middleware configurations

### Debugging Steps
1. Check Laravel logs (`storage/logs/laravel.log`)
2. Review activity logs via Spatie ActivityLog
3. Use Laravel Tinker for interactive debugging
4. Enable query logging for database issues
5. Check file permissions and ownership

## Future Enhancements

### Planned Features
- **Email Notifications**: Workflow transition notifications
- **Advanced Reporting**: Workflow analytics and metrics
- **API Documentation**: Comprehensive API documentation
- **Mobile Interface**: Responsive design improvements
- **Integration APIs**: Third-party system integrations

### Technical Debt
- **Test Coverage**: Expand automated test suite
- **Code Documentation**: Improve inline documentation
- **Performance Optimization**: Database query optimization
- **Security Audit**: Regular security assessments

---

**Last Updated**: November 9, 2025
**System Version**: Laravel 12.37.0 / PHP 8.3.26
**Documentation Version**: 1.0

This documentation provides a comprehensive overview of the DocFlow system for Claude AI assistant. Use this information to understand the system architecture, troubleshoot issues, and implement new features effectively.