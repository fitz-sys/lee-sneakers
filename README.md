# LEE Sneakers - Complete E-Commerce System

A full-featured e-commerce website for premium sneakers with separate admin and user panels.

## 🚀 Features

### User Features:
- ✅ User Registration & Login
- ✅ Browse products by category (Featured, Best Seller, Men, Women)
- ✅ View product details with images, prices, and ratings
- ✅ Sale badges and discount prices
- ✅ Responsive design for all devices

### Admin Features:
- ✅ Admin Dashboard with sales statistics
- ✅ Product Management (Add, Edit, Delete)
- ✅ Image Upload functionality
- ✅ Monitor sales and user activity
- ✅ Sales chart (last 7 days)
- ✅ Order management

## 📁 Project Structure

```
LEE_Sneakers/
├── config/
│   └── database.php          # Database configuration
├── includes/
│   ├── login.php             # Login handler
│   ├── signup.php            # Signup handler
│   └── logout.php            # Logout handler
├── admin/
│   ├── index.php             # Admin dashboard
│   ├── products.php          # Product management
│   ├── add_product.php       # Add product handler
│   ├── edit_product.php      # Edit product page
│   └── delete_product.php    # Delete product handler
├── user/
│   └── index.php             # User shopping panel
├── uploads/
│   └── products/             # Product images folder
├── css/
│   └── style.css             # Complete stylesheet
├── index.php                 # Login/Signup page
└── database.sql              # Database schema
```

## 🛠️ Installation Guide

