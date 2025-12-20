<script setup>
/**
 * Footer Component
 *
 * Site footer with links, social media, and developer credits.
 *
 * @requirement LAND-010 Footer with navigation and credits
 */
import { RouterLink } from 'vue-router'

const currentYear = new Date().getFullYear()

const footerLinks = {
  shop: [
    { name: 'All Products', to: '/shop' },
    { name: 'Beef', to: '/category/beef' },
    { name: 'Lamb', to: '/category/lamb' },
    { name: 'Poultry', to: '/category/poultry' },
    { name: 'Pork', to: '/category/pork' },
    { name: 'Specialty', to: '/category/specialty' }
  ],
  company: [
    { name: 'About Us', to: '/about' },
    { name: 'Contact', to: '/contact' },
    { name: 'Delivery Zones', to: '/delivery' },
    { name: 'Careers', to: '/careers' },
    { name: 'Blog', to: '/blog' }
  ],
  support: [
    { name: 'FAQ', to: '/faq' },
    { name: 'Shipping & Returns', to: '/shipping' },
    { name: 'Track Order', to: '/track-order' },
    { name: 'Terms of Service', to: '/terms' },
    { name: 'Privacy Policy', to: '/privacy' }
  ],
  account: [
    { name: 'Login', to: '/login' },
    { name: 'Register', to: '/register' },
    { name: 'My Orders', to: '/customer/orders' },
    { name: 'Wishlist', to: '/customer/wishlist' }
  ]
}

const socialLinks = [
  { name: 'Facebook', icon: 'facebook', href: 'https://www.facebook.com/share/17hrbEMpKY/' },
  { name: 'Instagram', icon: 'instagram', href: 'https://www.instagram.com/zambezi_meats?igsh=OXZkNjVvb2w2enll' },
  { name: 'TikTok', icon: 'tiktok', href: 'https://www.tiktok.com/@zambezi.meats?_r=1&_t=ZS-92Jw9xNcw8O' }
]
</script>

