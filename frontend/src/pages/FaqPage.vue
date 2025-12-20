<script setup>
/**
 * FaqPage.vue
 * Frequently Asked Questions page with accordion-style answers
 */
import { ref } from 'vue'
import { RouterLink } from 'vue-router'

const faqs = ref([
  {
    id: 1,
    question: 'What areas do you deliver to?',
    answer: 'We currently deliver to Engadine, Menangle Park, City of Campbelltown, and the greater Sydney area. If you\'re unsure whether we deliver to your location, please contact us.',
    open: false
  },
  {
    id: 2,
    question: 'How is the meat packaged for delivery?',
    answer: 'All our meat products are vacuum-sealed and packed in insulated boxes with ice packs to maintain the cold chain. This ensures your meat arrives fresh and safe.',
    open: false
  },
  {
    id: 3,
    question: 'What payment methods do you accept?',
    answer: 'We accept all major credit cards (Visa, MasterCard, American Express), PayPal, and bank transfers. All payments are processed securely.',
    open: false
  },
  {
    id: 4,
    question: 'Can I modify or cancel my order?',
    answer: 'You can modify or cancel your order within 2 hours of placing it. After that, please contact us as soon as possible and we\'ll do our best to accommodate your request.',
    open: false
  },
  {
    id: 5,
    question: 'How long does delivery take?',
    answer: 'Delivery times vary by location. Same-day delivery is available in Engadine and Menangle Park for orders placed before 2 PM. Greater Sydney deliveries typically take 1-2 business days.',
    open: false
  },
  {
    id: 6,
    question: 'What if I\'m not home during delivery?',
    answer: 'Someone must be present to receive the delivery as our products are perishable. You\'ll receive a delivery time window via SMS or email so you can plan accordingly.',
    open: false
  },
  {
    id: 7,
    question: 'Do you offer bulk or wholesale orders?',
    answer: 'Yes! We offer bulk pricing for restaurants, cafes, and large orders. Please contact us directly to discuss your requirements and get a custom quote.',
    open: false
  },
  {
    id: 8,
    question: 'Where does your meat come from?',
    answer: 'We source our premium quality meats from trusted Australian farms and international suppliers. All our products meet strict quality and safety standards.',
    open: false
  },
  {
    id: 9,
    question: 'What is your return policy?',
    answer: 'If you\'re not satisfied with your order, please contact us within 24 hours of delivery with photos of the product. We\'ll arrange a refund or replacement for any quality issues.',
    open: false
  },
  {
    id: 10,
    question: 'How do I store the meat after delivery?',
    answer: 'Refrigerate your meat immediately upon delivery. Fresh meat should be used within 3-5 days or frozen for longer storage. Follow the storage instructions on the packaging.',
    open: false
  }
])

const toggleFaq = (id) => {
  const faq = faqs.value.find(f => f.id === id)
  if (faq) {
    faq.open = !faq.open
  }
}
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
      <!-- Page Header -->
      <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-gray-900 mb-4">Frequently Asked Questions</h1>
        <p class="text-lg text-gray-600">Find answers to common questions about our products and services</p>
      </div>

      <!-- FAQ Accordion -->
      <div class="space-y-4">
        <div 
          v-for="faq in faqs" 
          :key="faq.id"
          class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden"
        >
          <button 
            @click="toggleFaq(faq.id)"
            class="w-full px-6 py-4 text-left flex items-center justify-between hover:bg-gray-50 transition-colors"
          >
            <span class="text-lg font-medium text-gray-800">{{ faq.question }}</span>
            <svg 
              class="w-5 h-5 text-gray-500 transition-transform duration-200"
              :class="{ 'rotate-180': faq.open }"
              fill="none" 
              stroke="currentColor" 
              viewBox="0 0 24 24"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
            </svg>
          </button>
          <div 
            v-show="faq.open"
            class="px-6 pb-4"
          >
            <p class="text-gray-600">{{ faq.answer }}</p>
          </div>
        </div>
      </div>

      <!-- Still Have Questions -->
      <div class="mt-12 bg-primary-50 rounded-lg p-8 text-center border border-primary-100">
        <h2 class="text-2xl font-semibold text-gray-800 mb-4">Still have questions?</h2>
        <p class="text-gray-600 mb-6">Can't find the answer you're looking for? Our team is here to help.</p>
        <RouterLink 
          to="/contact"
          class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition-colors"
        >
          Contact Us
          <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </RouterLink>
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
