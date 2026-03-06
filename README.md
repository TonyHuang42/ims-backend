### 1. Install dependencies

Install the PHP dependencies:

```bash
composer install
npm install
```

### 2. Environment Configuration

Create a copy of the example environment file:

```bash
cp .env.example .env
```

Open the `.env` file and update your database connection settings.

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ims
DB_USERNAME=root
DB_PASSWORD=your_password
```

### 3. Generate Application Keys

Generate the main Laravel application key and the JWT authentication secret:

```bash
php artisan key:generate
php artisan jwt:secret
```

### 4. Run Migrations & Seed the Database

Run the database migrations to create the tables, and seed the database with initial data (roles, departments, teams, and sample users):

```bash
php artisan migrate --seed
```

**Default Admin Credentials:**
- **Email:** `admin@example.com`
- **Password:** `password`

## Running the Application

To start the local development server with all related services (web server, queue listener, log tailing, and Vite dev server), run:

```bash
composer run dev
```

The application will be accessible at `http://localhost:8000`.

## Testing

This project uses [Pest](https://pestphp.com/) for testing. To run the test suite, use the composer script:

```bash
composer run test
```
