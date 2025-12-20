# Landing Page Module Documentation

## Overview

The Zambezi Meats landing page implements a shop-first design approach with engaging animations, scroll-triggered effects, and conversion-focused sections. The page is built with Vue 3 Composition API and uses Intersection Observer for performance-optimized animations.

## Requirements Implemented

| ID       | Requirement                           | Status      |
| -------- | ------------------------------------- | ----------- |
| LAND-001 | Hero section with animated background | ✅ Complete |
| LAND-002 | Header navigation with scroll effects | ✅ Complete |
| LAND-003 | Scroll-triggered animations           | ✅ Complete |
| LAND-004 | Featured products grid                | ✅ Complete |
| LAND-005 | Why Choose Us section                 | ✅ Complete |
| LAND-006 | Product quick view (modal)            | 🔄 Phase 3  |
| LAND-007 | Customer testimonials carousel        | ✅ Complete |
| LAND-008 | Delivery zones section                | ✅ Complete |
| LAND-009 | Contact form section                  | ✅ Complete |
| LAND-010 | Footer with navigation                | ✅ Complete |
| LAND-011 | Responsive mobile design              | ✅ Complete |
| LAND-012 | Newsletter signup                     | ✅ Complete |
| LAND-013 | Developer credits                     | ✅ Complete |
| LAND-014 | Entrance animations                   | ✅ Complete |
| LAND-015 | Micro-interactions                    | ✅ Complete |
| LAND-016 | Performance optimization              | ✅ Complete |
| LAND-017 | SEO meta tags                         | 🔄 Pending  |
| LAND-018 | Accessibility (WCAG)                  | ✅ Complete |
| LAND-019 | Social media links                    | ✅ Complete |
| LAND-020 | Component tests                       | 🔄 Pending  |

## Component Architecture

### Directory Structure

```
frontend/src/
├── components/
│   ├── common/
│   │   ├── HeaderNav.vue
│   │   └── FooterSection.vue
│   └── landing/
│       ├── HeroSection.vue
│       ├── FeaturedProducts.vue
│       ├── WhyChooseUs.vue
│       ├── TestimonialsSection.vue
│       ├── DeliveryZones.vue
│       ├── NewsletterSection.vue
│       └── ContactSection.vue
├── layouts/
│   └── GuestLayout.vue
└── pages/
    └── HomePage.vue
```

## Components

### HeroSection

Full-viewport hero with animated floating shapes and gradient background.

**Features:**

- Animated gradient background (secondary to black)
- Floating decorative shapes with blur effects
- Entrance animations (fade-in, slide-up)
- Trust badges with checkmarks
- Dual CTA buttons (Shop Now, Learn More)
- Scroll indicator with bounce animation

**Animation Classes:**

```css
.animate-float-slow   /* 8s cycle */
/* 8s cycle */
.animate-float-medium /* 6s cycle */
.animate-float-fast; /* 4s cycle */
```

### HeaderNav

Fixed navigation header with scroll-triggered style changes.

**Features:**

- Transparent on page load, solid white on scroll
- Logo with company name
- Desktop navigation with dropdown categories
- Mobile hamburger menu with slide animation
- Cart icon with item count badge
- User dropdown menu (when authenticated)
- Login/Register buttons (when guest)

**Props:** None (uses route and stores)

**Scroll Behavior:**

- Transparent background at top
- White background with shadow after 20px scroll
- Text colors adapt to background

### FeaturedProducts

Product grid showcasing 4 featured items with scroll animations.

**Features:**

- Section heading with description
- 4-column responsive grid
- Product cards with:
  - Image placeholder
  - Title, description, price
  - Original price (strikethrough) for discounts
  - Add to Cart button
  - Wishlist heart icon
- Intersection Observer for scroll reveal
- "View All Products" CTA button

**Demo Data:**

```javascript
const products = [
  { title: "Premium Ribeye Steak", price: 45.99, originalPrice: 52.99 },
  { title: "Australian Lamb Cutlets", price: 38.5 },
  { title: "Free Range Chicken Breast", price: 24.99 },
  { title: "Wagyu Beef Mince", price: 28.99, originalPrice: 35.0 },
];
```

### WhyChooseUs

6-feature value proposition section.

**Features:**

- Heading with subtext
- 3x2 grid of feature cards
- Each card includes:
  - Icon (SVG)
  - Title
  - Description
- Intersection Observer animations
- Staggered reveal (50ms delay between cards)

**Feature List:**

1. Premium Quality - Hand-selected from finest Australian farms
2. Same-Day Delivery - Ordered before 2PM
3. 100% Satisfaction Guarantee
4. Expert Butchers - 20+ years experience
5. Local & Sustainable - Supporting Australian farmers
6. Always Fresh - Never frozen premium cuts

### TestimonialsSection

Customer testimonials carousel with autoplay.

**Features:**

- Heading with description
- Carousel with 4 testimonials
- Auto-advance every 5 seconds
- Dot navigation
- Star ratings (5 stars)
- Customer avatar, name, and title
- Smooth slide transitions

**Testimonial Data:**

```javascript
{
  name: 'Sarah Mitchell',
  title: 'Home Chef',
  rating: 5,
  text: 'The quality of meat from Zambezi is exceptional...',
  avatar: '/avatars/sarah.jpg'
}
```

