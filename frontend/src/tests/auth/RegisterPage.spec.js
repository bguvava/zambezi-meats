/**
 * Register Page Tests
 *
 * Tests for the RegisterPage.vue component.
 *
 * @requirement AUTH-017 Create RegisterPage.vue with form validation
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { setActivePinia, createPinia } from "pinia";
import RegisterPage from "@/pages/auth/RegisterPage.vue";

// Mock vue-router
const mockPush = vi.fn();
vi.mock("vue-router", () => ({
  useRoute: () => ({
    query: {},
  }),
  useRouter: () => ({
    push: mockPush,
  }),
  RouterLink: {
    name: "RouterLink",
    template: "<a><slot /></a>",
    props: ["to"],
  },
}));

// Mock useAuth composable
const mockRegister = vi.fn();
const mockCheckEmailAvailability = vi.fn();

vi.mock("@/composables/useAuth", () => ({
  useAuth: () => ({
    register: mockRegister,
    checkEmailAvailability: mockCheckEmailAvailability,
    isLoading: { value: false },
  }),
}));

describe("RegisterPage.vue", () => {
  let wrapper;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    vi.useFakeTimers();

    mockCheckEmailAvailability.mockResolvedValue(true);

    wrapper = mount(RegisterPage, {
      global: {
        stubs: {
          RouterLink: {
            template: "<a><slot /></a>",
            props: ["to"],
          },
        },
      },
    });
  });

  afterEach(() => {
    vi.useRealTimers();
  });

  describe("Rendering", () => {
    it("should render registration form", () => {
      expect(wrapper.find("form").exists()).toBe(true);
    });

    it("should render name input", () => {
      expect(wrapper.find("input#name").exists()).toBe(true);
    });

    it("should render email input", () => {
      expect(wrapper.find('input[type="email"]').exists()).toBe(true);
    });

    it("should render phone input", () => {
      expect(wrapper.find('input[type="tel"]').exists()).toBe(true);
    });

    it("should render password input", () => {
      expect(wrapper.find("input#password").exists()).toBe(true);
    });

    it("should render confirm password input", () => {
      expect(wrapper.find("input#password_confirmation").exists()).toBe(true);
    });

    it("should render submit button", () => {
      expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it("should render login link", () => {
      const loginLink = wrapper
        .findAll("a")
        .find((a) => a.text().includes("Sign in"));
      expect(loginLink).toBeDefined();
    });

    it("should render terms link", () => {
      expect(wrapper.text()).toContain("Terms of Service");
    });

    it("should render privacy link", () => {
      expect(wrapper.text()).toContain("Privacy Policy");
    });
  });

  describe("Form Validation", () => {
    it("should show name error when name is empty on blur", async () => {
      const nameInput = wrapper.find("input#name");
      await nameInput.trigger("blur");

      expect(wrapper.text()).toContain("Name is required");
    });

    it("should show name error when name is too short", async () => {
      const nameInput = wrapper.find("input#name");
      await nameInput.setValue("A");
      await nameInput.trigger("blur");

      expect(wrapper.text()).toContain("at least 2 characters");
    });

    it("should show email error when email is empty on blur", async () => {
      const emailInput = wrapper.find('input[type="email"]');
      await emailInput.trigger("blur");

      expect(wrapper.text()).toContain("Email is required");
    });

    it("should show email error for invalid email format", async () => {
      const emailInput = wrapper.find('input[type="email"]');
      await emailInput.setValue("invalid-email");
      await emailInput.trigger("blur");

      expect(wrapper.text()).toContain("valid email address");
    });

    it("should show password error when password is empty on blur", async () => {
      const passwordInput = wrapper.find("input#password");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("Password is required");
    });

    it("should show password error when password is too short", async () => {
      const passwordInput = wrapper.find("input#password");
      await passwordInput.setValue("short");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("at least 8 characters");
    });

    it("should show password confirmation error when empty on blur", async () => {
      const confirmInput = wrapper.find("input#password_confirmation");
      await confirmInput.trigger("blur");

      expect(wrapper.text()).toContain("confirm your password");
    });

    it("should show password mismatch error", async () => {
      await wrapper.find("input#password").setValue("password123");
      await wrapper
        .find("input#password_confirmation")
        .setValue("different123");
      await wrapper.find("input#password_confirmation").trigger("blur");

      expect(wrapper.text()).toContain("Passwords do not match");
    });

    it("should validate phone number format", async () => {
      const phoneInput = wrapper.find('input[type="tel"]');
      await phoneInput.setValue("invalid");
      await phoneInput.trigger("blur");

      expect(wrapper.text()).toContain("valid phone number");
    });

    it("should accept valid phone number", async () => {
      const phoneInput = wrapper.find('input[type="tel"]');
      await phoneInput.setValue("+61400000000");
      await phoneInput.trigger("blur");

      expect(wrapper.text()).not.toContain("valid phone number");
    });
  });

  describe("Email Availability Check", () => {
    it("should check email availability after debounce", async () => {
      mockCheckEmailAvailability.mockResolvedValue(true);

      await wrapper.find('input[type="email"]').setValue("new@example.com");

      // Fast-forward debounce timer
      vi.advanceTimersByTime(500);
      await flushPromises();

      expect(mockCheckEmailAvailability).toHaveBeenCalledWith(
        "new@example.com"
      );
    });

    it("should show taken email message", async () => {
      mockCheckEmailAvailability.mockResolvedValue(false);

      await wrapper.find('input[type="email"]').setValue("taken@example.com");

      vi.advanceTimersByTime(500);
      await flushPromises();

      expect(wrapper.text()).toContain("already registered");
    });
  });

  describe("Password Strength", () => {
    it("should show weak password strength", async () => {
      await wrapper.find("input#password").setValue("weak");
      await flushPromises();

      expect(wrapper.text()).toContain("Weak");
    });

    it("should show strong password strength", async () => {
      await wrapper.find("input#password").setValue("StrongPass1!");
      await flushPromises();

      expect(wrapper.text()).toContain("Strong");
    });

    it("should show password strength indicator", async () => {
      await wrapper.find("input#password").setValue("Test1234!");
      await flushPromises();

      // Should show strength bars
      const strengthBars = wrapper.findAll(".h-1.flex-1.rounded-full");
      expect(strengthBars.length).toBe(5);
    });
  });

  describe("Form Submission", () => {
    it("should not submit with invalid form", async () => {
      const form = wrapper.find("form");
      await form.trigger("submit");

      expect(mockRegister).not.toHaveBeenCalled();
    });

    it("should submit with valid data", async () => {
      mockCheckEmailAvailability.mockResolvedValue(true);
      mockRegister.mockResolvedValueOnce({ success: true });

      // Fill in valid form data
      await wrapper.find("input#name").setValue("John Doe");
      await wrapper.find('input[type="email"]').setValue("john@example.com");

      // Trigger email check
      vi.advanceTimersByTime(500);
      await flushPromises();

      // Strong password
      await wrapper.find("input#password").setValue("StrongPass1!");
      await wrapper
        .find("input#password_confirmation")
        .setValue("StrongPass1!");

      await wrapper.find("form").trigger("submit");
      await flushPromises();

      expect(mockRegister).toHaveBeenCalledWith({
        name: "John Doe",
        email: "john@example.com",
        phone: null,
        password: "StrongPass1!",
        password_confirmation: "StrongPass1!",
      });
    });

    it("should include phone when provided", async () => {
      mockCheckEmailAvailability.mockResolvedValue(true);
      mockRegister.mockResolvedValueOnce({ success: true });

      await wrapper.find("input#name").setValue("John Doe");
      await wrapper.find('input[type="email"]').setValue("john@example.com");

      vi.advanceTimersByTime(500);
      await flushPromises();

      await wrapper.find('input[type="tel"]').setValue("+61400000000");
      await wrapper.find("input#password").setValue("StrongPass1!");
      await wrapper
        .find("input#password_confirmation")
        .setValue("StrongPass1!");

      await wrapper.find("form").trigger("submit");
      await flushPromises();

      expect(mockRegister).toHaveBeenCalledWith(
        expect.objectContaining({
          phone: "+61400000000",
        })
      );
    });

    it("should display server error on registration failure", async () => {
      mockCheckEmailAvailability.mockResolvedValue(true);
      mockRegister.mockResolvedValueOnce({
        success: false,
        message: "Registration failed",
        errors: { email: ["The email has already been taken."] },
      });

      await wrapper.find("input#name").setValue("John Doe");
      await wrapper.find('input[type="email"]').setValue("taken@example.com");

      vi.advanceTimersByTime(500);
      await flushPromises();

      await wrapper.find("input#password").setValue("StrongPass1!");
      await wrapper
        .find("input#password_confirmation")
        .setValue("StrongPass1!");

      await wrapper.find("form").trigger("submit");
      await flushPromises();

      expect(wrapper.text()).toContain("Registration failed");
    });
  });

  describe("Password Visibility Toggle", () => {
    it("should have password visibility toggle buttons", () => {
      // Both password fields should have toggle buttons
      const passwordContainer =
        wrapper.find("input#password").element.parentElement;
      const confirmContainer = wrapper.find("input#password_confirmation")
        .element.parentElement;

      expect(passwordContainer.querySelector("button")).toBeTruthy();
      expect(confirmContainer.querySelector("button")).toBeTruthy();
    });

    it("should toggle password visibility state", async () => {
      expect(wrapper.vm.showPassword).toBe(false);
      wrapper.vm.showPassword = true;
      expect(wrapper.vm.showPassword).toBe(true);
    });

    it("should toggle confirm password visibility state", async () => {
      expect(wrapper.vm.showConfirmPassword).toBe(false);
      wrapper.vm.showConfirmPassword = true;
      expect(wrapper.vm.showConfirmPassword).toBe(true);
    });
  });

  describe("Form State", () => {
    it("should have correct initial state", () => {
      expect(wrapper.vm.form.name).toBe("");
      expect(wrapper.vm.form.email).toBe("");
      expect(wrapper.vm.form.phone).toBe("");
      expect(wrapper.vm.form.password).toBe("");
      expect(wrapper.vm.form.password_confirmation).toBe("");
    });
  });
});
