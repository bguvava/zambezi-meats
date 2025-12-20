/**
 * Zambezi Meats - Main Application Entry Point
 *
 * Initializes Vue application with Pinia, Vue Router, and global plugins.
 *
 * @requirement PROJ-INIT-002 Create Vue.js 3 + Vite frontend project
 */
import { createApp } from "vue";
import { createPinia } from "pinia";
import router from "./router";
import App from "./App.vue";
import "./style.css";

// Import directives
import { clickAway } from "./directives/clickAway";

// Create Vue application
const app = createApp(App);

// Register global directives
app.directive("click-away", clickAway);

// Initialize Pinia store
const pinia = createPinia();
app.use(pinia);

// Initialize Vue Router
app.use(router);

// Mount application
app.mount("#app");

// Initialize stores after mounting
import { useAuthStore, useCartStore, useCurrencyStore } from "./stores";

const authStore = useAuthStore();
const cartStore = useCartStore();
const currencyStore = useCurrencyStore();

// Load persisted data
cartStore.loadFromStorage();
currencyStore.loadFromStorage();

// Initialize auth state
authStore.initialize().catch((error) => {
  console.log("User not authenticated:", error.message);
});
