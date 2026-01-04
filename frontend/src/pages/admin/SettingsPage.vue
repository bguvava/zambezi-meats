<script setup>
/**
 * Admin Settings Page
 * System configuration and store settings
 * @requirement SET-001 to SET-030 System Settings
 */
import { ref, computed, onMounted } from 'vue'
import { useAdminSettingsStore } from '@/stores/adminSettings'
import {
  Building, CreditCard, DollarSign, Shield, Save, Upload, Download, History,
  Eye, EyeOff, X, Check, AlertCircle, Loader2, ChevronRight
} from 'lucide-vue-next'

const settingsStore = useAdminSettingsStore()

// UI State
const showHistory = ref(false)
const showImportModal = ref(false)
const importData = ref('')
const testEmail = ref('')
const showSecrets = ref({})
const toast = ref({ show: false, type: 'success', message: '' })

// Settings groups configuration
const settingsGroups = [
  { id: 'store', name: 'Store Info', icon: Building },
  { id: 'payment', name: 'Payments', icon: CreditCard },
  { id: 'currency', name: 'Currency', icon: DollarSign },
  { id: 'security', name: 'Security', icon: Shield }
]

const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday']

// Computed
const currentGroup = computed(() => settingsStore.currentGroup)
const currentSettings = computed(() => settingsStore.currentGroupSettings)
const isLoading = computed(() => settingsStore.loading)
const isSaving = computed(() => settingsStore.saving)

// Methods
const selectGroup = (groupId) => {
  settingsStore.setCurrentGroup(groupId)
}

const saveCurrentGroup = async () => {
  try {
    await settingsStore.updateSettingsGroup(currentGroup.value, currentSettings.value)
    showToast('success', 'Settings saved successfully')
  } catch (error) {
    showToast('error', error.message || 'Failed to save settings')
  }
}

const updateSetting = (key, value) => {
  settingsStore.updateLocalSetting(key, value)
}

const handleLogoUpload = async (event) => {
  const file = event.target.files[0]
  if (!file) return
  try {
    await settingsStore.uploadStoreLogo(file)
    showToast('success', 'Logo uploaded successfully')
  } catch (error) {
    showToast('error', 'Failed to upload logo')
  }
}

const sendTestEmailAction = async () => {
  if (!testEmail.value) return
  try {
    await settingsStore.sendTestEmail(testEmail.value)
    showToast('success', 'Test email sent')
    testEmail.value = ''
  } catch (error) {
    showToast('error', 'Failed to send test email')
  }
}

const exportSettingsAction = async () => {
  try {
    const data = await settingsStore.exportSettings()
    const blob = new Blob([JSON.stringify(data, null, 2)], { type: 'application/json' })
    const url = URL.createObjectURL(blob)
    const a = document.createElement('a')
    a.href = url
    a.download = `zambezi-settings-${new Date().toISOString().split('T')[0]}.json`
    a.click()
    URL.revokeObjectURL(url)
    showToast('success', 'Settings exported')
  } catch (error) {
    showToast('error', 'Failed to export settings')
  }
}

const importSettingsAction = async () => {
  try {
    const data = JSON.parse(importData.value)
    await settingsStore.importSettings(data.settings || data)
    showImportModal.value = false
    importData.value = ''
    showToast('success', 'Settings imported successfully')
  } catch (error) {
    showToast('error', 'Failed to import settings')
  }
}

const loadHistory = async () => {
  await settingsStore.fetchHistory()
  showHistory.value = true
}

const toggleSecret = (key) => {
  showSecrets.value[key] = !showSecrets.value[key]
}

const showToast = (type, message) => {
  toast.value = { show: true, type, message }
  setTimeout(() => { toast.value.show = false }, 3000)
}

onMounted(async () => {
  await settingsStore.fetchAllSettings()
})
</script>

