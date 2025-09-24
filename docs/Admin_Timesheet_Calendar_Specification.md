# Admin Timesheet Calendar Feature Specification

## Overview
A comprehensive admin interface for managing and monitoring all employee timesheets through an intuitive calendar interface with multi-view capabilities, status indicators, and administrative controls.

## Feature Requirements

### Core Functionality
1. **Global Timesheet View** - Admin can see all employee timesheets in one interface
2. **Multi-View Calendar** - Daily, Weekly, Monthly views with seamless switching
3. **Status Indicators** - Visual indicators for pending approvals, missing entries, overtime
4. **Administrative Controls** - Edit, approve, reject, and manage timesheet entries
5. **Filtering & Search** - Filter by employee, department, status, date range
6. **Bulk Operations** - Approve/reject multiple entries at once

## UI/UX Design Recommendations

### 1. Calendar Layout Structure

```
┌─────────────────────────────────────────────────────────────────────┐
│ Admin Timesheet Calendar                                    [Export] │
├─────────────────────────────────────────────────────────────────────┤
│ Filters: [All Users ▼] [Department ▼] [Status ▼] [Date Range]      │
├─────────────────────────────────────────────────────────────────────┤
│ View: ○ Daily ○ Weekly ● Monthly     [◀ Sep 2025 ▶]              │
├─────────────────────────────────────────────────────────────────────┤
│                           Calendar Grid                              │
│ ┌─────┬─────┬─────┬─────┬─────┬─────┬─────┐                      │
│ │ Sun │ Mon │ Tue │ Wed │ Thu │ Fri │ Sat │                      │
│ ├─────┼─────┼─────┼─────┼─────┼─────┼─────┤                      │
│ │  1  │  2  │  3  │  4  │  5  │  6  │  7  │                      │
│ │ 🟢5 │ 🟡3 │ ⭕2 │ 🟢4 │ 🔴1 │     │     │                      │
│ └─────┴─────┴─────┴─────┴─────┴─────┴─────┘                      │
├─────────────────────────────────────────────────────────────────────┤
│ Legend: 🟢 Approved 🟡 Pending ⭕ Missing 🔴 Overtime ⚪ Normal    │
└─────────────────────────────────────────────────────────────────────┘
```

### 2. Daily View Details

```
┌─────────────────────────────────────────────────────────────────────┐
│ Monday, September 15, 2025                              [< Today >] │
├─────────────────────────────────────────────────────────────────────┤
│ Employee Name        │ Hours │ Status      │ Actions               │
├─────────────────────────────────────────────────────────────────────┤
│ John Doe            │ 8.5h  │ 🟡 Pending  │ [View] [Edit] [✓] [✗] │
│ Jane Smith          │ 7.0h  │ 🟢 Approved │ [View] [Edit]         │
│ Mike Johnson        │ 9.5h  │ 🔴 Overtime │ [View] [Edit] [✓] [✗] │
│ Sarah Wilson        │ --    │ ⭕ Missing  │ [Add Entry]           │
└─────────────────────────────────────────────────────────────────────┘
```

### 3. Color Coding & Status Indicators

| Status | Color | Icon | Description |
|--------|-------|------|-------------|
| **Approved** | 🟢 Green | ✓ | Timesheet approved by admin |
| **Pending** | 🟡 Yellow | ⏳ | Waiting for admin approval |
| **Missing** | ⭕ Red Circle | ❌ | No timesheet entry for the day |
| **Overtime** | 🔴 Red | ⚠️ | Hours exceed daily/weekly limit |
| **Normal** | ⚪ Gray | - | Standard working hours |
| **Rejected** | 🔵 Blue | ✗ | Timesheet rejected, needs revision |

### 4. Interactive Elements

#### Calendar Cell Actions
- **Click**: View day details
- **Double-click**: Quick edit mode
- **Right-click**: Context menu (Approve all, Reject all, Add missing)
- **Hover**: Show summary tooltip

