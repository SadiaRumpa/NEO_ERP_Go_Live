# NeoERP - Cloud & Production Database Configuration Guide

This guide details how to configure, secure, and deploy the NeoERP database across local, staging, and enterprise cloud environments (AWS RDS, DigitalOcean Managed Database, Cloudways, Supabase, or custom Linux VPS).

---

## 1. Supported Database Engines

| Engine | Version | Recommended Environment | Key Characteristics |
| :--- | :--- | :--- | :--- |
| **SQLite 3** | 3.35+ | **Local Development & Offline Testing** | Zero setup, embedded single file (`database/database.sqlite`), fully supported by Laravel test suite. |
| **MySQL** | 8.0+ | **Cloud Production (AWS RDS / DigitalOcean / VPS)** | Full ACID transactions, utf8mb4 multi-language support, high concurrent connection throughput. |
| **MariaDB** | 10.5+ | **Cloud Production / Shared Hosting** | Open-source MySQL fork, compatible with Laravel MariaDB driver. |
| **PostgreSQL** | 14.0+ | **Cloud Production (Supabase / Neon / AWS RDS)** | Advanced JSON indexing, connection pooling, enterprise data integrity. |

---

## 2. Setting Up MySQL / MariaDB for Production

### Step 2.1: Create Database & User
Run the following SQL commands on your database server:
```sql
CREATE DATABASE neoerp_production CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

CREATE USER 'neoerp_user'@'%' IDENTIFIED BY 'STRONG_SECRET_PASSWORD_HERE';
GRANT ALL PRIVILEGES ON neoerp_production.* TO 'neoerp_user'@'%';
FLUSH PRIVILEGES;
```

### Step 2.2: Configure `.env`
In your production environment file:
```dotenv
DB_CONNECTION=mysql
DB_HOST=your-rds-or-mysql-host.amazonaws.com
DB_PORT=3306
DB_DATABASE=neoerp_production
DB_USERNAME=neoerp_user
DB_PASSWORD=STRONG_SECRET_PASSWORD_HERE
```

### Step 2.3: Enforcing SSL/TLS (AWS RDS / DigitalOcean Managed)
If your managed cloud database enforces encrypted connections:
1. Download the Certificate Authority (CA) bundle:
   - **AWS RDS**: `global-bundle.pem` (or `rds-combined-ca-bundle.pem`)
   - **DigitalOcean**: `ca-certificate.crt`
2. Place the file in `storage/certs/` (or a secure server path).
3. Add to `.env`:
   ```dotenv
   DB_SSL_CA=/var/www/neoerp/storage/certs/global-bundle.pem
   ```

---

## 3. Setting Up PostgreSQL for Production

If deploying on PostgreSQL, Supabase, Neon, or AWS Aurora PostgreSQL:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=db.your-supabase-or-rds.com
DB_PORT=5432
DB_DATABASE=neoerp_production
DB_USERNAME=postgres
DB_PASSWORD=STRONG_SECRET_PASSWORD_HERE
DB_SCHEMA=public
DB_SSLMODE=require
```

---

## 4. First-Time Production Deployment Commands

On your production server, execute these commands in sequence:

```bash
# 1. Ensure storage directory permissions
chmod -R 775 storage bootstrap/cache

# 2. Run all database migrations (67 tables)
php artisan migrate --force

# 3. Seed essential system permissions & default stock units
php artisan db:seed --class=PermissionTableSeeder --force
php artisan db:seed --class=StockUnitSeeder --force

# 4. Provision primary business & super admin
php artisan db:seed --class=CreateBusinessInfoSeeder --force
php artisan db:seed --class=CreateAdminUserSeeder --force

# 5. (Optional - Staging only) Seed realistic UK construction test data
# php artisan db:seed --class=DemoDataSeeder --force

# 6. Optimize production performance
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## 5. Persistent File Storage (Drawings, Delivery Notes, Photos)

In cloud environments (Docker, Kubernetes, AWS Elastic Beanstalk), local files do not persist across deployments. Configure S3-compatible cloud object storage in `.env`:

```dotenv
FILESYSTEM_DISK=s3
AWS_ACCESS_KEY_ID=your-key
AWS_SECRET_ACCESS_KEY=your-secret
AWS_DEFAULT_REGION=eu-west-2
AWS_BUCKET=neoerp-cloud-assets
AWS_USE_PATH_STYLE_ENDPOINT=false
```

---

## 6. Automated Backup Strategy

To ensure zero data loss in production, configure an automated daily backup cron job:

```bash
# Example crontab entry for nightly MySQL backup (02:00 AM UTC):
0 2 * * * mysqldump -u neoerp_user -p'STRONG_SECRET_PASSWORD_HERE' neoerp_production | gzip > /var/backups/neoerp_$(date +\%Y\%m\%d).sql.gz
```
*(For AWS RDS or DigitalOcean Managed Databases, enable automated daily snapshots with point-in-time recovery).*
