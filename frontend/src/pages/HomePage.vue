<script setup>
/**
 * Home Page
 *
 * Landing page with hero section, featured products,
 * delivery zones, newsletter, and service area map.
 *
 * @requirement LAND-001 to LAND-015 Complete landing page implementation
 * @requirement ISSUE-005 Service area map showing 50km radius
 */
import HeroSection from '@/components/landing/HeroSection.vue'
import FeaturedProducts from '@/components/landing/FeaturedProducts.vue'
import WhyChooseUs from '@/components/landing/WhyChooseUs.vue'
import PromotionalSection from '@/components/landing/PromotionalSection.vue'
import DeliveryZones from '@/components/landing/DeliveryZones.vue'
import NewsletterSection from '@/components/landing/NewsletterSection.vue'
import ServiceAreaMap from '@/components/landing/ServiceAreaMap.vue'
</script>

<template>
  <div class="home-page">
    <!-- Hero Section with animated background and shop CTA -->
    <HeroSection />

    <!-- Featured Products Grid -->
    <FeaturedProducts />

    <!-- Why Choose Us - Icon-Based Design -->
    <WhyChooseUs />

    <!-- Promotional Section -->
    <PromotionalSection />

    <!-- Delivery Zones Interactive Map -->
    <DeliveryZones />

    <!-- Newsletter Signup -->
    <NewsletterSection />

    <!-- Service Area Map with 50km coverage -->
    <ServiceAreaMap />
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