<template>
  <footer class="bg-secondary-700 text-white">
    <!-- Main Footer -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
      <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8">
        <!-- Brand Column -->
        <div class="col-span-2 md:col-span-4 lg:col-span-1">
          <RouterLink to="/" class="inline-flex items-center gap-2 mb-6">
            <img
              src="/images/logo.png"
              alt="Zambezi Meats"
              class="w-10 h-10 object-contain rounded-full"
            />
            <span class="text-xl font-bold text-white">Zambezi Meats</span>
          </RouterLink>
          <p class="text-gray-300 text-sm mb-6">
            Premium quality Australian meats, hand-selected by expert butchers and delivered fresh to your doorstep.
          </p>
          <!-- Social Links -->
          <div class="flex gap-3">
            <a
              v-for="social in socialLinks"
              :key="social.name"
              :href="social.href"
              target="_blank"
              rel="noopener noreferrer"
              class="w-10 h-10 bg-white/10 rounded-full flex items-center justify-center hover:bg-primary-600 transition-colors"
              :title="social.name"
            >
              <!-- Facebook -->
              <svg v-if="social.icon === 'facebook'" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z" />
              </svg>
              <!-- Instagram -->
              <svg v-else-if="social.icon === 'instagram'" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073zm0 5.838c-3.403 0-6.162 2.759-6.162 6.162s2.759 6.163 6.162 6.163 6.162-2.759 6.162-6.163c0-3.403-2.759-6.162-6.162-6.162zm0 10.162c-2.209 0-4-1.79-4-4 0-2.209 1.791-4 4-4s4 1.791 4 4c0 2.21-1.791 4-4 4zm6.406-11.845c-.796 0-1.441.645-1.441 1.44s.645 1.44 1.441 1.44c.795 0 1.439-.645 1.439-1.44s-.644-1.44-1.439-1.44z" />
              </svg>
              <!-- TikTok -->
              <svg v-else-if="social.icon === 'tiktok'" class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M19.59 6.69a4.83 4.83 0 0 1-3.77-4.25V2h-3.45v13.67a2.89 2.89 0 0 1-5.2 1.74 2.89 2.89 0 0 1 2.31-4.64 2.93 2.93 0 0 1 .88.13V9.4a6.84 6.84 0 0 0-1-.05A6.33 6.33 0 0 0 5 20.1a6.34 6.34 0 0 0 10.86-4.43v-7a8.16 8.16 0 0 0 4.77 1.52v-3.4a4.85 4.85 0 0 1-1-.1z" />
              </svg>
            </a>
          </div>
        </div>

        <!-- Shop Links -->
        <div>
          <h3 class="text-white font-semibold mb-4">Shop</h3>
          <ul class="space-y-3">
            <li v-for="link in footerLinks.shop" :key="link.name">
              <RouterLink
                :to="link.to"
                class="text-gray-400 hover:text-white transition-colors text-sm"
              >
                {{ link.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Company Links -->
        <div>
          <h3 class="text-white font-semibold mb-4">Company</h3>
          <ul class="space-y-3">
            <li v-for="link in footerLinks.company" :key="link.name">
              <RouterLink
                :to="link.to"
                class="text-gray-400 hover:text-white transition-colors text-sm"
              >
                {{ link.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Support Links -->
        <div>
          <h3 class="text-white font-semibold mb-4">Support</h3>
          <ul class="space-y-3">
            <li v-for="link in footerLinks.support" :key="link.name">
              <RouterLink
                :to="link.to"
                class="text-gray-400 hover:text-white transition-colors text-sm"
              >
                {{ link.name }}
              </RouterLink>
            </li>
          </ul>
        </div>

        <!-- Account Links -->
        <div class="hidden lg:block">
          <h3 class="text-white font-semibold mb-4">Account</h3>
          <ul class="space-y-3">
            <li v-for="link in footerLinks.account" :key="link.name">
              <RouterLink
                :to="link.to"
                class="text-gray-400 hover:text-white transition-colors text-sm"
              >
                {{ link.name }}
              </RouterLink>
            </li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Bottom Bar -->
    <div class="border-t border-white/10">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
        <div class="flex flex-col md:flex-row justify-between items-center gap-4">
          <p class="text-gray-400 text-sm text-center md:text-left">
            © {{ currentYear }} Zambezi Meats. All rights reserved.
          </p>

          <!-- Payment Methods -->
          <div class="flex items-center gap-3">
            <span class="text-gray-500 text-xs">We accept:</span>
            <div class="flex gap-2 items-center">
              <!-- Visa -->
              <div class="w-10 h-6 bg-white rounded flex items-center justify-center" title="Visa">
                <svg class="h-4" viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M44 11H4C1.79 11 0 12.79 0 15V33C0 35.21 1.79 37 4 37H44C46.21 37 48 35.21 48 33V15C48 12.79 46.21 11 44 11Z" fill="#1A1F71"/>
                  <path d="M19.42 26.94L21.08 20.13H18.43L16.77 26.94H19.42ZM28.85 20.36C28.35 20.16 27.55 19.94 26.55 19.94C23.96 19.94 22.13 21.27 22.12 23.16C22.09 24.56 23.42 25.35 24.42 25.82C25.45 26.31 25.8 26.62 25.8 27.06C25.79 27.72 25 28.02 24.26 28.02C23.21 28.02 22.65 27.86 21.8 27.48L21.45 27.31L21.08 29.62C21.7 29.91 22.86 30.16 24.07 30.17C26.82 30.17 28.61 28.86 28.64 26.84C28.65 25.72 27.97 24.87 26.49 24.17C25.57 23.72 25.01 23.41 25.01 22.93C25.02 22.49 25.51 22.04 26.56 22.04C27.42 22.02 28.05 22.22 28.53 22.42L28.77 22.53L29.13 20.29L28.85 20.36ZM35.85 20.13H33.84C33.22 20.13 32.75 20.31 32.47 20.93L28.59 29.99H31.33L31.87 28.49H35.13L35.45 29.99H37.91L35.85 20.13ZM32.62 26.44C32.82 25.93 33.68 23.7 33.68 23.7C33.67 23.72 33.88 23.18 34.01 22.84L34.18 23.63C34.18 23.63 34.71 26.02 34.82 26.44H32.62ZM15.1 20.13L12.53 26.27L12.25 24.91C11.75 23.27 10.2 21.47 8.47 20.57L10.81 29.98H13.58L17.88 20.13H15.1Z" fill="#FFFFFF"/>
                  <path d="M10.2 20.13H6.06L6 20.33C9.31 21.17 11.55 23.27 12.24 24.91L11.4 20.94C11.26 20.33 10.8 20.15 10.2 20.13Z" fill="#FAA61A"/>
                </svg>
              </div>
              <!-- Mastercard -->
              <div class="w-10 h-6 bg-white rounded flex items-center justify-center" title="Mastercard">
                <svg class="h-4" viewBox="0 0 48 48" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="15" cy="24" r="10" fill="#EB001B"/>
                  <circle cx="33" cy="24" r="10" fill="#F79E1B"/>
                  <path d="M24 16.5C26.4 18.4 28 21.5 28 24C28 26.5 26.4 29.6 24 31.5C21.6 29.6 20 26.5 20 24C20 21.5 21.6 18.4 24 16.5Z" fill="#FF5F00"/>
                </svg>
              </div>
              <!-- PayPal -->
              <div class="w-12 h-6 bg-white rounded flex items-center justify-center" title="PayPal">
                <svg class="h-4" viewBox="0 0 124 33" xmlns="http://www.w3.org/2000/svg">
                  <path fill="#253B80" d="M46.211 6.749h-6.839a.95.95 0 0 0-.939.802l-2.766 17.537a.57.57 0 0 0 .564.658h3.265a.95.95 0 0 0 .939-.803l.746-4.73a.95.95 0 0 1 .938-.803h2.165c4.505 0 7.105-2.18 7.784-6.5.306-1.89.013-3.375-.872-4.415-.972-1.142-2.696-1.746-4.985-1.746zM47 13.154c-.374 2.454-2.249 2.454-4.062 2.454h-1.032l.724-4.583a.57.57 0 0 1 .563-.481h.473c1.235 0 2.4 0 3.002.704.359.42.469 1.044.332 1.906z"/>
                  <path fill="#179BD7" d="M66.654 13.075h-3.275a.57.57 0 0 0-.563.481l-.145.916-.229-.332c-.709-1.029-2.29-1.373-3.868-1.373-3.619 0-6.71 2.741-7.312 6.586-.313 1.918.132 3.752 1.22 5.031.998 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 0 0 .562.66h2.95a.95.95 0 0 0 .939-.803l1.77-11.209a.568.568 0 0 0-.561-.658zm-4.565 6.374c-.316 1.871-1.801 3.127-3.695 3.127-.951 0-1.711-.305-2.199-.883-.484-.574-.668-1.391-.514-2.301.295-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.499.589.697 1.411.554 2.317z"/>
                  <path fill="#253B80" d="M84.096 13.075h-3.291a.954.954 0 0 0-.787.417l-4.539 6.686-1.924-6.425a.953.953 0 0 0-.912-.678h-3.234a.57.57 0 0 0-.541.754l3.625 10.638-3.408 4.811a.57.57 0 0 0 .465.9h3.287a.949.949 0 0 0 .781-.408l10.946-15.8a.57.57 0 0 0-.468-.895z"/>
                  <path fill="#179BD7" d="M94.992 6.749h-6.84a.95.95 0 0 0-.938.802l-2.766 17.537a.569.569 0 0 0 .562.658h3.51a.665.665 0 0 0 .656-.562l.785-4.971a.95.95 0 0 1 .938-.803h2.164c4.506 0 7.105-2.18 7.785-6.5.307-1.89.012-3.375-.873-4.415-.971-1.142-2.694-1.746-4.983-1.746zm.789 6.405c-.373 2.454-2.248 2.454-4.062 2.454h-1.031l.725-4.583a.568.568 0 0 1 .562-.481h.473c1.234 0 2.4 0 3.002.704.359.42.468 1.044.331 1.906z"/>
                  <path fill="#179BD7" d="M115.434 13.075h-3.273a.567.567 0 0 0-.562.481l-.145.916-.23-.332c-.709-1.029-2.289-1.373-3.867-1.373-3.619 0-6.709 2.741-7.311 6.586-.312 1.918.131 3.752 1.219 5.031 1 1.176 2.426 1.666 4.125 1.666 2.916 0 4.533-1.875 4.533-1.875l-.146.91a.57.57 0 0 0 .564.66h2.949a.95.95 0 0 0 .938-.803l1.771-11.209a.571.571 0 0 0-.565-.658zm-4.565 6.374c-.314 1.871-1.801 3.127-3.695 3.127-.949 0-1.711-.305-2.199-.883-.484-.574-.666-1.391-.514-2.301.297-1.855 1.805-3.152 3.67-3.152.93 0 1.686.309 2.184.892.501.589.699 1.411.554 2.317z"/>
                  <path fill="#253B80" d="M119.295 7.23l-2.807 17.858a.569.569 0 0 0 .562.658h2.822c.469 0 .867-.34.939-.803l2.768-17.536a.57.57 0 0 0-.562-.659h-3.16a.571.571 0 0 0-.562.482z"/>
                </svg>
              </div>
              <!-- Stripe -->
              <div class="w-12 h-6 bg-white rounded flex items-center justify-center" title="Stripe">
                <svg class="h-4" viewBox="0 0 60 25" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path fill-rule="evenodd" clip-rule="evenodd" d="M60 12.8C60 8.55 57.85 5.05 53.6 5.05C49.35 5.05 46.75 8.55 46.75 12.75C46.75 17.7 49.9 20.5 54.35 20.5C56.5 20.5 58.15 20 59.4 19.25V15.8C58.15 16.5 56.7 16.95 54.9 16.95C53.15 16.95 51.6 16.35 51.4 14.25H59.95C59.95 14 60 13.2 60 12.8ZM51.3 11.25C51.3 9.25 52.5 8.4 53.55 8.4C54.6 8.4 55.7 9.25 55.7 11.25H51.3ZM40.4 5.05C38.6 5.05 37.45 5.9 36.85 6.5L36.6 5.3H32.15V24.5L36.85 23.5V19.15C37.5 19.6 38.4 20.3 40.05 20.3C43.4 20.3 46.4 17.55 46.4 12.35C46.4 7.65 43.35 5.05 40.4 5.05ZM39.25 16.7C38.15 16.7 37.5 16.3 37.05 15.8L36.85 9.7C37.3 9.15 38 8.8 39.05 8.8C40.7 8.8 41.85 10.55 41.85 12.75C41.85 15 40.75 16.7 39.25 16.7ZM27.3 4.4L32.05 3.4V0L27.3 1V4.4ZM27.3 5.3H32.05V20H27.3V5.3ZM23.05 6.65L22.75 5.3H18.4V20H23.1V10.3C24.1 9 25.85 9.25 26.4 9.45V5.3C25.8 5.1 23.75 4.7 23.05 6.65ZM13.6 1.75L9.05 2.75L9.05 15.9C9.05 18.5 11.05 20.5 13.65 20.5C15.1 20.5 16.15 20.25 16.7 19.95V16.3C16.2 16.5 13.6 17.25 13.6 14.65V9H16.7V5.3H13.6V1.75ZM4.7 9.7C4.7 9.05 5.25 8.75 6.2 8.75C7.5 8.75 9.15 9.15 10.45 9.85V5.6C9.05 5.05 7.65 4.8 6.25 4.8C2.5 4.8 0 6.8 0 10C0 14.9 6.9 14.15 6.9 16.3C6.9 17.1 6.2 17.4 5.2 17.4C3.75 17.4 1.9 16.8 0.45 16V20.3C2.05 21 3.7 21.35 5.2 21.35C9.05 21.35 11.7 19.4 11.7 16.15C11.7 10.85 4.7 11.75 4.7 9.7Z" fill="#635BFF"/>
                </svg>
              </div>
              <!-- Afterpay -->
              <div class="w-16 h-6 bg-[#B2FCE4] rounded flex items-center justify-center" title="Afterpay">
                <svg class="h-3" viewBox="0 0 100 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <path d="M10.8 19.2L8.4 24H3.6L15.6 0H20.4L8.4 24H3.6L10.8 10.8L10.8 19.2Z" fill="#000000"/>
                  <path d="M20.4 0L8.4 24H13.2L25.2 0H20.4Z" fill="#000000"/>
                  <text x="30" y="17" font-family="Arial" font-size="14" font-weight="bold" fill="#000000">afterpay</text>
                </svg>
              </div>
            </div>
          </div>

          <!-- Developer Credit -->
          <p class="text-gray-400 text-xs text-center md:text-right">
            Developed with ❤️ by
            <a
              href="https://bguvava.com"
              target="_blank"
              rel="noopener noreferrer"
              class="text-primary-500 hover:text-primary-400 transition-colors font-medium"
            >
              bguvava
            </a>
          </p>
        </div>
      </div>
    </div>
  </footer>
</template>
