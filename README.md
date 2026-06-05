# 🐾 MyVetPaws — Premium Veterinary Clinic & Practice Management System

MyVetPaws is a modern, high-performance, and visually stunning web application built for veterinary clinics, veterinary practitioners, and clinic owners. Featuring a premium **Obsidian Cosmic Dark-Mode Design**, MyVetPaws streamlines patient check-ins, diagnostic recordings, medical item management, billing/invoices, and appointment scheduling.

---

## ✨ Features & Architecture Highlights

### 🌌 Cosmic Glassmorphic Interface
* **Premium Design System**: Built with modern HSL-tailored cosmic colors, dark gradients, frosted glass panels (`backdrop-filter`), and micro-animations.
* **Tactile Interactions**: Dynamic bounce transitions on buttons, subtle hover indicators, and custom interactive inputs.
* **Responsive Layouts**: Full-screen optimized for desktop clinic dashboards, with elegant responsive collapses for mobile and tablet usage.

### 📅 Interactive Visits & Google Calendar View
* **Monthly Calendar Grid**: Seamless client-side monthly view built using Alpine.js. Transition across months timezone-safely.
* **Visit Details Panel**: Interactive date cells. Click on any date to load a detailed summary card of that day's scheduled check-ins below.
* **Quick Scheduler**: Hover over any date grid cell to reveal a `+` shortcut button linking to a patient check-in page prefilled for that specific date.

### 🏥 Patient Check-In & Medical Logs
* **Initial Vitals & Vitals Form**: Track patient weight (kg), body temperature (°C), and chief complaints during registration.
* **Smart Patient Filtering**: Alpine.js dynamically filters registered pets on-the-fly when choosing a customer.
* **Diagnostic & Treatment Logs**: Record exact diagnosis reports, detailed treatment plans, and schedule retroactive/future check-ups.

### 🗺️ Clinic Branding & Location Pinpoint (New)
* **Branding Asset Uploads**: Upload clinic branding assets (Logo and Banner) dynamically with real-time browser previewing, file validation, and automatic sizing cleanup.
* **Interactive Leaflet Map Pinpoint**: Includes an interactive Leaflet.js-powered map for easy location coordination (especially tailored for non-technical users).
* **Bi-Directional Coordinate Syncing**: Drag-and-drop the map marker to auto-update Latitude/Longitude input fields instantly, or type coordinates manually to shift the marker.
* **Geocoding Address Search**: Integrated address search bar querying OpenStreetMap Nominatim API to locate addresses instantly and pin coordinates automatically.

### 💊 Medical Services & Items Inventory Tracking (Obat & Alat Medis)
* **Services CRUD**: Configure clinic services (Grooming, Consultation, Surgeries, etc.) with automated pricing.
* **Medicines & Supplies CRUD**: Full inventory management for physical medical items, vaccines, syringes, and disposable supplies. Tracks purchase cost (`buy_price`), selling price (`sell_price`), margin, current stock levels, and safety thresholds (`min_stock`).
* **Auto-Depletion**: Prescribing items during a patient's examination automatically decrements stock levels in the database.
* **Restock Alerts**: Features visual indicators (Available, Low Stock, Out of Stock) throughout catalogs and a dedicated **"Restock Required"** widget on the Owner Dashboard showing items below safety thresholds.

### 🧾 Smart Consolidated Invoices & Billing
* **Multi-Pet Consolidated Invoices**: Generates a single unified invoice PDF for customers checking in multiple pets on the same day.
* **Separated billing**: Separates professional fees/procedures from physical medicines and disposable items on invoices for maximum clarity.
* **Clean Print Templates**: Clean PDF templates optimized for printers and digital downloads (removed generic branding watermarks for a premium feel).
* **Payment Tracking**: Record partial payments, calculate remaining balances, and track payment history instantly.

---

## 🛠️ Technology Stack

