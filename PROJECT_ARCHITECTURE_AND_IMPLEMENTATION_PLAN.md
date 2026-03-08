# Smart Online Cabana Booking and Management System
## Architecture and Implementation Plan

### STEP 1: Project Understanding
**Overview:**
The "Smart Online Cabana Booking and Management System" is a dedicated SaaS-like platform tailored for cabana and small resort owners. It aims to digitize the booking process, shifting away from manual or ad-hoc reservations to a streamlined, automated online system. 

**Core Dynamics:**
- **Customers** will have a frictionless experience finding cabanas, checking real-time availability, booking their stay, securely paying via PayHere, and leaving post-stay feedback.
- **Administrators/Owners** will have full control over their inventory, pricing, availability calendars, and a comprehensive dashboard to track revenue, occupancy, and upcoming bookings.

**Technical Approach:**
The system will be built using a **Decoupled Architecture**. A Laravel backend will act solely as a RESTful API, communicating using JSON. A Vue.js Single Page Application (SPA) will consume this API, providing a fast, reactive, and App-like user experience. Laravel Sanctum will bridge the secure authentication between these two layers.

---

### STEP 2: System Architecture
#### High-Level Architecture
- **Client Tier**: Web Browser (Mobile/Desktop responsive).
- **Presentation Tier**: Vue.js SPA (HTML5, CSS3, JavaScript/TypeScript).
- **Application Tier**: Laravel REST API.
- **Data Tier**: MySQL Database.
- **External Services**: PayHere (Payment Gateway).

#### Backend Architecture (Laravel)
- **Controllers**: Handle HTTP requests, input validation, and route parameters.
- **Service Layer**: House the core business logic (e.g., availability calculation, price computation) to keep controllers thin.
- **Eloquent ORM & Models**: Map database tables to PHP objects, handling relationships and data retrieval.
- **API Resources/Transformers**: Format database models into standardized JSON responses.

#### Frontend Architecture (Vue.js)
- **Framework**: Vue 3 (Composition API).
- **State Management**: Pinia (for managing global state like User Sessions and Shopping Cart/Draft Bookings).
- **Routing**: Vue Router for SPA navigation.
- **HTTP Client**: Axios configured with Interceptors to handle Sanctum CSRF / Bearer tokens automatically.

#### API Architecture
- Strictly **RESTful**.
- Uses standard HTTP methods (GET, POST, PUT, DELETE).
- Standardized error handling and status codes (200, 201, 401, 403, 404, 422, 500).

#### Folder Structure Overview
The project will be physically divided into two main repositories or folders:
```text
/smart-cabana-system
  /backend (Laravel)
    /app
      /Http/Controllers/Api
      /Models
      /Services
    /routes/api.php
    ...
  /frontend (Vue.js)
    /src
      /assets
      /components
      /views
      /stores (Pinia)
      /services (API calls)
      /router
    ...
```

---

### STEP 3: Database Design
Below are the primary tables required and their relationships:

1. **`users`**
   - `id`, `name`, `email`, `password`, `role` (admin/customer), `phone`, `timestamps`
2. **`cabanas`**
   - `id`, `name`, `description`, `price_per_night`, `max_guests`, `location`, `status` (active/inactive), `timestamps`
3. **`cabana_images`**
   - `id`, `cabana_id` (FK), `image_path`, `is_primary` (boolean), `timestamps`
4. **`cabana_blocks` (Availability Control)**
   - `id`, `cabana_id` (FK), `start_date`, `end_date`, `reason` (maintenance, private use)
5. **`bookings`**
   - `id`, `user_id` (FK), `cabana_id` (FK), `check_in`, `check_out`, `guests_count`, `total_amount`, `booking_status` (pending, confirmed, cancelled, completed), `payment_status` (pending, paid, failed), `timestamps`
6. **`payments`**
   - `id`, `booking_id` (FK), `transaction_id` (PayHere reference), `amount`, `payment_method`, `status` (success, failed), `timestamps`
7. **`reviews`**
   - `id`, `user_id` (FK), `cabana_id` (FK), `booking_id` (FK), `rating` (1-5), `comment`, `timestamps`

**Core Relationships:**
- User Has Many Bookings & Reviews.
- Cabana Has Many Images, Blocks, Bookings, & Reviews.
- Booking Belongs To Cabana & User, Has One Payment, Has One Review.

---

