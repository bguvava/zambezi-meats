/**
 * Click Away Directive
 * Triggers callback when clicking outside the element
 */
export const clickAway = {
  mounted(el, binding) {
    el.clickAwayEvent = function(event) {
      // Check if the click was outside the el and its children
      if (!(el === event.target || el.contains(event.target))) {
        // Call method provided in directive value
        binding.value(event)
      }
    }
    document.addEventListener('click', el.clickAwayEvent)
  },
  unmounted(el) {
    document.removeEventListener('click', el.clickAwayEvent)
  }
}

export default clickAway