<template>
  <div class="min-h-screen bg-gray-50">
    <!-- Header -->
    <div class="bg-white border-b border-gray-200 px-6 py-4">
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-2xl font-bold text-gray-900">System Settings</h1>
          <p class="text-sm text-gray-500 mt-1">Configure store settings and preferences</p>
        </div>
        <div class="flex items-center gap-3">
          <button @click="loadHistory" class="btn-secondary"><History class="w-4 h-4 mr-2" />History</button>
          <button @click="showImportModal = true" class="btn-secondary"><Upload class="w-4 h-4 mr-2" />Import</button>
          <button @click="exportSettingsAction" class="btn-secondary"><Download class="w-4 h-4 mr-2" />Export</button>
        </div>
      </div>
    </div>

    <!-- Main Content -->
    <div class="flex">
      <!-- Sidebar -->
      <div class="w-64 bg-white border-r border-gray-200 min-h-[calc(100vh-80px)]">
        <nav class="p-4 space-y-1">
          <button v-for="group in settingsGroups" :key="group.id" @click="selectGroup(group.id)"
            :class="['w-full flex items-center gap-3 px-4 py-3 rounded-lg text-left transition-all',
              currentGroup === group.id ? 'bg-primary-50 text-primary-700 font-medium' : 'text-gray-600 hover:bg-gray-50']">
            <component :is="group.icon" class="w-5 h-5" />{{ group.name }}
            <ChevronRight v-if="currentGroup === group.id" class="w-4 h-4 ml-auto" />
          </button>
        </nav>
      </div>

      <!-- Settings Form -->
      <div class="flex-1 p-6">
        <div v-if="isLoading" class="flex items-center justify-center py-20">
          <Loader2 class="w-8 h-8 animate-spin text-primary-600" />
        </div>

        <div v-else class="max-w-3xl">
          <!-- Store Settings -->
          <div v-if="currentGroup === 'store'" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-6">Store Information</h2>
              <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                  <label class="form-label">Store Name</label>
                  <input type="text" class="form-input" :value="currentSettings.store_name" @input="updateSetting('store_name', $event.target.value)" />
                </div>
                <div class="col-span-2">
                  <label class="form-label">Tagline</label>
                  <input type="text" class="form-input" :value="currentSettings.store_tagline" @input="updateSetting('store_tagline', $event.target.value)" />
                </div>
                <div class="col-span-2">
                  <label class="form-label">Store Logo</label>
                  <div class="flex items-center gap-4">
                    <div v-if="currentSettings.store_logo" class="w-20 h-20 rounded-lg border overflow-hidden">
                      <img :src="currentSettings.store_logo" alt="Logo" class="w-full h-full object-contain" />
                    </div>
                    <label class="btn-secondary cursor-pointer"><Upload class="w-4 h-4 mr-2" />Upload Logo
                      <input type="file" class="hidden" accept="image/*" @change="handleLogoUpload" />
                    </label>
                  </div>
                </div>
                <div class="col-span-2"><label class="form-label">Address</label><input type="text" class="form-input" :value="currentSettings.store_address" @input="updateSetting('store_address', $event.target.value)" /></div>
                <div><label class="form-label">Suburb</label><input type="text" class="form-input" :value="currentSettings.store_suburb" @input="updateSetting('store_suburb', $event.target.value)" /></div>
                <div class="grid grid-cols-2 gap-4">
                  <div><label class="form-label">State</label><input type="text" class="form-input" :value="currentSettings.store_state" @input="updateSetting('store_state', $event.target.value)" /></div>
                  <div><label class="form-label">Postcode</label><input type="text" class="form-input" :value="currentSettings.store_postcode" @input="updateSetting('store_postcode', $event.target.value)" /></div>
                </div>
                <div><label class="form-label">Phone</label><input type="tel" class="form-input" :value="currentSettings.store_phone" @input="updateSetting('store_phone', $event.target.value)" /></div>
                <div><label class="form-label">Email</label><input type="email" class="form-input" :value="currentSettings.store_email" @input="updateSetting('store_email', $event.target.value)" /></div>
                <div class="col-span-2"><label class="form-label">ABN</label><input type="text" class="form-input" :value="currentSettings.store_abn" @input="updateSetting('store_abn', $event.target.value)" /></div>
              </div>
            </div>
          </div>

          <!-- Operating Hours -->
          <div v-else-if="currentGroup === 'operating'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Operating Hours</h2>
            <div class="space-y-3">
              <div v-for="day in days" :key="day" class="flex items-center gap-4 py-2 border-b border-gray-100">
                <span class="w-28 font-medium capitalize">{{ day }}</span>
                <input type="time" class="form-input w-32" :value="currentSettings[`operating_hours_${day}`]?.open || '09:00'"
                  @input="updateSetting(`operating_hours_${day}`, { ...currentSettings[`operating_hours_${day}`], open: $event.target.value })" />
                <span class="text-gray-400">to</span>
                <input type="time" class="form-input w-32" :value="currentSettings[`operating_hours_${day}`]?.close || '17:00'"
                  @input="updateSetting(`operating_hours_${day}`, { ...currentSettings[`operating_hours_${day}`], close: $event.target.value })" />
                <label class="flex items-center gap-2 text-sm text-gray-600">
                  <input type="checkbox" class="form-checkbox" :checked="currentSettings[`operating_hours_${day}`]?.closed"
                    @change="updateSetting(`operating_hours_${day}`, { ...currentSettings[`operating_hours_${day}`], closed: $event.target.checked })" /> Closed
                </label>
              </div>
            </div>
          </div>

          <!-- Payment Settings -->
          <div v-else-if="currentGroup === 'payment'" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold">Stripe</h3>
                <label class="toggle"><input type="checkbox" :checked="currentSettings.stripe_enabled" @change="updateSetting('stripe_enabled', $event.target.checked)" /><span class="toggle-slider"></span></label>
              </div>
              <div v-if="currentSettings.stripe_enabled" class="space-y-4">
                <div class="flex gap-4">
                  <label class="flex items-center gap-2"><input type="radio" name="stripe_mode" value="test" :checked="currentSettings.stripe_mode === 'test'" @change="updateSetting('stripe_mode', 'test')" /> Test</label>
                  <label class="flex items-center gap-2"><input type="radio" name="stripe_mode" value="live" :checked="currentSettings.stripe_mode === 'live'" @change="updateSetting('stripe_mode', 'live')" /> Live</label>
                </div>
                <div><label class="form-label">Public Key</label><input type="text" class="form-input" :value="currentSettings.stripe_public_key" @input="updateSetting('stripe_public_key', $event.target.value)" /></div>
                <div><label class="form-label">Secret Key</label>
                  <div class="relative"><input :type="showSecrets.stripe ? 'text' : 'password'" class="form-input pr-10" :value="currentSettings.stripe_secret_key" @input="updateSetting('stripe_secret_key', $event.target.value)" />
                    <button type="button" @click="toggleSecret('stripe')" class="absolute right-3 top-1/2 -translate-y-1/2"><Eye v-if="!showSecrets.stripe" class="w-4 h-4 text-gray-400" /><EyeOff v-else class="w-4 h-4 text-gray-400" /></button>
                  </div>
                </div>
              </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <div class="flex items-center justify-between mb-4"><h3 class="text-lg font-semibold">PayPal</h3>
                <label class="toggle"><input type="checkbox" :checked="currentSettings.paypal_enabled" @change="updateSetting('paypal_enabled', $event.target.checked)" /><span class="toggle-slider"></span></label>
              </div>
              <div v-if="currentSettings.paypal_enabled" class="space-y-4">
                <div><label class="form-label">Client ID</label><input type="text" class="form-input" :value="currentSettings.paypal_client_id" @input="updateSetting('paypal_client_id', $event.target.value)" /></div>
                <div><label class="form-label">Secret</label>
                  <div class="relative"><input :type="showSecrets.paypal ? 'text' : 'password'" class="form-input pr-10" :value="currentSettings.paypal_secret" @input="updateSetting('paypal_secret', $event.target.value)" />
                    <button type="button" @click="toggleSecret('paypal')" class="absolute right-3 top-1/2 -translate-y-1/2"><Eye v-if="!showSecrets.paypal" class="w-4 h-4 text-gray-400" /><EyeOff v-else class="w-4 h-4 text-gray-400" /></button>
                  </div>
                </div>
              </div>
            </div>
            <div class="grid grid-cols-2 gap-6">
              <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"><div class="flex items-center justify-between"><h3 class="font-semibold">Afterpay</h3><label class="toggle"><input type="checkbox" :checked="currentSettings.afterpay_enabled" @change="updateSetting('afterpay_enabled', $event.target.checked)" /><span class="toggle-slider"></span></label></div></div>
              <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6"><div class="flex items-center justify-between"><h3 class="font-semibold">Cash on Delivery</h3><label class="toggle"><input type="checkbox" :checked="currentSettings.cod_enabled" @change="updateSetting('cod_enabled', $event.target.checked)" /><span class="toggle-slider"></span></label></div></div>
            </div>
          </div>

          <!-- Email Settings -->
          <div v-else-if="currentGroup === 'email'" class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-6">SMTP Configuration</h2>
              <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">SMTP Host</label><input type="text" class="form-input" :value="currentSettings.smtp_host" @input="updateSetting('smtp_host', $event.target.value)" /></div>
                <div><label class="form-label">Port</label><input type="number" class="form-input" :value="currentSettings.smtp_port" @input="updateSetting('smtp_port', parseInt($event.target.value))" /></div>
                <div><label class="form-label">Username</label><input type="text" class="form-input" :value="currentSettings.smtp_username" @input="updateSetting('smtp_username', $event.target.value)" /></div>
                <div><label class="form-label">Password</label><input type="password" class="form-input" :value="currentSettings.smtp_password" @input="updateSetting('smtp_password', $event.target.value)" /></div>
                <div><label class="form-label">Encryption</label><select class="form-input" :value="currentSettings.smtp_encryption" @change="updateSetting('smtp_encryption', $event.target.value)"><option value="tls">TLS</option><option value="ssl">SSL</option><option value="">None</option></select></div>
              </div>
            </div>
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
              <h2 class="text-lg font-semibold text-gray-900 mb-6">Sender Settings</h2>
              <div class="grid grid-cols-2 gap-4">
                <div><label class="form-label">From Name</label><input type="text" class="form-input" :value="currentSettings.mail_from_name" @input="updateSetting('mail_from_name', $event.target.value)" /></div>
                <div><label class="form-label">From Email</label><input type="email" class="form-input" :value="currentSettings.mail_from_address" @input="updateSetting('mail_from_address', $event.target.value)" /></div>
              </div>
              <div class="mt-6 pt-6 border-t"><h3 class="font-medium mb-3">Send Test Email</h3>
                <div class="flex gap-3"><input type="email" v-model="testEmail" class="form-input flex-1" placeholder="test@example.com" /><button @click="sendTestEmailAction" class="btn-primary" :disabled="!testEmail">Send Test</button></div>
              </div>
            </div>
          </div>

          <!-- Currency Settings -->
          <div v-else-if="currentGroup === 'currency'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Currency Configuration</h2>
            <div class="grid grid-cols-2 gap-4">
              <div><label class="form-label">Default Currency</label><select class="form-input" :value="currentSettings.default_currency" @change="updateSetting('default_currency', $event.target.value)"><option value="AUD">AUD</option><option value="USD">USD</option></select></div>
              <div><label class="form-label">Update Frequency</label><select class="form-input" :value="currentSettings.exchange_rate_update_frequency" @change="updateSetting('exchange_rate_update_frequency', $event.target.value)"><option value="hourly">Hourly</option><option value="daily">Daily</option><option value="weekly">Weekly</option></select></div>
              <div class="col-span-2"><label class="form-label">Exchange Rate API Key</label><input type="text" class="form-input" :value="currentSettings.exchange_rate_api_key" @input="updateSetting('exchange_rate_api_key', $event.target.value)" /></div>
              <div class="col-span-2 flex items-center gap-4">
                <label class="flex items-center gap-2"><input type="checkbox" class="form-checkbox" :checked="currentSettings.use_manual_rate" @change="updateSetting('use_manual_rate', $event.target.checked)" /> Use Manual Rate</label>
                <div v-if="currentSettings.use_manual_rate" class="flex items-center gap-2"><span>1 AUD =</span><input type="number" step="0.01" class="form-input w-24" :value="currentSettings.manual_usd_rate" @input="updateSetting('manual_usd_rate', parseFloat($event.target.value))" /><span>USD</span></div>
              </div>
            </div>
          </div>

          <!-- Delivery Settings -->
          <div v-else-if="currentGroup === 'delivery'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Delivery Configuration</h2>
            <div class="grid grid-cols-2 gap-4">
              <div><label class="form-label">Minimum Order ($)</label><input type="number" step="0.01" class="form-input" :value="currentSettings.minimum_order_amount" @input="updateSetting('minimum_order_amount', parseFloat($event.target.value))" /></div>
              <div><label class="form-label">Free Delivery Threshold ($)</label><input type="number" step="0.01" class="form-input" :value="currentSettings.free_delivery_threshold" @input="updateSetting('free_delivery_threshold', parseFloat($event.target.value))" /></div>
              <div><label class="form-label">Default Delivery Fee ($)</label><input type="number" step="0.01" class="form-input" :value="currentSettings.default_delivery_fee" @input="updateSetting('default_delivery_fee', parseFloat($event.target.value))" /></div>
            </div>
          </div>

          <!-- Security Settings -->
          <div v-else-if="currentGroup === 'security'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Security Configuration</h2>
            <div class="grid grid-cols-2 gap-4 mb-4">
              <div><label class="form-label">Session Timeout (minutes)</label><input type="number" class="form-input" :value="currentSettings.session_timeout_minutes" @input="updateSetting('session_timeout_minutes', parseInt($event.target.value))" /></div>
              <div><label class="form-label">Password Min Length</label><input type="number" class="form-input" :value="currentSettings.password_min_length" @input="updateSetting('password_min_length', parseInt($event.target.value))" /></div>
            </div>
            <div class="space-y-2">
              <label class="flex items-center gap-2"><input type="checkbox" class="form-checkbox" :checked="currentSettings.password_require_uppercase" @change="updateSetting('password_require_uppercase', $event.target.checked)" /> Require uppercase</label>
              <label class="flex items-center gap-2"><input type="checkbox" class="form-checkbox" :checked="currentSettings.password_require_numbers" @change="updateSetting('password_require_numbers', $event.target.checked)" /> Require numbers</label>
              <label class="flex items-center gap-2"><input type="checkbox" class="form-checkbox" :checked="currentSettings.password_require_symbols" @change="updateSetting('password_require_symbols', $event.target.checked)" /> Require symbols</label>
            </div>
          </div>

          <!-- Notifications -->
          <div v-else-if="currentGroup === 'notifications'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Notifications</h2>
            <div class="space-y-4">
              <div class="flex items-center justify-between py-3 border-b"><div><h3 class="font-medium">Email Notifications</h3><p class="text-sm text-gray-500">Receive via email</p></div><label class="toggle"><input type="checkbox" :checked="currentSettings.enable_email_notifications" @change="updateSetting('enable_email_notifications', $event.target.checked)" /><span class="toggle-slider"></span></label></div>
              <div class="flex items-center justify-between py-3"><div><h3 class="font-medium">SMS Notifications</h3><p class="text-sm text-gray-500">Receive via SMS</p></div><label class="toggle"><input type="checkbox" :checked="currentSettings.enable_sms_notifications" @change="updateSetting('enable_sms_notifications', $event.target.checked)" /><span class="toggle-slider"></span></label></div>
            </div>
          </div>

          <!-- Features -->
          <div v-else-if="currentGroup === 'features'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Feature Toggles</h2>
            <div class="space-y-4">
              <div v-for="f in [{key:'enable_wishlist',name:'Wishlist',desc:'Save products'},{key:'enable_reviews',name:'Reviews',desc:'Leave reviews'},{key:'enable_guest_checkout',name:'Guest Checkout',desc:'No account needed'},{key:'enable_multi_currency',name:'Multi-Currency',desc:'Multiple currencies'}]" :key="f.key" class="flex items-center justify-between py-3 border-b last:border-0">
                <div><h3 class="font-medium">{{ f.name }}</h3><p class="text-sm text-gray-500">{{ f.desc }}</p></div><label class="toggle"><input type="checkbox" :checked="currentSettings[f.key]" @change="updateSetting(f.key, $event.target.checked)" /><span class="toggle-slider"></span></label>
              </div>
            </div>
          </div>

          <!-- SEO -->
          <div v-else-if="currentGroup === 'seo'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">SEO Configuration</h2>
            <div class="space-y-4">
              <div><label class="form-label">Meta Title</label><input type="text" class="form-input" :value="currentSettings.meta_title" @input="updateSetting('meta_title', $event.target.value)" /></div>
              <div><label class="form-label">Meta Description</label><textarea class="form-input" rows="3" :value="currentSettings.meta_description" @input="updateSetting('meta_description', $event.target.value)"></textarea></div>
              <div><label class="form-label">Meta Keywords</label><input type="text" class="form-input" :value="currentSettings.meta_keywords" @input="updateSetting('meta_keywords', $event.target.value)" /></div>
            </div>
          </div>

          <!-- Social -->
          <div v-else-if="currentGroup === 'social'" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6">
            <h2 class="text-lg font-semibold text-gray-900 mb-6">Social Media</h2>
            <div class="space-y-4">
              <div><label class="form-label">Facebook</label><input type="url" class="form-input" :value="currentSettings.facebook_url" @input="updateSetting('facebook_url', $event.target.value)" /></div>
              <div><label class="form-label">Instagram</label><input type="url" class="form-input" :value="currentSettings.instagram_url" @input="updateSetting('instagram_url', $event.target.value)" /></div>
              <div><label class="form-label">Twitter</label><input type="url" class="form-input" :value="currentSettings.twitter_url" @input="updateSetting('twitter_url', $event.target.value)" /></div>
              <div><label class="form-label">YouTube</label><input type="url" class="form-input" :value="currentSettings.youtube_url" @input="updateSetting('youtube_url', $event.target.value)" /></div>
            </div>
          </div>

          <!-- Save Button -->
          <div class="mt-6 flex justify-end">
            <button @click="saveCurrentGroup" :disabled="isSaving" class="btn-primary">
              <Loader2 v-if="isSaving" class="w-4 h-4 mr-2 animate-spin" /><Save v-else class="w-4 h-4 mr-2" />Save Changes
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- History Modal -->
    <div v-if="showHistory" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl max-w-2xl w-full mx-4 max-h-[80vh] overflow-hidden">
        <div class="flex items-center justify-between p-4 border-b"><h2 class="text-lg font-semibold">Settings History</h2><button @click="showHistory = false" class="p-1 hover:bg-gray-100 rounded"><X class="w-5 h-5" /></button></div>
        <div class="p-4 overflow-y-auto max-h-96">
          <div v-if="!settingsStore.history?.data?.length" class="text-center py-8 text-gray-500">No history</div>
          <div v-else class="space-y-3"><div v-for="item in settingsStore.history.data" :key="item.id" class="p-3 border rounded-lg"><div class="flex items-center justify-between"><span class="font-medium">{{ item.description }}</span><span class="text-sm text-gray-500">{{ new Date(item.created_at).toLocaleString() }}</span></div><div class="text-sm text-gray-600 mt-1">By: {{ item.user?.name || 'System' }}</div></div></div>
        </div>
      </div>
    </div>

    <!-- Import Modal -->
    <div v-if="showImportModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-50">
      <div class="bg-white rounded-xl shadow-xl max-w-lg w-full mx-4">
        <div class="flex items-center justify-between p-4 border-b"><h2 class="text-lg font-semibold">Import Settings</h2><button @click="showImportModal = false" class="p-1 hover:bg-gray-100 rounded"><X class="w-5 h-5" /></button></div>
        <div class="p-4"><textarea v-model="importData" class="form-input w-full" rows="10" placeholder="Paste JSON..."></textarea><div class="flex justify-end gap-3 mt-4"><button @click="showImportModal = false" class="btn-secondary">Cancel</button><button @click="importSettingsAction" class="btn-primary" :disabled="!importData">Import</button></div></div>
      </div>
    </div>

    <!-- Toast -->
    <div v-if="toast.show" :class="['fixed bottom-4 right-4 px-4 py-3 rounded-lg shadow-lg flex items-center gap-2', toast.type === 'success' ? 'bg-green-500 text-white' : 'bg-red-500 text-white']">
      <Check v-if="toast.type === 'success'" class="w-5 h-5" /><AlertCircle v-else class="w-5 h-5" />{{ toast.message }}
    </div>
  </div>
</template>

<style scoped>
@reference "../../style.css";

.form-label { @apply block text-sm font-medium text-gray-700 mb-1; }
.form-input { @apply w-full px-3 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent; }
.form-checkbox { @apply w-4 h-4 text-primary-600 border-gray-300 rounded focus:ring-primary-500; }
.btn-primary { @apply inline-flex items-center px-4 py-2 bg-primary-600 text-white font-medium rounded-lg hover:bg-primary-700 disabled:opacity-50 disabled:cursor-not-allowed transition-colors; }
.btn-secondary { @apply inline-flex items-center px-4 py-2 bg-white border border-gray-300 text-gray-700 font-medium rounded-lg hover:bg-gray-50 transition-colors; }
.toggle { @apply relative inline-block w-11 h-6; }
.toggle input { @apply opacity-0 w-0 h-0; }
.toggle-slider { @apply absolute cursor-pointer inset-0 bg-gray-300 rounded-full transition-colors; }
.toggle-slider:before { content: ""; @apply absolute h-5 w-5 left-0.5 bottom-0.5 bg-white rounded-full transition-transform; }
.toggle input:checked + .toggle-slider { @apply bg-primary-600; }
.toggle input:checked + .toggle-slider:before { transform: translateX(20px); }
</style>
