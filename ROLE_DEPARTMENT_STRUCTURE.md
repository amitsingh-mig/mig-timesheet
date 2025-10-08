# Role and Department Structure

This document outlines the role and department structure implemented in the MIG-HRM system.

## 🏢 System Overview

The system has a **two-tier role structure** with specific department assignments:

### **Roles**
1. **Admin** - Administrative users with full system access (called "Administrators")
2. **Employee** - Regular users with limited access (called "Employees")

**Important**: Admin users are **NOT** called "employees" - they are "Administrators" or "Admins". Only users with the "employee" role are called "Employees".

### **Departments**

#### **Admin Department**
- **Admin** - Reserved for users with admin role

#### **Employee Departments**
- **Web** - Web Development team
- **Graphic** - Graphic Design team  
- **Editorial** - Editorial team
- **Multimedia** - Multimedia team
- **Sales** - Sales team
- **Marketing** - Marketing team
- **Intern** - Internship program
- **General** - Default department for employees

## 🔧 Implementation Details

### **Database Structure**

#### **Users Table**
```sql
- role_id (foreign key to roles table)
- department (string, nullable)
```

#### **Roles Table**
```sql
- id (primary key)
- name (string: 'admin' or 'employee')
```

### **Model Methods**

#### **User Model**
```php
// Role checking
$user->isAdmin()                    // Check if user is admin
$user->isEmployee()                 // Check if user is employee
$user->hasRole('admin')             // Check specific role

// Department management
$user->getDepartment()              // Get user's department (validated)
$user->belongsToDepartment('Web')   // Check specific department
$user->belongsToAnyDepartment(['Web', 'Graphic']) // Check multiple departments
$user->getValidDepartments()        // Get valid departments for user's role
$user->isValidDepartment('Web')     // Validate department for user's role
```

#### **DepartmentHelper Class**
```php
DepartmentHelper::getAllDepartments()           // Get all valid departments
DepartmentHelper::getEmployeeDepartments()      // Get employee departments only
DepartmentHelper::getAdminDepartment()          // Get admin department
DepartmentHelper::isValidDepartment('Web')      // Validate department
DepartmentHelper::getDisplayName('Web')         // Get display name
DepartmentHelper::getDepartmentColor('Web')     // Get UI color
DepartmentHelper::getDepartmentStats()          // Get department statistics
```

## 🚀 Setup Instructions

### **1. Run Migrations**
```bash
php artisan migrate
```

### **2. Setup Departments**
```bash
php artisan setup:departments
```

### **3. Seed Database (Optional)**
```bash
php artisan db:seed --class=DepartmentSeeder
```

## 📋 Validation Rules

### **User Creation/Update**
- **Admin users**: Automatically assigned to "Admin – Admin" department
- **Employee users**: Must belong to one of the valid employee departments
- **Department validation**: Enforced in CreateUserRequest and UserController

### **Valid Departments by Role**

#### **Admin Role**
- `Admin` (automatically assigned)

#### **Employee Role**
- `Web`
- `Graphic`
- `Editorial`
- `Multimedia`
- `Sales`
- `Marketing`
- `Intern`
- `General` (default)

## 🎯 Usage Examples

### **Creating Users**

#### **Administrator User**
```php
$admin = User::create([
    'name' => 'John Admin',
    'email' => 'admin@company.com',
    'password' => Hash::make('password'),
    'role_id' => $adminRole->id,
    'department' => 'Admin' // Automatically set
]);
```

#### **Employee User**
```php
$employee = User::create([
    'name' => 'Jane Developer',
    'email' => 'jane@company.com',
    'password' => Hash::make('password'),
    'role_id' => $employeeRole->id,
    'department' => 'Web' // Must be valid employee department
]);
```

### **Checking User Access**

#### **Role-based Access**
```php
if ($user->isAdmin()) {
    // Admin-only functionality
}

if ($user->isEmployee()) {
    // Employee functionality
}
```

#### **Department-based Access**
```php
if ($user->belongsToDepartment('Web')) {
    // Web team specific functionality
}

if ($user->belongsToAnyDepartment(['Web', 'Graphic'])) {
    // Creative team functionality
}
```

### **UI Display**
```php
// Get department display name
$displayName = DepartmentHelper::getDisplayName($user->department);
// "Web" -> "Web Development"

// Get department color for badges
$color = DepartmentHelper::getDepartmentColor($user->department);
// "Web" -> "primary"
```

## 🔒 Security Considerations

### **Access Control**
- **Admin users** have full system access
- **Employee users** have limited access based on their role
- **Department validation** prevents invalid department assignments
- **Role-based middleware** enforces access restrictions

### **Data Integrity**
- **Foreign key constraints** ensure valid role assignments
- **Validation rules** prevent invalid department assignments
- **Automatic department assignment** based on role

## 📊 Department Statistics

### **Getting Department Stats**
```php
$stats = DepartmentHelper::getDepartmentStats();
/*
Returns:
[
    'Web' => [
        'name' => 'Web',
        'display_name' => 'Web Development',
        'count' => 5,
        'color' => 'primary'
    ],
    // ... other departments
]
*/
```

### **User Queries by Department**
```php
// Get all web developers
$webDevelopers = User::where('department', 'Web')->get();

// Get all creative team members
$creativeTeam = User::whereIn('department', ['Web', 'Graphic', 'Editorial', 'Multimedia'])->get();

// Get department counts
$departmentCounts = User::select('department', DB::raw('count(*) as count'))
    ->groupBy('department')
    ->get();
```

## 🛠️ Maintenance

### **Adding New Departments**
1. Update `DepartmentHelper::getEmployeeDepartments()`
2. Update validation rules in `CreateUserRequest`
3. Update `UserController` validation
4. Run `php artisan setup:departments --force`

### **Modifying Department Structure**
1. Update the helper class methods
2. Update validation rules
3. Run migration if database changes needed
4. Update existing users with new structure

### **Troubleshooting**
- Use `php artisan setup:departments --force` to fix department assignments
- Check role assignments with `php artisan tinker`
- Verify validation rules are properly applied

---

**Last Updated**: December 2024  
**Version**: 1.0  
**Status**: Production Ready ✅
