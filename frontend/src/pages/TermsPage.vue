<script setup>
/**
 * TermsPage.vue
 * Terms of Service page
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Terms of Service</h1>
        <p class="text-gray-600">Last updated: January 2025</p>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="prose prose-gray max-w-none space-y-8">
          <!-- Introduction -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">1. Introduction</h2>
            <p class="text-gray-600">
              Welcome to Zambezi Meats. These Terms of Service ("Terms") govern your use of our website 
              and services. By accessing or using our website, you agree to be bound by these Terms.
            </p>
          </section>

          <!-- Use of Service -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">2. Use of Service</h2>
            <p class="text-gray-600">You agree to use our service only for lawful purposes and in accordance with these Terms. You must not:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 mt-4">
              <li>Use the service in any way that violates applicable laws or regulations</li>
              <li>Attempt to interfere with the proper functioning of the website</li>
              <li>Use automated systems to access the service without permission</li>
              <li>Impersonate any person or entity</li>
            </ul>
          </section>

          <!-- Account Registration -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">3. Account Registration</h2>
            <p class="text-gray-600">
              To place orders, you may need to create an account. You are responsible for maintaining 
              the confidentiality of your account credentials and for all activities under your account. 
              You must provide accurate and complete information when creating an account.
            </p>
          </section>

          <!-- Orders and Payments -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">4. Orders and Payments</h2>
            <p class="text-gray-600">
              All orders are subject to acceptance and availability. Prices are displayed in Australian Dollars (AUD) 
              and may change without notice. Payment must be received before order processing begins. 
              We accept major credit cards and PayPal.
            </p>
          </section>

          <!-- Delivery -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">5. Delivery</h2>
            <p class="text-gray-600">
              We deliver to selected areas in New South Wales. Delivery times are estimates and may vary. 
              You must ensure someone is available to receive perishable deliveries. 
              Risk of loss passes to you upon delivery.
            </p>
          </section>

          <!-- Returns and Refunds -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">6. Returns and Refunds</h2>
            <p class="text-gray-600">
              Due to the perishable nature of our products, we cannot accept physical returns. 
              If you receive damaged or defective products, please contact us within 24 hours 
              with photos for a refund or replacement. See our 
              <RouterLink to="/shipping" class="text-primary-600 hover:text-primary-700">Shipping & Returns</RouterLink> 
              page for full details.
            </p>
          </section>

          <!-- Intellectual Property -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">7. Intellectual Property</h2>
            <p class="text-gray-600">
              All content on this website, including text, images, logos, and graphics, is the property 
              of Zambezi Meats and is protected by copyright and trademark laws. You may not reproduce, 
              distribute, or create derivative works without our written permission.
            </p>
          </section>

          <!-- Limitation of Liability -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">8. Limitation of Liability</h2>
            <p class="text-gray-600">
              To the maximum extent permitted by law, Zambezi Meats shall not be liable for any indirect, 
              incidental, special, or consequential damages arising from your use of our service. 
              Our total liability shall not exceed the amount you paid for the relevant order.
            </p>
          </section>

          <!-- Governing Law -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">9. Governing Law</h2>
            <p class="text-gray-600">
              These Terms are governed by the laws of New South Wales, Australia. Any disputes shall be 
              resolved in the courts of New South Wales.
            </p>
          </section>

          <!-- Changes to Terms -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">10. Changes to Terms</h2>
            <p class="text-gray-600">
              We reserve the right to modify these Terms at any time. Changes will be effective upon 
              posting to our website. Your continued use of the service after changes constitutes 
              acceptance of the modified Terms.
            </p>
          </section>

          <!-- Contact -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">11. Contact Us</h2>
            <p class="text-gray-600">
              If you have questions about these Terms, please contact us:
            </p>
            <ul class="list-none text-gray-600 space-y-2 mt-4">
              <li><strong>Email:</strong> info@zambezimeats.com.au</li>
              <li><strong>Phone:</strong> +61 422 235 020</li>
              <li><strong>Address:</strong> 6/1053 Old Princes Highway, Engadine, NSW 2233</li>
            </ul>
          </section>
        </div>
      </div>

      <!-- Back Link -->
      <div class="mt-12 text-center">
        <RouterLink 
          to="/" 
          class="inline-flex items-center text-primary-600 hover:text-primary-700 font-medium"
        >
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
          </svg>
          Back to Home
        </RouterLink>
      </div>
    </div>
  </div>
</template>