### DeliveryZones

Interactive delivery zone selector.

**Features:**

- Heading with intro text
- 3 zone buttons (Sydney Metro, Greater Sydney, Regional)
- Zone info card showing:
  - Delivery fee
  - Minimum order
  - Free delivery threshold
  - Delivery times
- Animated zone transitions

**Zone Configuration:**

```javascript
{
  id: 'sydney',
  name: 'Sydney Metro',
  description: 'Inner Sydney, Eastern Suburbs, North Shore',
  deliveryFee: 'FREE over $100',
  minOrder: '$50',
  deliveryTime: 'Same-day / Next-day'
}
```

### NewsletterSection

Email capture form for marketing.

**Features:**

- Heading with value proposition
- Email input with validation
- Subscribe button
- Success state with confirmation message
- Background decorative pattern
- Form reset after 5 seconds

**States:**

- Default: Form visible
- Loading: Button shows spinner
- Success: Thank you message displayed

### ContactSection

Contact form and information section.

**Features:**

- Two-column layout (form + info)
- Contact form fields:
  - Name (required)
  - Email (required)
  - Phone (optional)
  - Subject dropdown
  - Message textarea
- Contact info cards:
  - Phone number
  - Email address
  - Business address
  - Business hours
- Social media links
- Form validation
- Success/error states

### FooterSection

Comprehensive site footer.

**Features:**

- Brand column with description
- Social media links (Facebook, Instagram, Twitter, YouTube)
- 4-column link sections:
  - Shop (categories)
  - Company (about, contact, etc.)
  - Support (FAQ, shipping, etc.)
  - Account (login, orders, etc.)
- Bottom bar with:
  - Copyright notice
  - Payment method badges
  - Developer credit (@digitalartist221)

## Animations

### Entrance Animations

Applied via `isVisible` reactive state and Intersection Observer:

```vue
<div
  :class="isVisible ? 'opacity-100 translate-y-0' : 'opacity-0 translate-y-10'"
  class="transition-all duration-700"
>
```

### Staggered Animations

Delay applied per item using index:

```vue
:style="{ transitionDelay: `${index * 100}ms` }"
```

### Intersection Observer Setup

```javascript
onMounted(() => {
  observer = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          isVisible.value = true;
          observer.disconnect();
        }
      });
    },
    { threshold: 0.1 }
  );
  observer.observe(sectionRef.value);
});
```

## Responsive Design

### Breakpoints

| Breakpoint | Size   | Usage            |
| ---------- | ------ | ---------------- |
| sm         | 640px  | Mobile landscape |
| md         | 768px  | Tablets          |
| lg         | 1024px | Desktop          |
| xl         | 1280px | Large desktop    |

### Grid Layouts

```html
<!-- Featured Products -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
  <!-- Why Choose Us -->
  <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
    <!-- Footer Links -->
    <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-8"></div>
  </div>
</div>
```

## Performance Optimization

1. **Lazy Loading:** Components loaded with dynamic imports
2. **Intersection Observer:** Animations only trigger when visible
3. **CSS Animations:** GPU-accelerated transforms
4. **Image Optimization:** Placeholder with error handling
5. **Event Cleanup:** Observer disconnected after triggering

## Accessibility

- Semantic HTML elements (`<section>`, `<nav>`, `<footer>`)
- ARIA labels on interactive elements
- Keyboard navigation support
- Focus visible states
- Color contrast compliance
- Screen reader text for icons

## Theme Colors

```javascript
// tailwind.config.js
{
  primary: '#8B0000',      // Dark red
  secondary: '#2C1810',    // Dark brown
  accent: '#F5F5DC',       // Cream/beige
  'primary-dark': '#6B0000',
  'primary-light': '#AB2020'
}
```

## Usage

### HomePage Integration

```vue
<template>
  <div class="home-page">
    <HeroSection />
    <FeaturedProducts />
    <WhyChooseUs />
    <TestimonialsSection />
    <DeliveryZones />
    <NewsletterSection />
    <ContactSection />
  </div>
</template>
```

### GuestLayout Integration

```vue
<template>
  <div class="min-h-screen flex flex-col">
    <HeaderNav />
    <main class="flex-1">
      <RouterView />
    </main>
    <FooterSection />
  </div>
</template>
```

## Testing

### Component Tests (Vitest)

```bash
npm run test -- --filter=landing
```

**Test Cases:**

- HeroSection renders with animation trigger
- FeaturedProducts displays 4 products
- Testimonials carousel advances automatically
- Newsletter form submits successfully
- Contact form validates required fields
- Footer renders all link sections
- Header changes style on scroll

### E2E Tests (Cypress)

```bash
npm run test:e2e -- --spec=landing
```

**Test Scenarios:**

1. Page loads with hero visible
2. Scroll reveals all sections
3. Newsletter signup flow
4. Contact form submission
5. Navigation links work
6. Mobile menu toggles

## Future Enhancements

1. **LAND-006:** Product quick view modal (Phase 3)
2. **LAND-017:** SEO meta tags implementation
3. **LAND-020:** Complete test coverage
4. Integration with real product API
5. Newsletter integration with Mailchimp/Sendinblue
6. Contact form integration with email service
7. Analytics tracking for conversions
