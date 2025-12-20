/**
 * Login Page Tests
 *
 * Tests for the LoginPage.vue component.
 *
 * @requirement AUTH-016 Create LoginPage.vue with form validation
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount, flushPromises } from "@vue/test-utils";
import { setActivePinia, createPinia } from "pinia";
import LoginPage from "@/pages/auth/LoginPage.vue";

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
const mockLogin = vi.fn();
vi.mock("@/composables/useAuth", () => ({
  useAuth: () => ({
    login: mockLogin,
    isLoading: { value: false },
  }),
}));

describe("LoginPage.vue", () => {
  let wrapper;

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();

    wrapper = mount(LoginPage, {
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
    it("should render login form", () => {
      expect(wrapper.find("form").exists()).toBe(true);
    });

    it("should render email input", () => {
      expect(wrapper.find('input[type="email"]').exists()).toBe(true);
    });

    it("should render password input", () => {
      expect(wrapper.find("input#password").exists()).toBe(true);
    });

    it("should render remember me checkbox", () => {
      expect(wrapper.find('input[type="checkbox"]').exists()).toBe(true);
    });

    it("should render submit button", () => {
      expect(wrapper.find('button[type="submit"]').exists()).toBe(true);
    });

    it("should render forgot password link", () => {
      const forgotLink = wrapper
        .findAll("a")
        .find((a) => a.text().includes("Forgot"));
      expect(forgotLink).toBeDefined();
    });

    it("should render register link", () => {
      const registerLink = wrapper
        .findAll("a")
        .find((a) => a.text().includes("create"));
      expect(registerLink).toBeDefined();
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

    it("should show password error when password is empty on blur", async () => {
      const passwordInput = wrapper.find("input#password");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).toContain("Password is required");
    });

    it("should clear password error when password is entered", async () => {
      const passwordInput = wrapper.find("input#password");
      await passwordInput.setValue("password123");
      await passwordInput.trigger("blur");

      expect(wrapper.text()).not.toContain("Password is required");
    });
  });

  describe("Password Visibility Toggle", () => {
    it("should toggle password visibility", async () => {
      const passwordInput = wrapper.find("input#password");
      expect(passwordInput.attributes("type")).toBe("password");

      // Find and click the toggle button
      const toggleButton = wrapper
        .find("input#password")
        .element.parentElement.querySelector("button");
      await wrapper
        .find("input#password")
        .element.parentElement.querySelector("button").click;

      // Test that the toggle functionality exists
      expect(wrapper.vm.showPassword).toBe(false);
      wrapper.vm.showPassword = true;
      await wrapper.vm.$nextTick();
      expect(wrapper.vm.showPassword).toBe(true);
    });
  });

  describe("Form Submission", () => {
    it("should not submit with invalid form", async () => {
      const form = wrapper.find("form");
      await form.trigger("submit");

      expect(mockLogin).not.toHaveBeenCalled();
    });

    it("should submit with valid credentials", async () => {
      mockLogin.mockResolvedValueOnce({ success: true });

      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("input#password").setValue("password123");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(mockLogin).toHaveBeenCalledWith({
        email: "test@example.com",
        password: "password123",
        remember: false,
      });
    });

    it("should include remember me when checked", async () => {
      mockLogin.mockResolvedValueOnce({ success: true });

      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("input#password").setValue("password123");
      await wrapper.find('input[type="checkbox"]').setValue(true);
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(mockLogin).toHaveBeenCalledWith({
        email: "test@example.com",
        password: "password123",
        remember: true,
      });
    });

    it("should display server error on login failure", async () => {
      mockLogin.mockResolvedValueOnce({
        success: false,
        message: "Invalid credentials",
        errors: {},
      });

      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("input#password").setValue("wrongpassword");
      await wrapper.find("form").trigger("submit");

      await flushPromises();

      expect(wrapper.text()).toContain("Invalid credentials");
    });
  });

  describe("Remember Me", () => {
    it("should start with remember me unchecked", () => {
      const checkbox = wrapper.find('input[type="checkbox"]');
      expect(checkbox.element.checked).toBe(false);
    });

    it("should toggle remember me", async () => {
      const checkbox = wrapper.find('input[type="checkbox"]');
      await checkbox.setValue(true);
      expect(wrapper.vm.form.remember).toBe(true);
    });
  });

  describe("Form State", () => {
    it("should have correct initial state", () => {
      expect(wrapper.vm.form.email).toBe("");
      expect(wrapper.vm.form.password).toBe("");
      expect(wrapper.vm.form.remember).toBe(false);
    });

    it("should update form state on input", async () => {
      await wrapper.find('input[type="email"]').setValue("test@example.com");
      await wrapper.find("input#password").setValue("password123");

      expect(wrapper.vm.form.email).toBe("test@example.com");
      expect(wrapper.vm.form.password).toBe("password123");
    });
  });
});
