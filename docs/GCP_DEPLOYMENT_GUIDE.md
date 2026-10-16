# Deploying NeoERP on Google Cloud Platform (GCP)

This guide walks you through deploying and showcasing **NeoERP** on **Google Cloud Platform** in less than 5 minutes using the **Google Cloud Console** web interface.

---

## 🌟 Why Google Cloud Platform for NeoERP?

- **Enterprise Reliability**: Hosted on Google's global fiber backbone.
- **Full Dockerized Stack**: Seamlessly runs PHP 8.2/8.4, Nginx, MySQL 8.0, Redis 7, Background Queue Workers, and the Artisan Scheduler.
- **Pre-loaded Showcase Data**: The automated deployment script automatically provisions **100 commercial construction enquiries, active jobs, live WIP costing, plant hires, and demo user accounts**.
- **Cost Effective**: An `e2-micro` or `e2-small` instance is eligible for free trial credits or costs only a few dollars per month.

---

## 🚀 Quick Step-by-Step Walkthrough

### Step 1: Open Google Cloud Console
1. Navigate to [console.cloud.google.com](https://console.cloud.google.com/).
2. Log in with your Google account.
3. Select or create a project (e.g., `neoerp-showcase`).

---

### Step 2: Create a Compute Engine VM Instance
1. In the left navigation menu, go to **Compute Engine** > **VM instances**.
2. Click **Create Instance**.
3. Configure the VM settings:
   - **Name**: `neoerp-showcase`
   - **Region / Zone**: Choose the closest region (e.g., `europe-west2` for London, `us-central1` for US, etc.)
   - **Machine configuration**:
     - Series: `E2`
     - Machine type: `e2-small` (2 vCPU, 2 GB RAM) or `e2-medium` (2 vCPU, 4 GB RAM) for smooth container builds.
   - **Boot disk**:
     - Click **Change**.
     - Operating System: **Ubuntu**
     - Version: **Ubuntu 24.04 LTS** (or Ubuntu 22.04 LTS)
     - Size: **20 GB** or **30 GB** (Standard persistent disk)
     - Click **Select**.
   - **Firewall** (*Crucial for public access*):
     - ✅ Check **Allow HTTP traffic**
     - ✅ Check **Allow HTTPS traffic**
4. Click **Create** at the bottom. The VM will boot in about 30–45 seconds.

---

### Step 3: Connect via Web SSH in Your Browser
1. In the VM instances list, locate `neoerp-showcase`.
2. Under the **Connect** column, click the **SSH** button.
3. A Google Cloud terminal window will open directly inside your browser.

---

### Step 4: Run the 1-Line Automated Deployment
In the SSH terminal window, copy and paste this single command:

```bash
git clone https://github.com/SadiaRumpa/NEO_ERP_Go_Live.git neoerp && cd neoerp && chmod +x deploy-gcp.sh && ./deploy-gcp.sh
```

---

### Step 5: That's It! What the Script Does Automatically
The script will run autonomously:
1. Installs **Docker** and the **Docker Compose** plugin.
2. Generates the production `.env` configuration file with a secure `APP_KEY`.
3. Launches all 6 Docker services:
   - `neoerp_web` (Nginx reverse proxy on port 80)
   - `neoerp_app` (PHP-FPM 8.2 with PDO MySQL, BCMath, Zip, GD, EXIF)
   - `neoerp_db` (MySQL 8.0 with persistent storage)
   - `neoerp_redis` (Redis 7 in-memory cache and queue driver)
   - `neoerp_queue` (Artisan asynchronous queue background worker)
   - `neoerp_scheduler` (Artisan automated cron task runner)
4. Waits for the MySQL database cluster to be healthy.
5. Runs all database migrations (`php artisan migrate --force`).
6. Seeds the complete showcase database (`php artisan db:seed --force`):
   - **100 Construction Enquiries & Leads**
   - **Active Commercial Jobs with live WIP 4-pillar costing**
   - **UK HMRC CIS Subcontractors & Payment Certificates**
   - **Procurement 3-Way Match Purchase Orders & Supplier Bills**
   - **Plant & Equipment Hire Register with Bleed Standby Alerts**
7. Links the public storage disk and caches configuration and routes for maximum speed.
8. Detects your VM's public External IP and displays your showcase URL.

---

## 🔑 Showcase Access Credentials

Once the script finishes, open your browser and navigate to:
`http://<YOUR-GCP-EXTERNAL-IP>`

### 1. Super Admin Account (Full System Access)
- **Email**: `admin@neoerp.local`
- **Password**: `NeoErp!Admin2026#Secure`

### 2. Commercial Director / Staff Account
- **Email**: `marcus.vance@neoerp.local`
- **Password**: `NeoErp!Staff2026#`

---

## 🛡️ Optional: Custom Domain & Free SSL (HTTPS)

If you would like to bind a custom domain (e.g., `erp.yourdomain.com`):
1. In your DNS provider (Cloudflare, GoDaddy, Namecheap), point an `A` record for your subdomain to the **External IP** of your GCP VM.
2. In the SSH terminal, install Certbot for Let's Encrypt:
   ```bash
   sudo apt-get install -y certbot python3-certbot-nginx
   sudo certbot --nginx -d erp.yourdomain.com
   ```
3. Update `APP_URL=https://erp.yourdomain.com` in `/home/$USER/neoerp/.env`.

---

## 🛠️ Useful Management Commands on the GCP VM

Inside the `neoerp/` directory on your VM:
- **View running containers**: `docker compose ps`
- **View live application logs**: `docker compose logs -f app`
- **View web server logs**: `docker compose logs -f web`
- **Re-seed demo data**: `docker compose exec app php artisan db:seed --force`
- **Restart services**: `docker compose restart`