* **Backend Framework**: CodeIgniter 4.7.3 (PHP 8.2+)
* **Database**: SQLite (Local development) / MySQL (Production server)
* **Frontend Styles**: Tailwind CSS v4 (Compiles via PostCSS / Vite toolchain)
* **Interactions**: Alpine.js & Lucide Icons
* **Deployment Automation**: GitHub Actions & Rsync over SSH

---

## 🚀 Local Development Setup

Follow these steps to run MyVetPaws on your local machine:

### 1. Prerequisites
* **PHP**: 8.2 or 8.4+ (required extensions: `intl`, `mbstring`, `sqlite3`, `mysqlnd`, `curl`)
* **Composer**: For PHP dependency management
* **Node.js & npm**: For compilation of Tailwind CSS assets

### 2. Installation Steps
1. Clone the repository and navigate to the project directory:
   ```bash
   cd "New Myvetpaws codeigniter"
   ```
2. Install Composer dependencies:
   ```bash
   composer install
   ```
3. Install NPM packages:
   ```bash
   npm install
   ```
4. Copy the environment template file:
   ```bash
   cp env .env
   ```
5. Open `.env` and configure your settings:
   * Ensure `app.baseURL` matches your local URL (e.g., `http://localhost:8083/`).
   * Ensure `database.default.DBDriver = 'SQLite3'` is configured for local development.

### 3. Database Migration & Seeding
Prepare the database schemas and populate sample data (includes a pre-registered clinic, doctor, sample customers, and invoices):
```bash
php spark migrate
php spark db:seed SampleDataSeeder
```

### 4. Running the Application
1. Start compiling Tailwind CSS assets:
   ```bash
   npm run build
   ```
   *(For active development with hot-rebuilding, run `npm run dev` instead)*
2. Spin up the CodeIgniter development server:
   ```bash
   php spark serve --port 8083
   ```
3. Open your browser and go to: **[http://localhost:8083/index.php/login](http://localhost:8083/index.php/login)**
4. Log in using the sample credentials:
   * **Email**: `admin@clinic.com`
   * **Password**: `password`

---

## 🤖 CI/CD Auto-Deployment Pipeline

MyVetPaws is configured for continuous integration and automated deployment via **GitHub Actions**.

### How it Works
1. When you push a change to the `main` branch:
   * The runner checks out the codebase.
   * Node.js sets up and executes `npm ci` and `npm run build` to compile the optimized Tailwind CSS v4 assets.
   * `rsync` securely pushes the compiled assets and codebase over SSH to the production server.
   * Post-deploy commands run `composer install --no-dev --optimize-autoloader`, run database migrations (`php spark migrate`), and correct ownership permissions (`www-data:www-data`) for the `/writable/` directories.

### Setup Repository Secrets
Ensure the following GitHub Actions secrets are set up under **Settings > Secrets and variables > Actions**:
* `SSH_HOST`: The production IP address of the server (`72.61.143.83`).
* `SSH_USER`: The SSH connection user (`root`).
* `SSH_PRIVATE_KEY`: The ED25519 private key generated specifically for GitHub Actions.

---

## 📁 Key File Structure

```text
├── .github/workflows/deploy.yml   # CI/CD GitHub Action configuration
├── app/
│   ├── Config/                    # CodeIgniter core configuration files
│   ├── Controllers/               # Controllers (Visit, Invoice, Auth, etc.)
│   ├── Database/
│   │   ├── Migrations/            # DB Schema definitions
│   │   └── Seeds/                 # Sample data seeders
│   ├── Models/                    # Data models mapping SQL tables
│   └── Views/
│       ├── layouts/               # Main layout wrappers and Tailwind inputs
│       ├── visits/                # Visits views & Alpine.js interactive calendar
│       └── invoices/              # Invoice views and print templates
├── public/
│   └── css/app.css                # Compiled Tailwind CSS stylesheet
└── package.json                   # Tailwind CSS build scripts & dependencies
```

---

## 📄 License

This project is licensed under the MIT License.
