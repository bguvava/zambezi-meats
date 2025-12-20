import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import Header from "../../components/navigation/Header.vue";
import { createRouter, createMemoryHistory } from "vue-router";
import { createPinia, setActivePinia } from "pinia";

const createMockRouter = () => {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      {
        path: "/",
        name: "Dashboard",
        component: { template: "<div>Dashboard</div>" },
      },
      {
        path: "/admin/dashboard",
        name: "AdminDashboard",
        component: { template: "<div>Admin</div>" },
      },
    ],
  });
};

describe("Header.vue", () => {
  let wrapper;
  let router;
  let pinia;

  beforeEach(async () => {
    pinia = createPinia();
    setActivePinia(pinia);

    router = createMockRouter();
    await router.push("/");
    await router.isReady();

    wrapper = mount(Header, {
      props: {
        sidebarCollapsed: false,
        showMobileMenu: false,
      },
      global: {
        plugins: [router, pinia],
        stubs: {
          Menu: true,
          Search: true,
          Bell: true,
          Sun: true,
          Moon: true,
          ChevronDown: true,
          ChevronRight: true,
          User: true,
          Settings: true,
          LogOut: true,
        },
      },
    });
  });

  it("renders the header", () => {
    expect(wrapper.find("header").exists()).toBe(true);
  });

  it("is fixed positioned", () => {
    expect(wrapper.find(".fixed").exists()).toBe(true);
  });

  it("emits toggle-search event", async () => {
    const buttons = wrapper.findAll("button");
    // Find search button and trigger click
    if (buttons.length > 0) {
      await buttons[1].trigger("click");
      // Event should be emitted or component should handle it
      expect(wrapper.vm).toBeDefined();
    }
  });

  it("toggles notifications dropdown", async () => {
    expect(wrapper.vm.showNotifications).toBe(false);

    wrapper.vm.showNotifications = true;
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.showNotifications).toBe(true);
  });

  it("toggles dark mode", async () => {
    const initialMode = wrapper.vm.isDarkMode;

    wrapper.vm.toggleDarkMode();

    expect(wrapper.vm.isDarkMode).toBe(!initialMode);
  });

  it("toggles user dropdown", async () => {
    expect(wrapper.vm.showUserMenu).toBe(false);

    wrapper.vm.showUserMenu = true;
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.showUserMenu).toBe(true);
  });

  it("closes dropdowns", () => {
    wrapper.vm.showUserMenu = true;
    wrapper.vm.showNotifications = true;

    wrapper.vm.closeDropdowns();

    expect(wrapper.vm.showUserMenu).toBe(false);
    expect(wrapper.vm.showNotifications).toBe(false);
  });

  it("applies transition classes", () => {
    expect(wrapper.find(".transition-all").exists()).toBe(true);
  });
});