#### Bulk Actions Panel
```
┌─────────────────────────────────────────────────────────────────────┐
│ Selected: 5 entries  [Select All] [Clear Selection]                │
│ Actions: [✓ Approve All] [✗ Reject All] [📧 Send Reminder]        │
└─────────────────────────────────────────────────────────────────────┘
```

## Data Structure Requirements

### 1. Enhanced Timesheet Model

```sql
-- Enhanced timesheets table
ALTER TABLE timesheets ADD COLUMN IF NOT EXISTS:
    status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    approved_by INT NULL REFERENCES users(id),
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    is_overtime BOOLEAN DEFAULT FALSE,
    expected_hours DECIMAL(4,2) DEFAULT 8.00,
    break_duration DECIMAL(4,2) DEFAULT 0.00,
    location VARCHAR(255) NULL,
    project_id INT NULL REFERENCES projects(id),
    notes TEXT NULL,
    submitted_at TIMESTAMP NULL,
    last_modified_by INT NULL REFERENCES users(id);
```

### 2. New Supporting Tables

```sql
-- Timesheet approval history
CREATE TABLE timesheet_approvals (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    timesheet_id BIGINT REFERENCES timesheets(id),
    admin_id BIGINT REFERENCES users(id),
    action ENUM('approved', 'rejected', 'requested_changes'),
    reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Timesheet policies
CREATE TABLE timesheet_policies (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    max_daily_hours DECIMAL(4,2) DEFAULT 12.00,
    max_weekly_hours DECIMAL(4,2) DEFAULT 40.00,
    require_approval BOOLEAN DEFAULT TRUE,
    overtime_threshold DECIMAL(4,2) DEFAULT 8.00,
    allow_retroactive_entries BOOLEAN DEFAULT TRUE,
    retroactive_limit_days INT DEFAULT 7,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- User policy assignments
CREATE TABLE user_policies (
    user_id BIGINT REFERENCES users(id),
    policy_id BIGINT REFERENCES timesheet_policies(id),
    PRIMARY KEY (user_id, policy_id)
);
```

### 3. API Endpoints Structure

```
GET    /admin/timesheet-calendar/data              # Calendar data
GET    /admin/timesheet-calendar/day/{date}        # Day details
POST   /admin/timesheet-calendar/approve/{id}      # Approve entry
POST   /admin/timesheet-calendar/reject/{id}       # Reject entry
POST   /admin/timesheet-calendar/bulk-approve      # Bulk approve
POST   /admin/timesheet-calendar/bulk-reject       # Bulk reject
PUT    /admin/timesheet-calendar/edit/{id}         # Edit entry
POST   /admin/timesheet-calendar/add-missing       # Add missing entry
GET    /admin/timesheet-calendar/stats             # Dashboard stats
GET    /admin/timesheet-calendar/export            # Export data
```

## Permissions & Security Logic

### 1. Role-Based Access Control

```php
// Permission levels
const TIMESHEET_PERMISSIONS = [
    'view_all_timesheets' => ['admin', 'manager'],
    'approve_timesheets' => ['admin', 'manager'],
    'edit_any_timesheet' => ['admin'],
    'delete_timesheets' => ['admin'],
    'bulk_operations' => ['admin', 'manager'],
    'export_timesheets' => ['admin', 'manager', 'hr'],
    'manage_policies' => ['admin'],
];
```

### 2. Department-Based Restrictions

```php
// Managers can only see their department's timesheets
class TimesheetCalendarPolicy {
    public function viewTimesheets($user, $targetUser = null) {
        if ($user->hasRole('admin')) {
            return true; // Admin sees all
        }
        
        if ($user->hasRole('manager')) {
            return $targetUser->department_id === $user->department_id;
        }
        
        return false;
    }
}
```

### 3. Approval Workflow

```
Employee → Manager → Admin (if needed)
    ↓         ↓         ↓
 Submit → Approve → Final Approval
           ↓         ↓
        Reject → Request Changes
```

## Technical Implementation Plan

### 1. Frontend Components

