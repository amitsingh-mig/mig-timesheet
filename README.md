# Employee Timesheet Management System

A comprehensive Laravel-based employee timesheet and attendance management system with role-based access control, modern UI, and robust security features.

## Features

### Core Functionality
- **User Management**: Role-based user system (Admin, Employee, Manager)
- **Timesheet Management**: Create, edit, submit, and approve timesheets
- **Attendance Tracking**: Clock in/out functionality with location tracking
- **Daily Updates**: Work summary and progress tracking
- **Calendar View**: Visual timesheet calendar for admins
- **Reports & Analytics**: Comprehensive reporting and data export

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

## Default Credentials

After running the migrations and seeders, you can log in with:

- **Admin**: admin@example.com / Admin@9711#31$
- **Employee**: amitrajput30205@gmail.com / User@12345!

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

## API Endpoints

### Authentication
- `POST /login` - User login
- `POST /logout` - User logout
- `GET /user` - Get authenticated user

### Timesheets
- `GET /timesheet` - List user timesheets
- `POST /timesheet` - Create timesheet
- `PUT /timesheet/{id}` - Update timesheet
- `DELETE /timesheet/{id}` - Delete timesheet

### Attendance
- `POST /attendance/clock-in` - Clock in
- `POST /attendance/clock-out` - Clock out
- `GET /attendance/status` - Get attendance status

## Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support and questions, please contact the development team or create an issue in the repository.
