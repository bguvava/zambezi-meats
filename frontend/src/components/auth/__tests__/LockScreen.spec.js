import { describe, it, expect, vi, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import { createPinia, setActivePinia } from "pinia";
import LockScreen from "@/components/auth/LockScreen.vue";
import { useAuthStore } from "@/stores/auth";

// Mock toast notifications
vi.mock("vue-toastification", () => ({
  useToast: () => ({
    success: vi.fn(),
    error: vi.fn(),
    warning: vi.fn(),
    info: vi.fn(),
  }),
}));

describe("LockScreen.vue", () => {
  let authStore;

  beforeEach(() => {
    setActivePinia(createPinia());
    authStore = useAuthStore();

    // Mock user data
    authStore.user = {
      id: 1,
      name: "John Doe",
      email: "john@example.com",
      role: "customer",
    };
  });

  it("renders when show prop is true", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    expect(wrapper.find(".lock-screen-overlay").exists()).toBe(true);
  });

  it("does not render when show prop is false", () => {
    const wrapper = mount(LockScreen, {
      props: { show: false },
    });

    expect(wrapper.find(".lock-screen-overlay").exists()).toBe(false);
  });

  it("displays user name correctly", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    expect(wrapper.text()).toContain("John Doe");
  });

  it("displays user email correctly", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    expect(wrapper.text()).toContain("john@example.com");
  });

  it("displays user initials in avatar", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const avatar = wrapper.find(".user-avatar");
    expect(avatar.text()).toBe("JD");
  });

  it("computes correct initials for single name", () => {
    authStore.user.name = "John";

    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const avatar = wrapper.find(".user-avatar");
    expect(avatar.text()).toBe("JO");
  });

  it("has password input field", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const passwordInput = wrapper.find('input[type="password"]');
    expect(passwordInput.exists()).toBe(true);
  });

  it("toggles password visibility", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const toggleButton = wrapper.find(".password-toggle");
    const passwordInput = wrapper.find("input");

    expect(passwordInput.attributes("type")).toBe("password");

    await toggleButton.trigger("click");
    expect(passwordInput.attributes("type")).toBe("text");

    await toggleButton.trigger("click");
    expect(passwordInput.attributes("type")).toBe("password");
  });

  it("displays unlock button", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const unlockButton = wrapper.find("button.unlock-btn");
    expect(unlockButton.exists()).toBe(true);
    expect(unlockButton.text()).toContain("Unlock Session");
  });

  it("displays logout button", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const logoutButton = wrapper.find("button.logout-btn");
    expect(logoutButton.exists()).toBe(true);
    expect(logoutButton.text()).toContain("Logout");
  });

  it("prevents unlock with empty password", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const unlockButton = wrapper.find("button.unlock-btn");
    await unlockButton.trigger("click");

    // Should show error and not emit unlock event
    expect(wrapper.emitted("unlock")).toBeFalsy();
  });

  it("emits unlock event with password on unlock click", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const passwordInput = wrapper.find("input");
    await passwordInput.setValue("password123");

    const unlockButton = wrapper.find("button.unlock-btn");
    await unlockButton.trigger("click");

    // Wait for async operations
    await wrapper.vm.$nextTick();

    // Should emit unlock with password
    expect(wrapper.emitted("unlock")).toBeTruthy();
  });

  it("emits logout event on logout click", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const logoutButton = wrapper.find("button.logout-btn");
    await logoutButton.trigger("click");

    expect(wrapper.emitted("logout")).toBeTruthy();
  });

  it("auto-focuses password input when shown", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: false },
      attachTo: document.body,
    });

    await wrapper.setProps({ show: true });
    await wrapper.vm.$nextTick();

    const passwordInput = wrapper.find("input");
    expect(document.activeElement).toBe(passwordInput.element);

    wrapper.unmount();
  });

  it("displays inactivity warning message", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    expect(wrapper.text()).toContain("session has been locked");
    expect(wrapper.text()).toContain("5 minutes");
  });

  it("shows loading state during unlock", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const passwordInput = wrapper.find("input");
    await passwordInput.setValue("password123");

    const unlockButton = wrapper.find("button.unlock-btn");

    // Start unlock
    await unlockButton.trigger("click");

    // Should show loading
    expect(wrapper.vm.isUnlocking).toBe(true);
    expect(unlockButton.attributes("disabled")).toBeDefined();
  });

  it("clears password after failed unlock", async () => {
    // Mock authStore.unlockSession to fail
    authStore.unlockSession = vi.fn().mockResolvedValue({
      success: false,
      message: "Invalid password",
    });

    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const passwordInput = wrapper.find("input");
    await passwordInput.setValue("wrong-password");

    const unlockButton = wrapper.find("button.unlock-btn");
    await unlockButton.trigger("click");

    // Wait for async operations
    await wrapper.vm.$nextTick();

    // Password should be cleared
    expect(passwordInput.element.value).toBe("");
  });

  it("disables buttons during unlock", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const passwordInput = wrapper.find("input");
    await passwordInput.setValue("password123");

    // Set loading state
    wrapper.vm.isUnlocking = true;
    await wrapper.vm.$nextTick();

    const unlockButton = wrapper.find("button.unlock-btn");
    const logoutButton = wrapper.find("button.logout-btn");

    expect(unlockButton.attributes("disabled")).toBeDefined();
    expect(logoutButton.attributes("disabled")).toBeDefined();
  });

  it("supports Enter key to unlock", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const passwordInput = wrapper.find("input");
    await passwordInput.setValue("password123");

    await passwordInput.trigger("keyup.enter");
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted("unlock")).toBeTruthy();
  });

  it("displays brand colors correctly", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const header = wrapper.find(".lock-screen-header");

    // Check that gradient is applied (class-based)
    expect(header.classes()).toContain("bg-gradient-to-r");
  });

  it("uses Teleport to render in body", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
      attachTo: document.body,
    });

    // Component should be teleported to body
    const teleportedContent = document.querySelector(".lock-screen-overlay");
    expect(teleportedContent).toBeTruthy();

    wrapper.unmount();
  });

  it("handles missing user gracefully", () => {
    authStore.user = null;

    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    // Should still render without crashing
    expect(wrapper.exists()).toBe(true);
  });

  it("displays help text about forgotten passwords", () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    expect(wrapper.text()).toContain("forgotten your password");
    expect(wrapper.text()).toContain("Logout");
  });

  it("clears password when component is hidden", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    const passwordInput = wrapper.find("input");
    await passwordInput.setValue("password123");

    await wrapper.setProps({ show: false });
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.password).toBe("");
  });

  it("resets state when component is shown again", async () => {
    const wrapper = mount(LockScreen, {
      props: { show: true },
    });

    // Set some state
    wrapper.vm.password = "test";
    wrapper.vm.showPassword = true;
    wrapper.vm.isUnlocking = true;

    await wrapper.setProps({ show: false });
    await wrapper.setProps({ show: true });
    await wrapper.vm.$nextTick();

    // State should be reset
    expect(wrapper.vm.password).toBe("");
    expect(wrapper.vm.showPassword).toBe(false);
    expect(wrapper.vm.isUnlocking).toBe(false);
  });
});
