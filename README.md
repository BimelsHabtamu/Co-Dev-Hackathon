# Qelm Meda — Hackathon Submission Document
Project: Smart Inventory & Sales Management System  
Client: Qelem Meda Technologies  
Stack: Laravel 12 + Filament 3 · Vue 3 + Vite · SQLite  
Duration: 24 hours

---

## How to Run

```bash
# 1. Backend

cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve


# → http://localhost:8000
# → Admin panel: http://localhost:8000/admin

# 2. Frontend
cd frontend
npm install
npm run dev
# → http://localhost:5173


Sales Officer (Frontend)          Manager (Backend /admin)

1. Login → gets Sanctum token     1. Login → Filament session
2. Browse products (search/SKU)   2. Dashboard → live stats
3. Add items to cart              3. Manage Products / Users
4. Apply discount (0–100%)        4. View all Sales
5. System calculates:             5. Approve / Reject large sales
   - Subtotal = Qty × Price       6. Generate Reports (Daily/Monthly/Stock)
   - Discount Amount              7. Export PDF or Excel
   - VAT = 15% of taxable amount
   - Total = Taxable + VAT
6. Submit sale →
   IF total > 50,000 Birr:
     → status = pending_approval (manager must approve)
   ELSE:
     → status = completed
     → stock deducted immediately
7. View own sales history only"# CO-DEV_HACKATHON" 
"# CO-DEV_HACKATHON" 
