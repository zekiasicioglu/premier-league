# Premier League Application

## Requirements
- PHP 8.2
- Node.js 18
- Composer
- MySQL 8.0 (if using MySQL option)
- Docker (if using MySQL with Docker)

## Database Options
1. **MySQL with Docker**
   ```bash
   docker-compose up -d mysql
   ```

2. **SQLite**
   - Update `.env` file with SQLite configuration:
   ```
   DB_CONNECTION=sqlite
   ```

## Setup Steps

1. **Environment Setup**
   ```bash
   cp .env.example .env
   ```
   Configure database settings in `.env`:
   ```
   # For MySQL
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=premier_league
   DB_USERNAME=root
   DB_PASSWORD=your_password

   # For SQLite
   DB_CONNECTION=sqlite
   ```

2. **Backend Setup**
   ```bash
   composer install
   php artisan migrate
   php artisan db:seed
   ```

3. **Frontend Setup**
   ```bash
   npm install
   npm run dev
   ```

## API Base URL Configuration
Two options for configuring `VITE_API_BASE_URL` in `.env`:

1. When using default port 8000:
   ```
   APP_URL=http://localhost:8000
   VITE_API_BASE_URL="${APP_URL}"
   ```

2. Alternative configuration:
   ```
   APP_URL=http://localhost
   VITE_API_BASE_URL="${APP_URL}:8000"
   ```

## Team Seeding Options
Two strategies available in `TeamSeeder.php`:

1. Default strategy (from tables page):
   ```php
   TeamsServiceFacade::seedTeams("tables")
   ```
   - Fetches data from: https://www.premierleague.com/tables
   - Includes team strength values

2. Alternative strategy (from clubs page):
   ```php
   TeamsServiceFacade::seedTeams("teams")
   ```
   - Fetches data from: https://www.premierleague.com/clubs
   - Sets team strength to 0

## Default User Credentials
- Email: test@example.com
- Password: password

## Development
- Backend runs with: `php artisan serve`
- Frontend runs with: `npm run dev`
- Each setup step has its own commit for reference

## Project Structure
- Backend: Laravel application
- Frontend: Vue.js with Vite
- Database: MySQL or SQLite
- Team data: Scraped from Premier League website 