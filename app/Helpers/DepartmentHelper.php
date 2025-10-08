<?php

namespace App\Helpers;

class DepartmentHelper
{
    /**
     * Get all valid departments
     */
    public static function getAllDepartments(): array
    {
        return [
            'Admin',
            'Web',
            'Graphic', 
            'Editorial',
            'Multimedia',
            'Sales',
            'Marketing',
            'Intern',
            'General'
        ];
    }

    /**
     * Get departments available for employees (non-admin users)
     */
    public static function getEmployeeDepartments(): array
    {
        return [
            'Web',
            'Graphic', 
            'Editorial',
            'Multimedia',
            'Sales',
            'Marketing',
            'Intern',
            'General'
        ];
    }

    /**
     * Get admin department
     */
    public static function getAdminDepartment(): string
    {
        return 'Admin';
    }

    /**
     * Check if department is valid
     */
    public static function isValidDepartment(string $department): bool
    {
        return in_array($department, self::getAllDepartments());
    }

    /**
     * Check if department is valid for employees
     */
    public static function isValidEmployeeDepartment(string $department): bool
    {
        return in_array($department, self::getEmployeeDepartments());
    }

    /**
     * Get department display name with proper formatting
     */
    public static function getDisplayName(string $department): string
    {
        $displayNames = [
            'Admin' => 'Administration',
            'Web' => 'Web Development',
            'Graphic' => 'Graphic Design',
            'Editorial' => 'Editorial',
            'Multimedia' => 'Multimedia',
            'Sales' => 'Sales',
            'Marketing' => 'Marketing',
            'Intern' => 'Internship',
            'General' => 'General'
        ];

        return $displayNames[$department] ?? $department;
    }

    /**
     * Get department color for UI display
     */
    public static function getDepartmentColor(string $department): string
    {
        $colors = [
            'Admin' => 'danger',
            'Web' => 'primary',
            'Graphic' => 'success',
            'Editorial' => 'info',
            'Multimedia' => 'warning',
            'Sales' => 'secondary',
            'Marketing' => 'dark',
            'Intern' => 'light',
            'General' => 'muted'
        ];

        return $colors[$department] ?? 'secondary';
    }

    /**
     * Get departments grouped by category
     */
    public static function getDepartmentsByCategory(): array
    {
        return [
            'Administration' => ['Admin'],
            'Creative' => ['Web', 'Graphic', 'Editorial', 'Multimedia'],
            'Business' => ['Sales', 'Marketing'],
            'Other' => ['Intern', 'General']
        ];
    }

    /**
     * Get department statistics
     */
    public static function getDepartmentStats(): array
    {
        $stats = [];
        $departments = self::getAllDepartments();

        foreach ($departments as $department) {
            $count = \App\Models\User::where('department', $department)->count();
            $stats[$department] = [
                'name' => $department,
                'display_name' => self::getDisplayName($department),
                'count' => $count,
                'color' => self::getDepartmentColor($department)
            ];
        }

        return $stats;
    }
}