### Step 1: Install XAMPP
1. Download XAMPP from [https://www.apachefriends.org/](https://www.apachefriends.org/)
2. Install XAMPP and start Apache and MySQL services

### Step 2: Create Database
1. Open phpMyAdmin: `http://localhost/phpmyadmin`
2. Create a new database named `lee_sneakers`
3. Import the `database.sql` file:
   - Click on the `lee_sneakers` database
   - Go to "Import" tab
   - Choose the `database.sql` file
   - Click "Go"

### Step 3: Setup Project Files
1. Copy the entire project folder to: `C:\xampp\htdocs\LEE_Sneakers\`
2. Create the uploads folder if it doesn't exist:
   - Navigate to `C:\xampp\htdocs\LEE_Sneakers\`
   - Create folder: `uploads/products/`
   - Set folder permissions to allow write access

### Step 4: Configure Database
1. Open `config/database.php`
2. Update database credentials if needed:
   ```php
   define('DB_HOST', 'localhost');
   define('DB_USER', 'root');
   define('DB_PASS', '');  // Leave empty for default XAMPP
   define('DB_NAME', 'lee_sneakers');
   ```

### Step 5: Access the Website
1. Open your browser
2. Go to: `http://localhost/LEE_Sneakers/`
3. You should see the login page

## 🔐 Default Login Credentials

### Admin Account:
- **Username:** admin
- **Password:** admin123

### Test User Account:
- Create your own by clicking "Sign Up" on the login page

## 📝 Usage Instructions

### For Users:
1. Sign up for a new account or login
2. Browse products by category
3. View product details, ratings, and prices
4. Look for sale items with special badges

### For Admins:
1. Login with admin credentials
2. Access Dashboard to view statistics
3. Manage products:
   - Click "Products" in navigation
   - Add new products with images
   - Edit existing products
   - Delete products
4. Monitor sales and orders

## 🎨 Color Scheme

- **Primary:** #FEC700 (Yellow Gold)
- **Secondary:** #02462E (Dark Green)
- **Background:** #000435 (Navy Blue)
- **Accent:** #064734 (Forest Green)

## 📱 Responsive Design

The website is fully responsive and works on:
- Desktop (1920px and above)
- Laptop (1024px - 1919px)
- Tablet (768px - 1023px)
- Mobile (320px - 767px)

## 🔧 Technical Stack

- **Frontend:** HTML5, CSS3, Bootstrap 5, JavaScript
- **Backend:** PHP 7.4+
- **Database:** MySQL (via XAMPP)
- **Icons:** Font Awesome 6.4.0
- **Charts:** Chart.js

## 📂 File Descriptions

### Core Files:

**index.php** - Main login/signup page
- Handles user authentication
- Toggle between login and signup forms
- Session management

**config/database.php** - Database connection
- MySQL connection setup
- Helper functions for security
- Session initialization

### Admin Files:

**admin/index.php** - Dashboard
- Sales statistics (Products, Users, Orders, Revenue)
- Sales chart (last 7 days)
- Recent orders table
- Quick action buttons

**admin/products.php** - Product listing
- View all products in table format
- Add, edit, delete functionality
- Product image preview

**admin/add_product.php** - Add new product
- Form validation
- Image upload (JPG, PNG, WEBP, JFIF)
- Maximum file size: 5MB
- Automatic image filename generation

**admin/edit_product.php** - Edit existing product
- Load product details
- Update product information
- Change product image (optional)

**admin/delete_product.php** - Delete product
- Remove product from database
- Delete associated image file

### User Files:

**user/index.php** - Shopping interface
- Display products by category
- Hero section with animation
- Product cards with hover effects
- Footer with social links

### Include Files:

**includes/login.php** - Login authentication
- Password verification
- Role-based redirection
- Session creation

**includes/signup.php** - User registration
- Form validation
- Password hashing
- Email validation
- Duplicate username/email check

**includes/logout.php** - Session termination
- Destroy session data
- Redirect to login page

## 🗄️ Database Tables

### users
- `id` - Primary key
- `username` - Unique username
- `email` - Unique email
- `password` - Hashed password
- `full_name` - User's full name
- `role` - 'admin' or 'user'
- `created_at` - Registration timestamp

### products
- `id` - Primary key
- `name` - Product name
- `image` - Image filename
- `price` - Current price
- `original_price` - Original price (for sales)
- `category` - Featured, Best Seller, Men, Women
- `rating` - 1.0 to 5.0 stars
- `sale` - Boolean (on sale or not)
- `stock` - Available quantity
- `description` - Product description
- `created_at` - Creation timestamp

### orders
- `id` - Primary key
- `user_id` - Foreign key to users
- `total_amount` - Order total
- `status` - pending, processing, completed, cancelled
- `shipping_address` - Delivery address
- `payment_method` - Payment type
- `created_at` - Order timestamp

### order_items
- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `quantity` - Items ordered
- `price` - Price at time of order

## 🔒 Security Features

- ✅ Password hashing (bcrypt)
- ✅ SQL injection protection (prepared statements)
- ✅ XSS protection (htmlspecialchars)
- ✅ File upload validation
- ✅ Session-based authentication
- ✅ Role-based access control
- ✅ CSRF protection ready

## 🐛 Troubleshooting

### Database Connection Error:
```
Error: Connection failed
Solution: Check if MySQL is running in XAMPP Control Panel
```

### Image Upload Error:
```
Error: Failed to upload image
Solution: 
1. Check if uploads/products/ folder exists
2. Set folder permissions to 777
3. Check file size (max 5MB)
4. Check file type (JPG, PNG, WEBP, JFIF only)
```

### Page Not Found:
```
Error: 404 Not Found
Solution: Ensure project is in htdocs folder
Access: http://localhost/LEE_Sneakers/
```

### Session Error:
```
Error: Session not started
Solution: Check if session_start() is enabled in php.ini
```

## 📈 Future Enhancements

- [ ] Shopping cart functionality
- [ ] Checkout and payment integration
- [ ] Order tracking system
- [ ] Product search and filters
- [ ] Customer reviews and ratings
- [ ] Email notifications
- [ ] Wishlist feature
- [ ] Size and color variants
- [ ] Inventory management
- [ ] Sales reports and analytics

## 📞 Support

For issues or questions:
1. Check the troubleshooting section
2. Review XAMPP logs: `C:\xampp\apache\logs\error.log`
3. Check PHP error logs
4. Verify database connections

## 📄 License

This project is for educational purposes.

## 👨‍💻 Developer Notes

### Adding New Categories:
1. Update database enum in products table
2. Add category in admin forms
3. Create new section in user/index.php

### Changing Upload Limits:
Edit `php.ini`:
```ini
upload_max_filesize = 10M
post_max_size = 10M
```

### Customizing Colors:
Edit `css/style.css` and update color variables:
```css
Primary: #FEC700
Secondary: #02462E
Background: #000435
```

## 🎯 Project Goals Achieved

✅ Separate login for admin and user  
✅ Sign-up functionality  
✅ Image upload for products  
✅ Admin dashboard with statistics  
✅ Sales and user monitoring  
✅ XAMPP/MySQL database integration  
✅ Separated files (HTML, CSS, JS, PHP)  
✅ Complete product management (CRUD)  
✅ Responsive design  
✅ Security implementation  

---

**Version:** 1.0.0  
**Last Updated:** October 15, 2025  
**Developed for:** LEE Sneakers E-Commerce Platform