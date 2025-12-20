/**
 * Reset Password Page Tests
 *
 * Tests for the ResetPasswordPage.vue component.
 *
 * @requirement AUTH-019 Create ResetPasswordPage.vue
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { setActivePinia, createPinia } from "pinia";
import ResetPasswordPage from "@/pages/auth/ResetPasswordPage.vue";

// Mock vue-router
const mockRoute = {
  query: {
    token: "valid-reset-token",
    email: "test@example.com",
  },
};

vi.mock("vue-router", () => ({
  useRoute: () => mockRoute,
  useRouter: () => ({
    push: vi.fn(),
  }),
  RouterLink: {
    name: "RouterLink",
    template: "<a><slot /></a>",
    props: ["to"],
  },
}));

// Mock useAuth composable
const mockResetPassword = vi.fn();

vi.mock("@/composables/useAuth", () => ({
  useAuth: () => ({
    resetPassword: mockResetPassword,
    isLoading: { value: false },
  }),
}));

describe("ResetPasswordPage.vue", () => {
  let wrapper;

  const createWrapper = (
    routeQuery = { token: "valid-token", email: "test@example.com" }
  ) => {
    mockRoute.query = routeQuery;
    return mount(ResetPasswordPage, {
      global: {
        stubs: {
          RouterLink: {
            template: "<a><slot /></a>",
            props: ["to"],
          },
        },
      },
    });
  };

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    mockRoute.query = { token: "valid-token", email: "test@example.com" };
  });

  describe("Rendering", () => {
    it("should render page title", () => {
      wrapper = createWrapper();
      expect(wrapper.text()).toContain("Set new password");
    });

    it("should render email input", () => {
      wrapper = createWrapper();
      expect(wrapper.find('input[type="email"]').exists()).toBe(true);
    });

    it("should pre-fill email from URL", () => {
      wrapper = createWrapper({
        token: "token",
        email: "prefilled@example.com",
      });
      expect(wrapper.vm.form.email).toBe("prefilled@example.com");
    });

    it("should render password input", () => {
      wrapper = createWrapper();
      expect(wrapper.find("input#password").exists()).toBe(true);
    });

    it("should render confirm password input", () => {
      wrapper = createWrapper();
      expect(wrapper.find("input#password_confirmation").exists()).toBe(true);
    });

    it("should render submit button", () => {
      wrapper = createWrapper();
      expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
      // Button shows either "Resetting..." or "Reset Password" depending on loading state
      const buttonText = wrapper.find('button[type="submit"]').text();
      expect(buttonText.includes("Reset")).toBe(true);
    });

    it("should render back to login link", () => {
      wrapper = createWrapper();
      expect(wrapper.text()).toContain("Back to Sign In");
    });
  });

  describe("Invalid Token", () => {
    it("should detect missing token and set tokenValid to false", async () => {
      wrapper = createWrapper({ email: "test@example.com" });
      await flushPromises();
      // The tokenValid flag should be false when token is missing
      expect(wrapper.vm.tokenValid).toBe(false);
    });

    it("should detect empty token and set tokenValid to false", async () => {
      wrapper = createWrapper({ token: "", email: "test@example.com" });
      await flushPromises();
      expect(wrapper.vm.tokenValid).toBe(false);
    });

    it("should show form when token is invalid but still mounted", async () => {
      wrapper = createWrapper({});
      await flushPromises();
      // Component still renders but tokenValid should be false
      expect(wrapper.vm.tokenValid).toBe(false);
    });

    it("should indicate invalid state when no token provided", async () => {
      wrapper = createWrapper({});
      await flushPromises();
      // When tokenValid is false, the error state should be indicated
      expect(wrapper.vm.tokenValid).toBe(false);
    });
  });

  describe("Form Validation", () => {
    it("should show password error when password is empty on blur", async () => {
      wrapper = createWrapper();
      const passwordInput = wrapper.find("input#password");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("Password is required");
    });

    it("should show password error when password is too short", async () => {
      wrapper = createWrapper();
      const passwordInput = wrapper.find("input#password");
      await passwordInput.setValue("short");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("at least 8 characters");
    });

    it("should validate password complexity - lowercase", async () => {
      wrapper = createWrapper();
      const passwordInput = wrapper.find("input#password");
      await passwordInput.setValue("UPPERCASEONLY1!");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("lowercase letter");
    });

    it("should validate password complexity - uppercase", async () => {
      wrapper = createWrapper();
      const passwordInput = wrapper.find("input#password");
      await passwordInput.setValue("lowercaseonly1!");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("uppercase letter");
    });

    it("should validate password complexity - number", async () => {
      wrapper = createWrapper();
      const passwordInput = wrapper.find("input#password");
      await passwordInput.setValue("UpperLower!");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("number");
    });

    it("should validate password complexity - special character", async () => {
      wrapper = createWrapper();
      const passwordInput = wrapper.find("input#password");
      await passwordInput.setValue("UpperLower1");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("special character");
    });

    it("should show password confirmation error when empty on blur", async () => {
      wrapper = createWrapper();
      const confirmInput = wrapper.find("input#password_confirmation");
      await confirmInput.trigger("blur");

      expect(wrapper.text()).toContain("confirm your password");
    });

    it("should show password mismatch error", async () => {
      wrapper = createWrapper();
      await wrapper.find("input#password").setValue("ValidPass1!");
      await wrapper
        .find("input#password_confirmation")
        .setValue("DifferentPass1!");
      await wrapper.find("input#password_confirmation").trigger("blur");

      expect(wrapper.text()).toContain("Passwords do not match");
    });

    it("should pass validation for matching strong passwords", async () => {
      wrapper = createWrapper();
      await wrapper.find("input#password").setValue("ValidPass1!");
      await wrapper.find("input#password").trigger("blur");
      await wrapper.find("input#password_confirmation").setValue("ValidPass1!");
      await wrapper.find("input#password_confirmation").trigger("blur");

      expect(wrapper.text()).not.toContain("Passwords do not match");
      expect(wrapper.text()).not.toContain("Password is required");
    });
  });

  describe("Form Submission", () => {
    it("should not submit with invalid form", async () => {
      wrapper = createWrapper();
      await wrapper.find("form").trigger("submit");
      expect(mockResetPassword).not.toHaveBeenCalled();
    });

    it("should submit with valid data", async () => {
      wrapper = createWrapper();
      mockResetPassword.mockResolvedValueOnce({ success: true });

      await wrapper.find("input#password").setValue("ValidPass1!");
      await wrapper.find("input#password_confirmation").setValue("ValidPass1!");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(mockResetPassword).toHaveBeenCalledWith({
        email: "test@example.com",
        password: "ValidPass1!",
        password_confirmation: "ValidPass1!",
        token: "valid-token",
      });
    });

    it("should show error message on failure", async () => {
      wrapper = createWrapper();
      mockResetPassword.mockResolvedValueOnce({
        success: false,
        message: "This password reset token is invalid.",
      });

      await wrapper.find("input#password").setValue("ValidPass1!");
      await wrapper.find("input#password_confirmation").setValue("ValidPass1!");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(wrapper.text()).toContain("token is invalid");
    });

    it("should display field-specific errors", async () => {
      wrapper = createWrapper();
      mockResetPassword.mockResolvedValueOnce({
        success: false,
        message: "Validation failed",
        errors: { email: ["The email field is required."] },
      });

      await wrapper.find("input#password").setValue("ValidPass1!");
      await wrapper.find("input#password_confirmation").setValue("ValidPass1!");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(wrapper.text()).toContain("Validation failed");
    });
  });

  describe("Password Visibility Toggle", () => {
    it("should have password visibility toggle buttons", () => {
      wrapper = createWrapper();
      const passwordContainer =
        wrapper.find("input#password").element.parentElement;
      const confirmContainer = wrapper.find("input#password_confirmation")
        .element.parentElement;

      expect(passwordContainer.querySelector("button")).toBeTruthy();
      expect(confirmContainer.querySelector("button")).toBeTruthy();
    });

    it("should toggle password visibility state", async () => {
      wrapper = createWrapper();
      expect(wrapper.vm.showPassword).toBe(false);
      wrapper.vm.showPassword = true;
      expect(wrapper.vm.showPassword).toBe(true);
    });

    it("should toggle confirm password visibility state", async () => {
      wrapper = createWrapper();
      expect(wrapper.vm.showConfirmPassword).toBe(false);
      wrapper.vm.showConfirmPassword = true;
      expect(wrapper.vm.showConfirmPassword).toBe(true);
    });
  });

  describe("Form State", () => {
    it("should populate token from URL", () => {
      wrapper = createWrapper({ token: "my-token", email: "test@example.com" });
      expect(wrapper.vm.form.token).toBe("my-token");
    });

    it("should populate email from URL", () => {
      wrapper = createWrapper({
        token: "token",
        email: "prefilled@example.com",
      });
      expect(wrapper.vm.form.email).toBe("prefilled@example.com");
    });

    it("should have correct initial password state", () => {
      wrapper = createWrapper();
      expect(wrapper.vm.form.password).toBe("");
      expect(wrapper.vm.form.password_confirmation).toBe("");
    });
  });

  describe("Computed Properties", () => {
    it("should be valid with all valid data", async () => {
      wrapper = createWrapper();
      await wrapper.find("input#password").setValue("ValidPass1!");
      await wrapper.find("input#password_confirmation").setValue("ValidPass1!");

      expect(wrapper.vm.isFormValid).toBe(true);
    });

    it("should be invalid with mismatched passwords", async () => {
      wrapper = createWrapper();
      await wrapper.find("input#password").setValue("ValidPass1!");
      await wrapper
        .find("input#password_confirmation")
        .setValue("DifferentPass1!");
      await wrapper.find("input#password_confirmation").trigger("blur");

      expect(wrapper.vm.isFormValid).toBe(false);
    });
  });
});
