# NeoERP — Enterprise Commercial Construction ERP

NeoERP is an enterprise ERP system engineered specifically for commercial main contractors, subcontractors, and construction trade enterprises. Built on Laravel 12 and PHP 8.4+, it delivers live Job Costing WIP tracking across four distinct cost pillars, automated procurement 3-way matching, UK HMRC CIS tax deductions, and plant & equipment idle bleed protection.

---

## Quick Start (Local Testing)

The local environment is pre-configured with SQLite, fully migrated, and seeded with realistic commercial construction demo data.

```bash
# 1. Install dependencies
composer install

# 2. Run local development server
php artisan serve --port=8000
```

- **Application URL**: `http://localhost:8000`
- **Default Super Admin**: `admin@neoerp.local`
- **Default Password**: `NeoErp!Admin2026#Secure`

To re-seed test data at any time:
```bash
php artisan db:seed
```

---

## Production Database Configuration

For cloud or hosting environments (AWS RDS, DigitalOcean, VPS, Supabase, Cloudways):

1. Copy the production template:
   ```bash
   cp .env.production.example .env
   ```
2. Configure your cloud database connection in `.env`:
   ```dotenv
   DB_CONNECTION=mysql
   DB_HOST=your-database-cluster.com
   DB_PORT=3306
   DB_DATABASE=neoerp_production
   DB_USERNAME=neoerp_user
   DB_PASSWORD=YourSecurePassword
   ```
3. Run cloud migrations & seed initial permissions:
   ```bash
   php artisan migrate --force
   php artisan db:seed --class=PermissionTableSeeder --force
   php artisan db:seed --class=CreateBusinessInfoSeeder --force
   php artisan db:seed --class=CreateAdminUserSeeder --force
   php artisan db:seed --class=StockUnitSeeder --force
   ```

For detailed instructions on SSL certificates, PostgreSQL, and automated backups, see the [Database Configuration Guide](docs/DATABASE_CONFIGURATION.md).

---

## Core Construction Milestones

1. **Procurement & 3-Way Matching**: Purchase order lifecycle, goods received notes (GRNs), and supplier bills with automated tolerance variance detection.
2. **Subcontractor Management & UK CIS**: HMRC UTR verification, 20% standard / 30% higher tax rate deductions on labor with material exemptions, and CIS300 monthly returns.
3. **Plant & Equipment Hire**: On-hire tracking, daily/weekly rates, overdue standby bleed alerts, and off-hire certificates.
4. **Commercial Contracting**: Digital change orders (variations) with client e-signatures, progress payment applications (valuations), and retention ledgers.
5. **Live Job Costing WIP**: Executive multi-project portfolio dashboard aggregating costs across Materials, Labor, Subcontractors, and Plant against contract sums.

---

## Automated Test Suite

NeoERP features 100% test coverage across all commercial modules:

```bash
php artisan test
```
*70 passing feature tests (413 assertions).*
