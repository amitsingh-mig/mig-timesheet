# MIG-TimeSheet

A comprehensive Laravel-based employee timesheet and attendance management system with role-based access control, modern UI, and robust security features. Built for efficient time tracking and employee management.

## Features

### Core Functionality
- **User Management**: Role-based user system (Admin, Employee)
- **Timesheet Management**: Create, edit, and manage timesheets with multiple entries per day
- **Real-time Updates**: Live synchronization between employee and admin views
- **Employee Time Tracking**: Comprehensive time tracking with hours calculation
- **Admin Dashboard**: Complete admin panel for employee management
- **Multiple Entries**: Support for multiple timesheet entries per day
- **Auto-refresh**: Automatic data refresh and cross-tab synchronization

### Security Features
- **CSRF Protection**: All forms protected against CSRF attacks
- **Role-Based Access Control**: Granular permissions system
- **Mass Assignment Protection**: Secure model attributes
- **Input Validation**: Comprehensive form validation
- **Session Security**: Secure session management

### User Interface
- **Responsive Design**: Mobile-friendly interface
- **Role-Based Theming**: Different color schemes for different roles
- **Modern UI Components**: Built with Tailwind CSS
- **Interactive Elements**: Real-time updates and notifications

## Technology Stack

- **Backend**: Laravel 9.x
- **Frontend**: Blade templates with Tailwind CSS
- **Database**: MySQL/PostgreSQL
- **Authentication**: Laravel Sanctum
- **JavaScript**: Alpine.js for interactivity

## Installation

### Prerequisites
- PHP 8.0.2 or higher
- Composer
- Node.js and NPM
- MySQL/PostgreSQL database

### Setup Instructions

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd employee-time-sheet
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment configuration**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

5. **Database setup**
   ```bash
   php artisan migrate
   php artisan db:seed
   ```

6. **Build assets**
   ```bash
   npm run build
   ```

7. **Start the development server**
   ```bash
   php artisan serve
   ```

8. **Access the application**
   - Open your browser and go to `http://127.0.0.1:8000`
   - Login with admin credentials: `admin@example.com` / `admin123`
   - Or register a new employee account

## Quick Start

1. **Login as Admin**: Use `admin@example.com` / `admin123`
2. **Add Employee**: Go to Dashboard → Add Employee
3. **Employee Login**: Employee can login and add timesheet entries
4. **View Records**: Admin can view all employee records at `/admin/employees/time/view`
5. **Real-time Updates**: Changes are automatically synchronized across views

## Default Credentials

After running the migrations and seeders, you can log in with:

- **Admin**: admin@example.com / admin123
- **Employee**: amitrajput30205@gmail.com / (password set during registration)

## Project Structure

```
app/
├── Http/
│   ├── Controllers/     # Application controllers
│   ├── Middleware/      # Custom middleware
│   └── Requests/        # Form request validation
├── Models/              # Eloquent models
├── Policies/            # Authorization policies
└── Services/            # Business logic services

resources/
├── views/               # Blade templates
├── css/                 # Stylesheets
└── js/                  # JavaScript files

database/
├── migrations/          # Database migrations
└── seeders/            # Database seeders
```

## Security

This application implements multiple security layers:

- **Authentication**: Secure user authentication with Laravel Sanctum
- **Authorization**: Role-based access control with policies
- **CSRF Protection**: All forms protected against CSRF attacks
- **Input Validation**: Comprehensive validation rules
- **Mass Assignment Protection**: Secure model attributes

For detailed security information, see [SECURITY.md](SECURITY.md).

## Key Features

### Employee Features
- **Timesheet Management**: Add multiple entries per day
- **Time Tracking**: Track work hours with start/end times
- **Task Management**: Add task descriptions and details
- **Real-time Updates**: Automatic synchronization with admin view

### Admin Features
- **Employee Management**: View and manage all employees
- **Time Overview**: Comprehensive time tracking dashboard
- **Employee Records**: Detailed employee time records
- **Real-time Monitoring**: Live updates of employee activities
- **Debug Tools**: Built-in debugging and monitoring tools

### System Features
- **Cross-tab Sync**: Real-time updates across browser tabs
- **Auto-refresh**: Automatic data refresh every 60 seconds
- **Cache Management**: Intelligent caching with force refresh
- **Error Handling**: Comprehensive error handling and user feedback

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Recent Updates

### Version 2.0 - Current
- ✅ **Branding Update**: Changed from MIG-HRM to MIG-TimeSheet
- ✅ **Multiple Entries**: Support for multiple timesheet entries per day
- ✅ **Real-time Sync**: Cross-tab synchronization and auto-refresh
- ✅ **Admin Dashboard**: Enhanced admin panel with employee management
- ✅ **Login Fix**: Resolved admin login issues
- ✅ **Performance**: Optimized data loading and caching
- ✅ **UI Improvements**: Enhanced user interface and user experience

### Key Improvements
- **Employee Records**: Real-time employee time tracking
- **Debug Tools**: Built-in debugging and monitoring
- **Cache Management**: Intelligent caching with force refresh
- **Error Handling**: Comprehensive error handling and user feedback
- **Mobile Responsive**: Fully responsive design for all devices

## Support

For support and questions, please contact the development team or create an issue in the repository.

---

**MIG-TimeSheet** - Efficient Employee Time Management System
