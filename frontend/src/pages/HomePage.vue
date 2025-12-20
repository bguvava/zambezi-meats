<script setup>
/**
 * Home Page
 *
 * Landing page with hero section, featured products,
 * delivery zones, newsletter, and contact sections.
 *
 * @requirement LAND-001 to LAND-015 Complete landing page implementation
 */
import HeroSection from '@/components/landing/HeroSection.vue'
import FeaturedProducts from '@/components/landing/FeaturedProducts.vue'
import WhyChooseUs from '@/components/landing/WhyChooseUs.vue'
import DeliveryZones from '@/components/landing/DeliveryZones.vue'
import NewsletterSection from '@/components/landing/NewsletterSection.vue'
import ContactSection from '@/components/landing/ContactSection.vue'
</script>

<template>
  <div class="home-page">
    <!-- Hero Section with animated background and shop CTA -->
    <HeroSection />

    <!-- Featured Products Grid -->
    <FeaturedProducts />

    <!-- Why Choose Us - 6 Feature Cards -->
    <WhyChooseUs />

    <!-- Delivery Zones Interactive Map -->
    <DeliveryZones />

    <!-- Newsletter Signup -->
    <NewsletterSection />

    <!-- Contact Form and Info -->
    <ContactSection />
  </div>
</template>

<style scoped>
.home-page {
  /* Remove default header padding as HeroSection handles this */
  margin-top: -4rem;
}

@media (min-width: 768px) {
  .home-page {
    margin-top: -5rem;
  }
}
</style>
