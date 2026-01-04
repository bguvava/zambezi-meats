/**
 * Vue Router Configuration
 *
 * Defines all application routes with authentication guards
 * and role-based access control.
 *
 * @requirement PROJ-INIT-013 Configure Vue Router with guards
 */
import { createRouter, createWebHistory } from "vue-router";
import { useAuthStore } from "@/stores/auth";

// Lazy-load route components for code splitting
const routes = [
  // ==========================================================================
  // PUBLIC ROUTES (Guest Layout)
  // ==========================================================================
  {
    path: "/",
    component: () => import("@/layouts/GuestLayout.vue"),
    children: [
      {
        path: "",
        name: "home",
        component: () => import("@/pages/HomePage.vue"),
        meta: { title: "Home" },
      },
      {
        path: "shop",
        name: "shop",
        component: () => import("@/pages/ShopPage.vue"),
        meta: { title: "Shop" },
      },
      {
        path: "shop/:slug",
        name: "product",
        component: () => import("@/pages/ProductPage.vue"),
        meta: { title: "Product" },
      },
      {
        path: "category/:slug",
        name: "category",
        component: () => import("@/pages/CategoryPage.vue"),
        meta: { title: "Category" },
      },
      {
        path: "cart",
        name: "cart",
        component: () => import("@/pages/CartPage.vue"),
        meta: { title: "Shopping Cart" },
      },
      {
        path: "about",
        name: "about",
        component: () => import("@/pages/AboutPage.vue"),
        meta: { title: "About Us" },
      },
      {
        path: "contact",
        name: "contact",
        component: () => import("@/pages/ContactPage.vue"),
        meta: { title: "Contact Us" },
      },
      {
        path: "delivery",
        name: "delivery",
        component: () => import("@/pages/DeliveryPage.vue"),
        meta: { title: "Delivery Information" },
      },
      {
        path: "faq",
        name: "faq",
        component: () => import("@/pages/FaqPage.vue"),
        meta: { title: "Frequently Asked Questions" },
      },
      {
        path: "shipping",
        name: "shipping",
        component: () => import("@/pages/ShippingPage.vue"),
        meta: { title: "Shipping & Returns" },
      },
      {
        path: "terms",
        name: "terms",
        component: () => import("@/pages/TermsPage.vue"),
        meta: { title: "Terms of Service" },
      },
      {
        path: "privacy",
        name: "privacy",
        component: () => import("@/pages/PrivacyPage.vue"),
        meta: { title: "Privacy Policy" },
      },
      {
        path: "blog",
        name: "blog",
        component: () => import("@/pages/BlogListPage.vue"),
        meta: { title: "Blog" },
      },
      {
        path: "blog/:slug",
        name: "blog-post",
        component: () => import("@/pages/BlogPostPage.vue"),
        meta: { title: "Blog Post" },
      },
    ],
  },

  // ==========================================================================
  // AUTH ROUTES (Guest Only)
  // ==========================================================================
  {
    path: "/login",
    name: "login",
    component: () => import("@/pages/auth/LoginPage.vue"),
    meta: { title: "Login", guest: true },
  },
  {
    path: "/register",
    name: "register",
    component: () => import("@/pages/auth/RegisterPage.vue"),
    meta: { title: "Register", guest: true },
  },
  {
    path: "/forgot-password",
    name: "forgot-password",
    component: () => import("@/pages/auth/ForgotPasswordPage.vue"),
    meta: { title: "Forgot Password", guest: true },
  },
  {
    path: "/reset-password/:token",
    name: "reset-password",
    component: () => import("@/pages/auth/ResetPasswordPage.vue"),
    meta: { title: "Reset Password", guest: true },
  },

  // ==========================================================================
  // CHECKOUT ROUTES (Auth Required)
  // ==========================================================================
  {
    path: "/checkout",
    name: "checkout",
    component: () => import("@/pages/CheckoutPage.vue"),
    meta: { title: "Checkout", requiresAuth: true },
  },
  {
    path: "/checkout/confirm",
    name: "checkout-confirm",
    component: () => import("@/components/checkout/PaymentReturnHandler.vue"),
    meta: { title: "Confirming Payment", requiresAuth: true },
  },
  {
    path: "/checkout/success/:orderNumber",
    name: "checkout-success",
    component: () => import("@/pages/CheckoutPage.vue"),
    meta: { title: "Order Confirmed", requiresAuth: true },
  },

  // ==========================================================================
  // CUSTOMER DASHBOARD ROUTES
  // ==========================================================================
  {
    path: "/customer",
    component: () => import("@/layouts/CustomerLayout.vue"),
    meta: { requiresAuth: true, role: "customer" },
    children: [
      {
        path: "",
        name: "customer-dashboard",
        component: () => import("@/pages/customer/DashboardPage.vue"),
        meta: { title: "My Account" },
      },
      {
        path: "orders",
        name: "customer-orders",
        component: () => import("@/pages/customer/OrdersPage.vue"),
        meta: { title: "My Orders" },
      },
      {
        path: "orders/:orderNumber",
        name: "customer-order-detail",
        component: () => import("@/pages/customer/OrderDetailPage.vue"),
        meta: { title: "Order Details" },
      },
      {
        path: "profile",
        name: "customer-profile",
        component: () => import("@/pages/customer/Profile.vue"),
        meta: { title: "My Profile" },
      },
      {
        path: "addresses",
        name: "customer-addresses",
        component: () => import("@/pages/customer/AddressesPage.vue"),
        meta: { title: "My Addresses" },
      },
      {
        path: "wishlist",
        name: "customer-wishlist",
        component: () => import("@/pages/customer/WishlistPage.vue"),
        meta: { title: "My Wishlist" },
      },
      {
        path: "notifications",
        name: "customer-notifications",
        component: () => import("@/pages/customer/NotificationsPage.vue"),
        meta: { title: "Notifications" },
      },
      {
        path: "support",
        name: "customer-support",
        component: () => import("@/pages/customer/SupportTicketsPage.vue"),
        meta: { title: "Support" },
      },
    ],
  },

  // ==========================================================================
  // STAFF DASHBOARD ROUTES
  // ==========================================================================
  {
    path: "/staff",
    component: () => import("@/layouts/StaffLayout.vue"),
    meta: { requiresAuth: true, role: "staff" },
    children: [
      {
        path: "",
        name: "staff-dashboard",
        component: () => import("@/pages/staff/DashboardPage.vue"),
        meta: { title: "Staff Dashboard" },
      },
      {
        path: "orders",
        name: "staff-orders",
        component: () => import("@/pages/staff/OrdersPage.vue"),
        meta: { title: "Order Queue" },
      },
      {
        path: "deliveries",
        name: "staff-deliveries",
        component: () => import("@/pages/staff/DeliveriesPage.vue"),
        meta: { title: "My Deliveries" },
      },
      {
        path: "stock",
        name: "staff-stock",
        component: () => import("@/pages/staff/StockCheckPage.vue"),
        meta: { title: "Stock Check" },
      },
      {
        path: "waste",
        name: "staff-waste",
        component: () => import("@/pages/staff/WasteLogPage.vue"),
        meta: { title: "Waste Log" },
      },
      {
        path: "messages",
        name: "staff-messages",
        component: () => import("@/pages/staff/MessagesPage.vue"),
        meta: { title: "Messages" },
      },
      {
        path: "invoices",
        name: "staff-invoices",
        component: () => import("@/pages/staff/InvoicesPage.vue"),
        meta: { title: "Invoices" },
      },
      {
        path: "invoices/:id",
        name: "staff-invoice-detail",
        component: () => import("@/pages/staff/InvoiceDetailPage.vue"),
        meta: { title: "Invoice Details" },
      },
    ],
  },

  // ==========================================================================
  // ADMIN DASHBOARD ROUTES
  // ==========================================================================
  {
    path: "/admin",
    component: () => import("@/layouts/AdminLayout.vue"),
    meta: { requiresAuth: true, role: "admin" },
    children: [
      {
        path: "",
        name: "admin-dashboard",
        component: () => import("@/pages/admin/DashboardPage.vue"),
        meta: { title: "Admin Dashboard" },
      },
      {
        path: "users",
        name: "admin-users",
        component: () => import("@/pages/admin/UsersPage.vue"),
        meta: { title: "User Management" },
      },
      {
        path: "products",
        name: "admin-products",
        component: () => import("@/pages/admin/ProductsPage.vue"),
        meta: { title: "Product Management" },
      },
      {
        path: "categories",
        name: "admin-categories",
        component: () => import("@/pages/admin/CategoriesPage.vue"),
        meta: { title: "Category Management" },
      },
      {
        path: "orders",
        name: "admin-orders",
        component: () => import("@/pages/admin/OrdersPage.vue"),
        meta: { title: "All Orders" },
      },
      {
        path: "inventory",
        name: "admin-inventory",
        component: () => import("@/pages/admin/InventoryPage.vue"),
        meta: { title: "Inventory Management" },
      },
      {
        path: "deliveries",
        name: "admin-deliveries",
        component: () => import("@/pages/admin/DeliveryPage.vue"),
        meta: { title: "Delivery Management" },
      },
      {
        path: "promotions",
        name: "admin-promotions",
        component: () => import("@/pages/admin/PromotionsPage.vue"),
        meta: { title: "Promotions Management" },
      },
      {
        path: "staff",
        name: "admin-staff",
        component: () => import("@/pages/admin/StaffPage.vue"),
        meta: { title: "Staff Management" },
      },
      {
        path: "activity-logs",
        name: "admin-activity-logs",
        component: () => import("@/pages/admin/ActivityLogsPage.vue"),
        meta: { title: "Activity Logs" },
      },
      {
        path: "reports",
        name: "admin-reports",
        component: () => import("@/pages/admin/ReportsPage.vue"),
        meta: { title: "Reports & Analytics" },
      },
      {
        path: "messages",
        name: "admin-messages",
        component: () => import("@/pages/admin/MessagesPage.vue"),
        meta: { title: "Messages" },
      },
      {
        path: "settings",
        name: "admin-settings",
        component: () => import("@/pages/admin/SettingsPage.vue"),
        meta: { title: "System Settings" },
      },
      {
        path: "invoices",
        name: "admin-invoices",
        component: () => import("@/pages/admin/InvoicesPage.vue"),
        meta: { title: "Invoices" },
      },
      {
        path: "invoices/:id",
        name: "admin-invoice-detail",
        component: () => import("@/pages/admin/InvoiceDetailPage.vue"),
        meta: { title: "Invoice Details" },
      },
    ],
  },

  // ==========================================================================
  // ERROR ROUTES
  // ==========================================================================
  {
    path: "/403",
    name: "forbidden",
    component: () => import("@/pages/errors/ForbiddenPage.vue"),
    meta: { title: "Access Denied" },
  },
  {
    path: "/:pathMatch(.*)*",
    name: "not-found",
    component: () => import("@/pages/errors/NotFoundPage.vue"),
    meta: { title: "Page Not Found" },
  },
];