#### Vue.js Components Structure
```
AdminTimesheetCalendar/
├── CalendarView.vue              # Main calendar component
├── DayView.vue                  # Daily detailed view
├── WeekView.vue                 # Weekly grid view
├── MonthView.vue                # Monthly overview
├── TimesheetCard.vue            # Individual timesheet card
├── BulkActionsPanel.vue         # Bulk operations
├── FilterBar.vue                # Filtering controls
├── StatusIndicator.vue          # Status badges/icons
├── ApprovalModal.vue            # Approval/rejection modal
└── EditTimesheetModal.vue       # Edit timesheet modal
```

#### Calendar Cell Data Structure
```javascript
const calendarCell = {
    date: '2025-09-15',
    timesheets: [
        {
            id: 1,
            user_id: 5,
            user_name: 'John Doe',
            hours: 8.5,
            status: 'pending',
            is_overtime: false,
            project: 'Web Development',
            location: 'Office'
        }
    ],
    summary: {
        total_hours: 24.5,
        total_employees: 3,
        pending_count: 1,
        approved_count: 2,
        missing_count: 1,
        overtime_count: 0
    }
};
```

### 2. Backend Architecture

#### Controller Methods
```php
class AdminTimesheetCalendarController extends Controller {
    public function getCalendarData(Request $request);
    public function getDayDetails($date);
    public function approveTimesheet($id);
    public function rejectTimesheet(Request $request, $id);
    public function bulkApprove(Request $request);
    public function bulkReject(Request $request);
    public function editTimesheet(Request $request, $id);
    public function addMissingEntry(Request $request);
    public function getStatistics(Request $request);
    public function exportData(Request $request);
}
```

### 3. Performance Optimizations

#### Database Queries
```sql
-- Optimized calendar data query
SELECT 
    DATE(t.date) as calendar_date,
    COUNT(*) as total_entries,
    SUM(CASE WHEN t.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
    SUM(CASE WHEN t.status = 'approved' THEN 1 ELSE 0 END) as approved_count,
    SUM(CASE WHEN t.is_overtime = 1 THEN 1 ELSE 0 END) as overtime_count,
    SUM(t.hours_worked) as total_hours
FROM timesheets t
WHERE t.date BETWEEN ? AND ?
GROUP BY DATE(t.date)
ORDER BY t.date;
```

#### Caching Strategy
```php
// Cache calendar data for performance
Cache::remember("admin_timesheet_calendar_{$month}_{$year}", 3600, function() {
    return $this->generateCalendarData($month, $year);
});
```

## Advanced Features

### 1. Real-time Updates
- WebSocket integration for live timesheet updates
- Push notifications for new submissions
- Auto-refresh calendar when data changes

### 2. Analytics Dashboard
```
┌─────────────────────────────────────────────────────────────────────┐
│ Timesheet Analytics                                                 │
├─────────────────────────────────────────────────────────────────────┤
│ This Month: 📊                                                     │
│ • Average Response Time: 2.3 hours                                 │
│ • Approval Rate: 94%                                               │
│ • Most Productive Day: Tuesday                                     │
│ • Overtime Frequency: 12%                                         │
└─────────────────────────────────────────────────────────────────────┘
```

### 3. Smart Features
- **Auto-approval** for trusted employees
- **Pattern detection** for unusual timesheet patterns
- **Reminder system** for missing entries
- **Integration** with project management tools

## Mobile Responsiveness

### Mobile Calendar View
```
┌─────────────────────────────┐
│ Sep 2025     [≡] [📅] [⚙️] │
├─────────────────────────────┤
│ Today: 15 entries           │
│ Pending: 3 🟡               │
│ Missing: 1 ⭕               │
├─────────────────────────────┤
│ [Swipe left/right for days] │
│                             │
│ 📱 Optimized for mobile     │
│ • Touch-friendly interface  │
│ • Swipe navigation         │
│ • Quick actions           │
└─────────────────────────────┘
```

This specification provides a comprehensive foundation for implementing an advanced Admin Timesheet Calendar feature that balances functionality with usability while maintaining security and performance standards.