### STEP 4: Core System Modules
1. **Authentication Module**: Registration, Login, Logout, Profile Management, Role Verification (Sanctum SPA Authentication).
2. **Cabana Management Module**: Admin CRUD operations for cabanas, handling multiple image uploads, and defining capacities and pricing.
3. **Availability Management Module**: Core engine that checks date overlaps against existing `bookings` and admin `cabana_blocks` to determine if a cabana is bookable for a given date range.
4. **Booking System Module**: The customer checkout flow determining total cost, capturing guest details, and creating a pending reservation.
5. **Payment System Module**: Integration with PayHere; initiating the payment modal, and a backend webhook listener to automatically update booking statuses upon successful payment.
6. **Review System Module**: Mechanism for customers to rate completed stays, and for the system to aggregate and display average ratings on cabana listings.
7. **Admin Dashboard Module**: Analytical reporting showing total revenue, occupancy rates, upcoming check-ins, and recent bookings.

---

### STEP 5: API Endpoints (RESTful Structure)

**Auth & Profile**
- `POST /api/register`
- `POST /api/login`
- `POST /api/logout`
- `GET /api/user` (Get authenticated user profile)

**Public Data (Customers)**
- `GET /api/cabanas` (List active cabanas with filters/pagination)
- `GET /api/cabanas/{id}` (Get single cabana details)
- `GET /api/cabanas/{id}/reviews` (Get reviews)

**Availability**
- `POST /api/cabanas/{id}/check-availability` (Validate specific dates)
- `GET /api/cabanas/{id}/calendar` (Fetch booked/blocked dates for UI rendering)

**Customer Actions (Requires Auth)**
- `POST /api/bookings` (Create new booking)
- `GET /api/bookings` (Get my booking history)
- `GET /api/bookings/{id}` (Get single booking details)
- `POST /api/bookings/{id}/reviews` (Leave a review)

**Payments**
- `POST /api/payments/initiate` (Generate PayHere hash/parameters)
- `POST /api/payments/payhere-webhook` (Server-to-Server callback from PayHere)

**Admin Operations (Requires Admin Role)**
- `POST /api/admin/cabanas` (Create cabana)
- `PUT /api/admin/cabanas/{id}` (Update cabana)
- `DELETE /api/admin/cabanas/{id}` (Delete/Deactivate cabana)
- `POST /api/admin/cabanas/{id}/images` (Upload images)
- `DELETE /api/admin/images/{id}` (Remove image)
- `POST /api/admin/cabanas/{id}/blocks` (Block dates)
- `GET /api/admin/bookings` (View all system bookings)
- `PUT /api/admin/bookings/{id}/status` (Manually override booking status)
- `GET /api/admin/dashboard/stats` (Fetch dashboard aggregates)

---

### STEP 6: Implementation Roadmap

- **Phase 1 – Project Setup**
  - Initialize Laravel API project and Vue.js SPA project.
  - Setup MySQL database connection.
  - Setup basic project structure, CORS, and Git repository.
- **Phase 2 – Database & Models**
  - Create all migrations, models, and relationships.
  - Create model factories and seeders for realistic testing data.
- **Phase 3 – Authentication**
  - Set up Laravel Sanctum.
  - Build Auth API endpoints.
  - Build Vue login/register screens and wire up Pinia Auth store.
- **Phase 4 – Cabana Management (Admin)**
  - Build API for CRUD and Image uploads.
  - Build Vue Admin layout and Cabana management interfaces.
- **Phase 5 – Public Catalog & Availability**
  - Build public Cabana listing and detail pages in Vue.
  - Implement availability checking logic in backend.
  - Build an interactive calendar on the frontend.
- **Phase 6 – Booking System**
  - Build the checkout/booking form.
  - Implement backend booking validation and creation (pending status).
  - Build customer booking history views.
- **Phase 7 – Payment Integration**
  - Register PayHere Sandbox.
  - Integrate PayHere checkout script on frontend.
  - Securely generate payment hashes on backend.
  - Implement the webhook to confirm payments automatically.
- **Phase 8 – Admin Booking Management & Dashboard**
  - Build Admin views to see all bookings and manage statuses.
  - Build the graphical Admin Dashboard with key statistics.
- **Phase 9 – Review System**
  - Allow customers to leave reviews on completed bookings.
  - Display aggregated reviews on the public cabana screens.
- **Phase 10 – Final Polish & Testing**
  - End-to-end testing of the booking flow.
  - UI/UX refinements, responsive design checks.
  - Security review (Route protection, validation).
