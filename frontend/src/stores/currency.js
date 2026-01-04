/**
 * Currency Store
 *
 * Manages currency selection, exchange rates, and formatting.
 *
 * @requirement PROJ-INIT-010 Configure Pinia store structure
 */
import { defineStore } from "pinia";
import { ref, computed } from "vue";
import currency from "currency.js";
import api from "@/services/api";

export const useCurrencyStore = defineStore("currency", () => {
  // State
  const currentCurrency = ref("AUD");
  const exchangeRates = ref({
    AUD: 1,
    USD: 0.65, // Default fallback rate
  });
  const isLoading = ref(false);
  const lastUpdated = ref(null);

  // Currency configurations
  const CURRENCIES = {
    AUD: {
      code: "AUD",
      symbol: "$",
      name: "Australian Dollar",
      locale: "en-AU",
      precision: 2,
    },
    USD: {
      code: "USD",
      symbol: "US$",
      name: "US Dollar",
      locale: "en-US",
      precision: 2,
    },
  };

  // Getters
  const currentCurrencyConfig = computed(
    () => CURRENCIES[currentCurrency.value]
  );

  const currentSymbol = computed(
    () => currentCurrencyConfig.value?.symbol || "$"
  );

  const availableCurrencies = computed(() => Object.values(CURRENCIES));

  const currentRate = computed(
    () => exchangeRates.value[currentCurrency.value] || 1
  );

  // Actions

  /**
   * Set current currency
   * @param {string} code - Currency code (AUD, USD)
   */
  function setCurrency(code) {
    if (CURRENCIES[code]) {
      currentCurrency.value = code;
      localStorage.setItem("zambezi_currency", code);
    }
  }

  /**
   * Load currency preference from storage
   */
  function loadFromStorage() {
    const stored = localStorage.getItem("zambezi_currency");
    if (stored && CURRENCIES[stored]) {
      currentCurrency.value = stored;
    }
  }

  /**
   * Fetch current exchange rates
   */
  async function fetchExchangeRates() {
    isLoading.value = true;

    try {
      const response = await api.get("/public/exchange-rates");
      exchangeRates.value = response.data.rates || exchangeRates.value;
      lastUpdated.value = new Date();
    } catch (error) {
      console.error("Failed to fetch exchange rates:", error);
      // Keep using fallback rates
    } finally {
      isLoading.value = false;
    }
  }

  /**
   * Convert amount from AUD to current currency
   * @param {number} amountAUD - Amount in AUD
   * @returns {number} Amount in current currency
   */
  function convert(amountAUD) {
    if (currentCurrency.value === "AUD") {
      return amountAUD;
    }
    return amountAUD * currentRate.value;
  }

  /**
   * Format amount in current currency
   * @param {number} amount - Amount to format (in AUD)
   * @param {boolean} showCode - Whether to show currency code
   * @returns {string} Formatted currency string
   */
  function format(amount, showCode = false) {
    const config = currentCurrencyConfig.value;
    const convertedAmount = convert(amount);

    const formatted = currency(convertedAmount, {
      symbol: config.symbol,
      precision: config.precision,
      separator: ",",
      decimal: ".",
    }).format();

    return showCode ? `${formatted} ${config.code}` : formatted;
  }

  /**
   * Format amount without conversion (for display purposes)
   * @param {number} amount - Amount to format
   * @param {string} currencyCode - Currency code
   * @returns {string} Formatted currency string
   */
  function formatRaw(amount, currencyCode = "AUD") {
    const config = CURRENCIES[currencyCode] || CURRENCIES.AUD;

    return currency(amount, {
      symbol: config.symbol,
      precision: config.precision,
      separator: ",",
      decimal: ".",
    }).format();
  }

  /**
   * Format price in current currency (alias for format)
   * @param {number} price - Price to format (in AUD)
   * @returns {string} Formatted price string
   */
  function formatPrice(price) {
    return format(price, false);
  }

  /**
   * Get currency by code
   * @param {string} code - Currency code
   */
  function getCurrency(code) {
    return CURRENCIES[code] || null;
  }

  return {
    // State
    currentCurrency,
    exchangeRates,
    isLoading,
    lastUpdated,
    CURRENCIES,

    // Getters
    currentCurrencyConfig,
    currentSymbol,
    availableCurrencies,
    currentRate,

    // Actions
    setCurrency,
    loadFromStorage,
    fetchExchangeRates,
    convert,
    format,
    formatPrice,
    formatRaw,
    getCurrency,
  };
});