// Create router instance
const router = createRouter({
  history: createWebHistory(),
  routes,
  scrollBehavior(to, from, savedPosition) {
    if (savedPosition) {
      return savedPosition;
    }
    return { top: 0 };
  },
});

// Navigation guards
router.beforeEach(async (to, from, next) => {
  const authStore = useAuthStore();

  // Wait for auth initialization to complete before checking routes
  // This prevents false negatives on page refresh
  if (!authStore.initialized) {
    try {
      await authStore.initialize();
    } catch (error) {
      // Initialization failed - user will be treated as guest
      console.log("Auth initialization during navigation:", error.message);
    }
  }

  // Update page title
  document.title = to.meta.title
    ? `${to.meta.title} | Zambezi Meats`
    : "Zambezi Meats";

  // Update last activity time for authenticated users (keep session alive)
  if (authStore.isAuthenticated) {
    authStore.updateLastActivity();
  }

  // Handle guest-only routes (login, register)
  if (to.meta.guest && authStore.isAuthenticated) {
    // Redirect authenticated users away from guest pages
    const redirectTo = authStore.isAdmin
      ? { name: "admin-dashboard" }
      : authStore.isStaff
      ? { name: "staff-dashboard" }
      : { name: "customer-dashboard" };
    return next(redirectTo);
  }

  // Handle protected routes
  if (to.meta.requiresAuth && !authStore.isAuthenticated) {
    return next({ name: "login", query: { redirect: to.fullPath } });
  }

  // Handle role-based access
  if (to.meta.role) {
    const userRole = authStore.userRole;

    if (to.meta.role === "admin" && !authStore.isAdmin) {
      return next({ name: "forbidden" });
    }

    if (to.meta.role === "staff" && !authStore.isStaff) {
      return next({ name: "forbidden" });
    }

    // Customers can access customer routes, but also staff/admin can
    if (to.meta.role === "customer" && !authStore.isAuthenticated) {
      return next({ name: "login", query: { redirect: to.fullPath } });
    }
  }

  next();
});

export default router;
