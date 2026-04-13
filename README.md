# Foodoos Web Application

Foodoos Web is a comprehensive, feature-rich food delivery and restaurant management platform built with Laravel format. The system is designed specifically to handle robust multi-tenant restaurant operations, incorporating Point of Sale (POS), online food ordering, dining table reservations, and delivery operations all into one unified cohesive platform. 

It powers an entire ecosystem for Admins, Customers, Delivery Boys, Chefs, and Staff members with robust access control.

---

## 🛠 Tech Stack & Dependencies

- **Framework:** Laravel 9.x
- **Language:** PHP ^8.0.2
- **Authentication:** Laravel Sanctum (Token-based API auth)
- **Roles & Permissions:** Spatie Permission
- **Media Management:** Spatie Media Library
- **PDF Generation:** Barryvdh Laravel DomPDF
- **Database:** MySQL / MariaDB

---

## 🧑‍🤝‍🧑 User Roles

The platform runs on a robust Role-Based Access Control (RBAC) system defined via Spatie. Major roles include:

1. **Admin** - Superuser with complete access to settings, reports, financial analytics, access controls, and overall system management.
2. **Customer** - End-users who can browse restaurants/branches, apply coupons/offers, place online orders, reserve tables, track orders, and manage addresses.
3. **Delivery Boy** - Riders who handle order dispatches. They can view assigned orders, update order status to delivered, and manage their profile.
4. **Waiter** - Services physical dining tables, manages table orders, and interfaces with the kitchen.
5. **Chef** - Receives realtime updates on pending orders and marks items as prepared.
6. **Branch Manager** - Oversees a specific branch/location, managing local staff, inventory, POS operations, and branch-specific reporting.
7. **POS Operator** - Focused role for counter staff taking live POS orders.
8. **Staff** - General staff with flexible permission grants.

---

## 🚀 Core Features

### 1. Robust Ordering Ecosystem
- **Online Food Delivery:** Customers can order through a dedicated frontend/app. Includes cart, time-slot selection, and real-time status.
- **POS (Point of Sale):** A fast, unified POS interface for operators to create in-house walk-in orders.
- **Dining & Table Booking:** Dine-in booking system with QR codes and table-specific ordering.
- **Guest Checkout:** Ability to order without forcing a full account creation.

### 2. Item & Menu Catalog
- **Categories & Addons:** Group items. Add optional or required Extras and Addons.
- **Variations & Attributes:** Size, Crust, Spice level mapping (e.g., Small, Medium, Large).
- **Dynamic Menu Templates:** Menu sections and dynamic templates to control frontend display.

### 3. Payment & SMS Integrations
Diverse global and regional payment integrations available:
- **Payments:** Stripe, PayPal, Razorpay, Paytm, Cashfree, Midtrans, Mollie, Pesapal, Paystack, PhonePe, Bkash.
- **SMS Gateways:** Twilio, Vonage (Nexmo) for OTPs and notifications.

### 4. Promotion & Marketing
- **Coupons:** Advanced coupon engine with flat/percentage discounts and limits.
- **Offers:** Direct item-based promotions (e.g., "Deal of the Day").
- **Push Notifications:** Firebase-integrated push notifications to engage customers.
- **Mail & SMS Alerts:** Automated transactional alerts for order updates. 

### 5. Multi-Branch & Spatial Operations
- Manage multiple branches from a single admin panel.
- Branch-specific tracking and access scopes for employees.

### 6. Analytics & Dashboards
- **Dashboards:** Metrics on Total Sales, Orders, Customers, Top Selling Items.
- **Reports:** Items reports, credit balance reports, sales reports exportable as CSV/PDF.

---

## 📂 Project Structure

- `app/Http/Controllers/Admin/`: Contains all administration logic (Settings, Sales, Employee Management).
- `app/Http/Controllers/Api/` & `Frontend/`: The RESTful API layer consumed by mobile applications and the web frontend.
- `app/Http/Controllers/Table/`: Endpoints dealing exclusively with table/dine-in operations.
- `app/Models/`: Eloquent models mapping database relationships (e.g., `Order`, `User`, `Item`, `DeliveryBoy`).
- `database/seeders/`: Extensive seeders setting up initial configurations, roles, permissions, currencies, and demo data.
- `routes/api.php`: The API routing heart of the project.

---

## ⚙️ Installation & Setup

### Prerequisites
- PHP >= 8.0.2
- Composer
- MySQL >= 5.7 (or MariaDB)
- Web Server (Apache/Nginx)

### Steps

1. **Clone the repository** (if using Git) or unzip the file.
2. **Install Composer Dependencies:**
   ```bash
   composer install
   ```
3. **Environment Setup:**
   ```bash
   cp .env.example .env
   ```
   Update `.env` with your DB credentials, App URL, and other keys.
4. **Generate Application Key:**
   ```bash
   php artisan key:generate
   ```
5. **Run Migrations & Seeders:**
   ```bash
   php artisan migrate --seed
   ```
   *(Running seeders is highly recommended to establish default Admin accounts, Roles, Currencies, and Settings.)*
6. **Link Storage:**
   If using local file storage for Spatie Medialibrary:
   ```bash
   php artisan storage:link
   ```
7. **Serve the Application:**
   ```bash
   php artisan serve
   ```

*(Alternatively, use the provided `install.sh` if deploying on a configured Linux environment).*

---

## 🛡️ Security

- **Sanctum Authentication:** Secure token exchange for all API endpoints.
- **Granular Permissions:** Every admin route runs through Spatie's permission middleware (e.g., `middleware('permission:items_edit')`), ensuring users can only interact with modules they govern.

---

## 📜 Code of Conduct & Contributing
Contributions are essentially merge requests. Please follow standard Laravel styling (PSR-12) and ensure endpoints are adequately protected via Request validations and Sanctum middlewares.

## 📄 License
This project utilizes various open-source packages. Please refer to individual packages in `composer.json` for specific licensing.
