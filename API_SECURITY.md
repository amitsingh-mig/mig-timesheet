# API Security Documentation

## Overview
This document outlines the security measures implemented in the Employee Timesheet Management System API.

## Authentication & Authorization

### Authentication Methods
- **Laravel Sanctum**: Token-based authentication for API endpoints
- **Rate Limiting**: Applied to authentication endpoints to prevent brute force attacks
- **Password Security**: Strong password requirements with confirmation

### Authorization Levels
- **Public Routes**: Registration and login only
- **Authenticated Routes**: All other endpoints require valid Sanctum token
- **Role-Based Access**: Admin-only endpoints protected by role middleware

## Security Features

### 1. Rate Limiting
```php
// Authentication endpoints
Route::post('/register', 'register')->middleware('throttle:5,1');
Route::post('/login','login')->middleware('throttle:5,1');
```

### 2. Input Validation
- **Registration**: Name, email, password with confirmation
- **Login**: Email and password validation
- **Password Reset**: Current password verification required
- **All Endpoints**: Comprehensive validation rules

### 3. Role-Based Access Control
```php
// Admin-only routes
Route::middleware(['auth:sanctum', 'role:admin'])->group(function(){
    // Admin functionality
});

// User profile routes
Route::middleware('auth:sanctum')->group(function(){
    // User can access own data
});
```

### 4. Data Protection
- **Password Hashing**: All passwords hashed using Laravel's Hash facade
- **Token Management**: Secure token generation and revocation
- **Data Sanitization**: Input sanitization and validation
- **Error Handling**: Secure error responses without sensitive information

## API Endpoints Security

### Authentication Endpoints
| Endpoint | Method | Security | Rate Limit |
|----------|--------|----------|------------|
| `/api/register` | POST | Public | 5/min |
| `/api/login` | POST | Public | 5/min |
| `/api/logout` | POST | Sanctum | - |
| `/api/reset-password` | POST | Sanctum | - |

### User Management (Admin Only)
| Endpoint | Method | Security | Access |
|----------|--------|----------|--------|
| `/api/user` | GET | Sanctum + Admin | Admin |
| `/api/user/{id}` | GET | Sanctum + Admin | Admin |
| `/api/user/{id}` | PUT | Sanctum + Admin | Admin |
| `/api/user/{id}` | DELETE | Sanctum + Admin | Admin |

### User Profile (Self Access)
| Endpoint | Method | Security | Access |
|----------|--------|----------|--------|
| `/api/profile` | GET | Sanctum | Own Data |
| `/api/profile` | PUT | Sanctum | Own Data |

### Attendance (User + Admin)
| Endpoint | Method | Security | Access |
|----------|--------|----------|--------|
| `/api/attendance/clock-in` | POST | Sanctum | Own Data |
| `/api/attendance/clock-out` | POST | Sanctum | Own Data |
| `/api/attendance/status` | GET | Sanctum | Own Data |
| `/api/attendance/report/{id}` | GET | Sanctum + Admin | Admin |
| `/api/attendance/all-report` | GET | Sanctum + Admin | Admin |

### Enhanced Attendance Sessions
| Endpoint | Method | Security | Access |
|----------|--------|----------|--------|
| `/api/attendance-sessions/clock-in` | POST | Sanctum | Own Data |
| `/api/attendance-sessions/clock-out` | POST | Sanctum | Own Data |
| `/api/attendance-sessions/status` | GET | Sanctum | Own Data |
| `/api/attendance-sessions/work-summary` | POST | Sanctum | Own Data |
| `/api/attendance-sessions/timeline/{userId?}` | GET | Sanctum + Admin | Admin |
| `/api/attendance-sessions/auto-clock-out` | POST | Sanctum + Admin | Admin |

### Timesheet Management
| Endpoint | Method | Security | Access |
|----------|--------|----------|--------|
| `/api/timesheet/enhanced` | GET | Sanctum | Own Data |
| `/api/timesheet/enhanced` | POST | Sanctum | Own Data |
| `/api/timesheet/merge-duplicates` | POST | Sanctum | Own Data |
| `/api/timesheet/inconsistencies` | GET | Sanctum + Admin | Admin |

### Dashboard
| Endpoint | Method | Security | Access |
|----------|--------|----------|--------|
| `/api/dashboard/unified` | GET | Sanctum | Own Data |
| `/api/dashboard/timeline` | GET | Sanctum + Admin | Admin |

## Security Best Practices

### 1. Token Management
- Tokens are automatically revoked on password reset
- Users can logout to revoke all tokens
- Tokens have expiration (configured in Sanctum)

### 2. Input Validation
- All inputs are validated using Laravel's validation rules
- SQL injection prevention through Eloquent ORM
- XSS prevention through proper data handling

### 3. Error Handling
- Generic error messages for authentication failures
- No sensitive information exposed in error responses
- Proper HTTP status codes

### 4. Data Access Control
- Users can only access their own data
- Admin users can access all data
- Role-based middleware enforcement

## Security Headers
The API includes security headers for:
- CORS configuration
- Content-Type validation
- XSS protection
- CSRF protection (for web routes)

## Monitoring & Logging
- Authentication attempts logged
- Failed login attempts tracked
- API access patterns monitored
- Security events recorded

## Production Deployment Security

### Environment Configuration
```env
APP_ENV=production
APP_DEBUG=false
SANCTUM_STATEFUL_DOMAINS=yourdomain.com
SESSION_DRIVER=database
```

### Database Security
- Use prepared statements (Eloquent ORM)
- Regular database backups
- Secure database credentials
- Connection encryption

### Server Security
- HTTPS enforcement
- Proper file permissions
- Regular security updates
- Firewall configuration

## Security Testing

### Recommended Tests
1. **Authentication Testing**
   - Test rate limiting on login/register
   - Test token expiration
   - Test password strength requirements

2. **Authorization Testing**
   - Test role-based access control
   - Test data isolation between users
   - Test admin privilege escalation

3. **Input Validation Testing**
   - Test SQL injection attempts
   - Test XSS payloads
   - Test malformed requests

4. **API Security Testing**
   - Test unauthorized access attempts
   - Test token manipulation
   - Test rate limit bypass attempts

## Incident Response

### Security Incident Procedure
1. **Detection**: Monitor logs for suspicious activity
2. **Assessment**: Evaluate the severity and impact
3. **Containment**: Isolate affected systems
4. **Investigation**: Analyze the incident
5. **Recovery**: Restore normal operations
6. **Documentation**: Record lessons learned

### Contact Information
For security issues, contact the development team immediately.

## Compliance

### Data Protection
- User data is protected according to privacy requirements
- Personal information is not exposed in API responses
- Data retention policies are enforced

### Audit Trail
- All API access is logged
- User actions are tracked
- System changes are recorded

This API security implementation ensures that the Employee Timesheet Management System maintains high security standards while providing necessary functionality for users and administrators.
