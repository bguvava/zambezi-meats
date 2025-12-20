<script setup>
/**
 * PrivacyPage.vue
 * Privacy Policy page
 */
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Privacy Policy</h1>
        <p class="text-gray-600">Last updated: January 2025</p>
      </div>

      <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-8">
        <div class="prose prose-gray max-w-none space-y-8">
          <!-- Introduction -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">1. Introduction</h2>
            <p class="text-gray-600">
              Zambezi Meats ("we", "our", "us") is committed to protecting your privacy. This Privacy Policy 
              explains how we collect, use, disclose, and safeguard your information when you visit our website 
              or make a purchase.
            </p>
          </section>

          <!-- Information We Collect -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">2. Information We Collect</h2>
            
            <h3 class="text-lg font-medium text-gray-800 mt-4">Personal Information</h3>
            <p class="text-gray-600">When you create an account or place an order, we collect:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 mt-2">
              <li>Name and contact information (email, phone number)</li>
              <li>Delivery address</li>
              <li>Payment information (processed securely by our payment providers)</li>
              <li>Order history and preferences</li>
            </ul>

            <h3 class="text-lg font-medium text-gray-800 mt-4">Automatically Collected Information</h3>
            <p class="text-gray-600">When you visit our website, we may collect:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 mt-2">
              <li>IP address and device information</li>
              <li>Browser type and version</li>
              <li>Pages visited and time spent</li>
              <li>Referring website</li>
            </ul>
          </section>

          <!-- How We Use Your Information -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">3. How We Use Your Information</h2>
            <p class="text-gray-600">We use your information to:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 mt-4">
              <li>Process and fulfill your orders</li>
              <li>Communicate with you about your orders and account</li>
              <li>Send promotional emails (with your consent)</li>
              <li>Improve our website and services</li>
              <li>Prevent fraud and ensure security</li>
              <li>Comply with legal obligations</li>
            </ul>
          </section>

          <!-- Information Sharing -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">4. Information Sharing</h2>
            <p class="text-gray-600">We may share your information with:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 mt-4">
              <li><strong>Service Providers:</strong> Payment processors, delivery partners, and hosting providers who assist us in operating our business</li>
              <li><strong>Legal Requirements:</strong> When required by law or to protect our rights</li>
              <li><strong>Business Transfers:</strong> In connection with a merger, acquisition, or sale of assets</li>
            </ul>
            <p class="text-gray-600 mt-4">
              We do not sell your personal information to third parties.
            </p>
          </section>

          <!-- Cookies -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">5. Cookies</h2>
            <p class="text-gray-600">
              We use cookies and similar technologies to enhance your browsing experience, 
              remember your preferences, and analyze website traffic. You can control cookies 
              through your browser settings, but some features may not function properly if 
              cookies are disabled.
            </p>
          </section>

          <!-- Data Security -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">6. Data Security</h2>
            <p class="text-gray-600">
              We implement appropriate technical and organizational measures to protect your 
              personal information. However, no method of transmission over the Internet is 
              100% secure, and we cannot guarantee absolute security.
            </p>
          </section>

          <!-- Your Rights -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">7. Your Rights</h2>
            <p class="text-gray-600">Under Australian privacy law, you have the right to:</p>
            <ul class="list-disc list-inside text-gray-600 space-y-2 mt-4">
              <li>Access your personal information</li>
              <li>Correct inaccurate information</li>
              <li>Request deletion of your information</li>
              <li>Opt out of marketing communications</li>
              <li>Lodge a complaint with the Office of the Australian Information Commissioner</li>
            </ul>
          </section>

          <!-- Data Retention -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">8. Data Retention</h2>
            <p class="text-gray-600">
              We retain your personal information for as long as necessary to fulfill the purposes 
              for which it was collected, comply with legal obligations, resolve disputes, and 
              enforce our agreements. Order records are typically retained for 7 years for 
              accounting purposes.
            </p>
          </section>

          <!-- Third-Party Links -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">9. Third-Party Links</h2>
            <p class="text-gray-600">
              Our website may contain links to third-party websites. We are not responsible for 
              the privacy practices of these websites. We encourage you to read their privacy 
              policies before providing any personal information.
            </p>
          </section>

          <!-- Changes to Policy -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">10. Changes to This Policy</h2>
            <p class="text-gray-600">
              We may update this Privacy Policy from time to time. Changes will be posted on this 
              page with an updated revision date. We encourage you to review this policy periodically.
            </p>
          </section>

          <!-- Contact -->
          <section>
            <h2 class="text-2xl font-semibold text-gray-800">11. Contact Us</h2>
            <p class="text-gray-600">
              If you have questions about this Privacy Policy or our privacy practices, please contact us:
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
