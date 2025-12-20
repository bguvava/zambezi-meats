/**
 * Forgot Password Page Tests
 *
 * Tests for the ForgotPasswordPage.vue component.
 *
 * @requirement AUTH-018 Create ForgotPasswordPage.vue
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { setActivePinia, createPinia } from "pinia";
import ForgotPasswordPage from "@/pages/auth/ForgotPasswordPage.vue";

// Mock vue-router
vi.mock("vue-router", () => ({
  useRoute: () => ({
    query: {},
  }),
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
const mockForgotPassword = vi.fn();

vi.mock("@/composables/useAuth", () => ({
  useAuth: () => ({
    forgotPassword: mockForgotPassword,
    isLoading: { value: false },
  }),
}));

describe("ForgotPasswordPage.vue", () => {
  let wrapper;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();

    wrapper = mount(ForgotPasswordPage, {
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

  describe("Rendering", () => {
    it("should render page title", () => {
      expect(wrapper.text()).toContain("Reset your password");
    });

    it("should render email input", () => {
      expect(wrapper.find('input[type="email"]').exists()).toBe(true);
    });

    it("should render submit button", () => {
      expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
      // Button shows either "Sending..." or "Send Reset Link" depending on loading state
      const buttonText = wrapper.find('button[type="submit"]').text();
      expect(
        buttonText.includes("Send") || buttonText.includes("Sending")
      ).toBe(true);
    });

    it("should render back to login link", () => {
      expect(wrapper.text()).toContain("Back to Sign In");
    });

    it("should render instruction text", () => {
      expect(wrapper.text()).toContain("we'll send you a link");
    });
  });

  describe("Form Validation", () => {
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

    it("should clear email error for valid email", async () => {
      const emailInput = wrapper.find('input[type="email"]');
      await emailInput.setValue("valid@example.com");
      await emailInput.trigger("blur");

      expect(wrapper.text()).not.toContain("Email is required");
      expect(wrapper.text()).not.toContain("valid email address");
    });
  });

  describe("Form Submission", () => {
    it("should not submit with invalid email", async () => {
      await wrapper.find("form").trigger("submit");
      expect(mockForgotPassword).not.toHaveBeenCalled();
    });

    it("should submit with valid email", async () => {
      mockForgotPassword.mockResolvedValueOnce({
        success: true,
        message: "Reset link sent",
      });

      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(mockForgotPassword).toHaveBeenCalledWith("test@example.com");
    });

    it("should show success message on success", async () => {
      mockForgotPassword.mockResolvedValueOnce({
        success: true,
        message: "Password reset link sent to your email.",
      });

      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(wrapper.text()).toContain("Check your email");
      expect(wrapper.text()).toContain("Password reset link sent");
    });

    it("should show error message on failure", async () => {
      mockForgotPassword.mockResolvedValueOnce({
        success: false,
        message: "We couldn't find a user with that email address.",
      });

      await wrapper
        .find('input[type="email"]')
        .setValue("nonexistent@example.com");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(wrapper.text()).toContain("couldn't find a user");
    });

    it("should hide form after successful submission", async () => {
      mockForgotPassword.mockResolvedValueOnce({
        success: true,
        message: "Reset link sent",
      });

      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      // Form should be hidden, success message shown
      expect(wrapper.find("form").exists()).toBe(false);
    });
  });

  describe("Success State", () => {
    beforeEach(async () => {
      mockForgotPassword.mockResolvedValueOnce({
        success: true,
        message: "Password reset link sent to your email.",
      });

      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("form").trigger("submit");
      await flushPromises();
    });

    it("should show success icon", () => {
      expect(wrapper.find(".bg-green-50").exists()).toBe(true);
    });

    it("should show check email heading", () => {
      expect(wrapper.text()).toContain("Check your email");
    });

    it("should show try again button", () => {
      expect(wrapper.text()).toContain("try again");
    });

    it("should allow trying again", async () => {
      // Find and click try again button
      const tryAgainButton = wrapper.find('button[type="button"]');
      await tryAgainButton.trigger("click");

      // Form should be visible again
      expect(wrapper.find("form").exists()).toBe(true);
    });
  });

  describe("Form State", () => {
    it("should have correct initial state", () => {
      expect(wrapper.vm.email).toBe("");
      expect(wrapper.vm.success).toBe(false);
      expect(wrapper.vm.error).toBe("");
    });

    it("should update email on input", async () => {
      await wrapper.find('input[type="email"]').setValue("test@example.com");
      expect(wrapper.vm.email).toBe("test@example.com");
    });

    it("should clear error on input", async () => {
      // First set an error
      wrapper.vm.error = "Some error";
      await wrapper.vm.$nextTick();

      // Then type in the email field
      await wrapper.find('input[type="email"]').trigger("input");

      expect(wrapper.vm.error).toBe("");
    });
  });

  describe("Computed Properties", () => {
    it("should be valid with valid email", async () => {
      await wrapper.find('input[type="email"]').setValue("valid@example.com");
      expect(wrapper.vm.isFormValid).toBe(true);
    });

    it("should be invalid with invalid email", async () => {
      await wrapper.find('input[type="email"]').setValue("invalid");
      await wrapper.find('input[type="email"]').trigger("blur");
      expect(wrapper.vm.isFormValid).toBe(false);
    });

    it("should be invalid with empty email", () => {
      // isFormValid is a computed based on email value and emailError
      // With empty email, it should be falsy (either false or empty string evaluates to false)
      expect(!!wrapper.vm.isFormValid).toBe(false);
    });
  });
});
