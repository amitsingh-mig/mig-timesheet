# Advanced Employee Records Filter

## Overview
This feature adds an advanced filter system to view employee records with comprehensive working hours calculations and CSV export functionality.

## Features

### 1. Advanced Filtering
- **Employee Name**: Filter by specific employee
- **Department**: Filter by department (Web, Graphic, Editorial, Multimedia, Sales, Marketing, Intern, General)
- **Time Period**: Choose between Days, Weeks, Months, Years
- **Custom Date Range**: Optional custom date range selection

### 2. Working Hours Display
The system calculates and displays working hours in four categories:
- **Days**: e.g., 08 hrs (current day)
- **Weeks**: e.g., 42 hrs (current week)
- **Months**: e.g., 168 hrs (current month)
- **Years**: e.g., 2016 hrs (current year)

### 3. Working Hours Summary
Real-time summary cards showing:
- Total Employees
- Total Hours (for selected period)
- Average Hours per Employee
- Active Employees

### 4. CSV Export
Export filtered employee data to CSV with the following columns:
- Employee Name
- Email
- Department
- Days Hours
- Weeks Hours
- Months Hours
- Years Hours
- Status (Active/Inactive)
- Export Date

## Access
Navigate to: `http://127.0.0.1:8000/admin/employees/time/view`

## API Endpoints

### Get Employee Records
```
GET /admin/employees/time/records
```
Parameters:
- `employee_id` (optional): Filter by specific employee
- `department` (optional): Filter by department
- `time_period` (optional): days, weeks, months, years
- `start_date` (optional): Custom start date
- `end_date` (optional): Custom end date
- `page` (optional): Page number for pagination

### Get Working Hours Summary
```
GET /admin/employees/time/summary
```
Parameters:
- `period` (optional): day, week, month, year
- `employee_id` (optional): Filter by specific employee
- `department` (optional): Filter by department
- `start_date` (optional): Custom start date
- `end_date` (optional): Custom end date

### Export to CSV
```
GET /admin/employees/time/export
```
Parameters:
- `employee_id` (optional): Filter by specific employee
- `department` (optional): Filter by department
- `time_period` (optional): days, weeks, months, years
- `start_date` (optional): Custom start date
- `end_date` (optional): Custom end date

## Technical Implementation

### Controller Methods
- `getEmployeeRecords()`: Retrieves paginated employee records with hours calculations
- `getWorkingHoursSummary()`: Calculates summary statistics
- `calculateEmployeeHours()`: Calculates hours for different time periods
- `getHoursForPeriod()`: Gets hours for a specific date range
- `exportTimeCsv()`: Exports filtered data to CSV

### Database Queries
The system queries the `timesheets` table to calculate working hours based on:
- `hours_worked` field (decimal)
- `hours` field (HH:MM format or decimal)
- Date ranges for different periods

### Frontend Features
- Responsive Bootstrap-based UI
- Real-time filtering
- Interactive charts
- Modal dialogs for detailed views
- Pagination support
- Loading states and error handling

## Usage Examples

### Filter by Department
1. Select "Web Development" from Department dropdown
2. Click "Apply Filters"
3. View filtered results with working hours

### Export Specific Employee Data
1. Select employee from "Employee Name" dropdown
2. Choose time period (e.g., "Months")
3. Click "Export CSV"
4. Download file with employee's monthly hours

### View Summary Statistics
1. Use period buttons (Day/Week/Month/Year)
2. Apply filters as needed
3. View real-time summary cards

## Security
- All endpoints require admin authentication
- CSRF protection enabled
- Input validation and sanitization
- SQL injection prevention through Eloquent ORM

## Browser Compatibility
- Modern browsers with JavaScript enabled
- Bootstrap 5 compatible
- Chart.js for data visualization
