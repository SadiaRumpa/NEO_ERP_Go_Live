# NeoERP — Enterprise Commercial Construction ERP

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2%20%7C%208.4-777BB4?style=flat&logo=php&logoColor=white)](https://www.php.net/)
[![Docker](https://img.shields.io/badge/Docker-Ready-2496ED?style=flat&logo=docker&logoColor=white)](https://www.docker.com/)
[![Tests](https://img.shields.io/badge/Tests-73%20Passed%20(427%20assertions)-brightgreen?style=flat)](https://phpunit.de/)
[![License](https://img.shields.io/badge/License-Proprietary-blue.svg)](LICENSE)

**NeoERP** is a next-generation Enterprise Resource Planning (ERP) platform engineered specifically for commercial main contractors, trade subcontractors, and construction enterprises. Built on **Laravel 12** and **PHP 8.4+**, it unites multi-project commercial contracting, automated procurement 3-way matching, UK HMRC CIS tax deductions, plant & equipment standby bleed alerts, and live four-pillar Job Costing WIP tracking into an executive web interface.

---

## 🏗️ Core Commercial Milestones

1. **Live 4-Pillar Job Costing WIP**: Real-time project cost aggregation across **Materials**, **Labor**, **Subcontractors**, and **Plant & Equipment** against contract sums, with unbilled WIP assets vs. overbilling liability tracking.
2. **Procurement & 3-Way Matching**: Complete purchase order lifecycle, Goods Received Notes (GRN), and supplier bill reconciliation with automated variance tolerance detection.
3. **UK HMRC CIS Subcontractor Management**: HMRC UTR verification, 20% standard / 30% higher tax rate deductions on labor with material exemptions, and automated CIS300 monthly returns.
4. **Plant & Equipment Hire Register**: Tracking of on-hire equipment, daily/weekly rates, overdue standby bleed alerts, and off-hire certificates.
5. **Commercial Contracting**: Change orders (variations) with client e-signatures, progress payment applications (valuations), and retention ledgers.
6. **Executive Reports & Analytics**: Lead-to-job conversion velocity, pipeline metrics, and weighted stock valuation reports.
7. **Modernized UI/UX**:
   - **Dual Light / Dark Mode**: Crisp light and dark slate themes with instant one-click switching.
   - **Pinned Bottom Controls Dock**: User Profile, Theme Mode switcher, and Configuration Gear neatly docked at the bottom of the left sidebar.
   - **Sticky List Headers & Dual Sliders**: Pinned table headers with top and bottom synchronized horizontal sliders for wide commercial data tables.
   - **Executive Contact Icons**: Visual interactive contact action links (phone, mail, WhatsApp, map).

---

## 🌐 Google Cloud Run Serverless Deployment (1-Click Public HTTPS)

🚀 **Live Production URL**: [https://neoerp-1009631465369.europe-west2.run.app](https://neoerp-1009631465369.europe-west2.run.app)

NeoERP is deployed on **Google Cloud Run** in `europe-west2` (London) with automated auto-scaling and an official, publicly-trusted Google SSL certificate.

### ⚡ Option A: 1-Click CLI Deployment (Local Windows Terminal)

Run the included automated deployment script in PowerShell:

```powershell
.\deploy-cloudrun.ps1
```
*(Or double-click `deploy-cloudrun.bat`)*

The script will automatically:
1. Verify Google Cloud SDK (`gcloud`) and sign you in.
2. Select your Google Cloud Project.
3. Enable Cloud Run, Cloud Build & Artifact Registry APIs.
4. Build the container with PHP 8.4, Nginx & all dependencies.
5. Provision SQLite database, run migrations, and seed **100 commercial demo records**.
6. Output your live `https://<service-name>.run.app` URL and login credentials.

---

### 🌐 Option B: Continuous Deployment from Google Cloud Console UI

1. Open [Google Cloud Console &rarr; Cloud Run](https://console.cloud.google.com/run).
2. Click **Create Service**.
3. Choose **Continuously deploy from a repository** &rarr; Select GitHub &rarr; `SadiaRumpa/NEO_ERP_Go_Live` (branch: `main`).
4. Build Type: **Dockerfile** (path: `Dockerfile`).
5. Service name: `neoerp` | Region: `europe-west2` (or your preferred region).
6. Authentication: Check **Allow unauthenticated invocations**.
7. Container port: `8080` | Memory: `1 GiB` | CPU: `1`.
8. Click **Create** & wait ~2 minutes. Cloud Run will output your live HTTPS link!

---

## 🖥️ Alternative: Compute Engine VM Deployment

### 1. Launch a Compute Engine VM
1. Open [Google Cloud Console](https://console.cloud.google.com/) &rarr; **Compute Engine** &rarr; **VM instances**.
2. Click **Create Instance**:
   - **Machine type**: `e2-small` (2 GB RAM) or `e2-medium` (4 GB RAM).
   - **Boot disk**: **Ubuntu 24.04 LTS** (or 22.04 LTS) with **25 GB** disk space.
   - **Firewall**: Check both **Allow HTTP traffic** and **Allow HTTPS traffic**.
3. Click **Create** and wait ~30 seconds for the VM to start.

### 2. Connect via Web SSH & Deploy
Click the **SSH** button next to your VM instance in the Google Cloud Console, and run:

```bash
sudo apt update && sudo apt install -y git curl
git clone https://github.com/SadiaRumpa/NEO_ERP_Go_Live.git neoerp
cd neoerp
chmod +x deploy-gcp.sh
./deploy-gcp.sh
```

*(If cloning via SSH key: `git clone git@github.com:SadiaRumpa/NEO_ERP_Go_Live.git neoerp`)*

The script automatically:
- Installs Docker Engine & the Docker Compose plugin.
- Launches 6 isolated containers: Nginx, PHP-FPM, MySQL 8.0, Redis 7, Queue worker, and Scheduler.
- Runs all database migrations.
- Seeds **100 commercial enquiries, active jobs, live WIP portfolio, plant hires, and demo accounts**.
- Optimizes production route and view caches.

---

## 🔑 Showcase Login Credentials

Once deployment completes, open your browser and navigate to:  
`http://<YOUR-GCP-EXTERNAL-IP>`

| Role | Email | Password | Access Level |
| :--- | :--- | :--- | :--- |
| **Super Admin** | `admin@neoerp.local` | `NeoErp!Admin2026#Secure` | Full system configuration & management |
| **Commercial Director** | `marcus.vance@neoerp.local` | `NeoErp!Staff2026#` | Contracts, WIP portfolio & procurement |
| **Project Engineer** | `sarah.engineer@neoerp.local` | `NeoErp!Staff2026#` | Job execution, RAMS & task management |

---

## 🚀 Sharing the Showcase Link with Others

To share your live project showcase with clients, partners, or team members:

1. **Find your VM's External IP**:
   - In Google Cloud Console &rarr; **Compute Engine** &rarr; **VM instances**, locate the **External IP** column for `neoerp-showcase` (e.g. `34.142.123.45`).
2. **Share the Direct Web Link**:
   - Provide the URL: `http://<EXTERNAL-IP>` along with the demo credentials above.
   - *Ensure the firewall allows HTTP: In GCP Console VM details, verify that **"Allow HTTP traffic"** is checked.*
3. **Make the IP Static (Recommended so the IP never changes)**:
   - In GCP Console &rarr; **VPC network** &rarr; **IP addresses**, find the external IP of your VM and change its type from **Ephemeral** to **Static**.
4. **Connect a Custom Domain & Free SSL (Optional)**:
   - Point an `A` record from your domain (e.g. `erp.yourdomain.com`) to the VM's External IP.
   - In the VM terminal, run Let's Encrypt Certbot:
     ```bash
     sudo apt-get install -y certbot python3-certbot-nginx
     sudo certbot --nginx -d erp.yourdomain.com
     ```

---

## 🔄 Automated Continuous Deployment (GitHub Actions CI/CD)

Whenever you push new code to `main`, GitHub Actions can automatically update your live GCP VM with zero manual intervention.

### Setup (Takes 2 minutes):
1. In your GCP SSH terminal, generate a deployment key:
   ```bash
   ssh-keygen -t ed25519 -N "" -f ~/.ssh/github_actions_key && cat ~/.ssh/github_actions_key.pub >> ~/.ssh/authorized_keys && cat ~/.ssh/github_actions_key
   ```
2. In your GitHub repository, go to **Settings &rarr; Secrets and variables &rarr; Actions &rarr; New repository secret** and add:
   - `GCP_VM_HOST`: Your VM's External IP.
   - `GCP_VM_USER`: Your GCP username (e.g., `sadiahossain133`).
   - `GCP_SSH_PRIVATE_KEY`: The private key printed above.

### Manual VM Update Command
At any time, you can also update the VM directly by SSHing in and running:
```bash
cd ~/neoerp && ./update.sh
```

---

## 💻 Local Development Quick Start

The local environment is pre-configured with SQLite and fully seeded with realistic test data.

```bash
# 1. Install PHP dependencies
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

## 🧪 Automated Test Suite

NeoERP maintains 100% passing test coverage across all commercial modules:

```bash
php artisan test
```

```text
Tests:    73 passed (427 assertions)
Duration: 3.5s
```

Covers:
- Phase 1 Security, Authorization & RBAC
- Phase 2 Commercial Pipelines, Enquiries & Status Transitions
- Phase 3 Sanctum API, Rate Limiting & Notifications
- Phase 4 Mobile Field Hub PWA, CIS Invoicing & Accounting
- Procurement 3-Way Matching, Price/Quantity Variance & GRN
- Plant & Equipment On-Hire Tracking, Standby Bleed & Overdue Alerts
- Subcontractor CIS Standard/Higher Deductions & Monthly CIS300 Return
- Live Job Costing 4-Pillar WIP Aggregation & Commercial Health Status
- Enterprise Password Policy Compliance

---

## 📄 Documentation

- [GCP Deployment Guide](docs/GCP_DEPLOYMENT_GUIDE.md) — Comprehensive Google Cloud Console walkthrough.
- [Database Configuration Guide](docs/DATABASE_CONFIGURATION.md) — Production database setup (MySQL, PostgreSQL, SSL).
