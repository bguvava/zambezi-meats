import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import MenuItem from "../../components/navigation/MenuItem.vue";
import { createRouter, createMemoryHistory } from "vue-router";

const createMockRouter = (currentPath = "/") => {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", component: { template: "<div>Home</div>" } },
      { path: "/users", component: { template: "<div>Users</div>" } },
      { path: "/products", component: { template: "<div>Products</div>" } },
    ],
  });
};

describe("MenuItem.vue", () => {
  let wrapper;
  let router;

  beforeEach(async () => {
    router = createMockRouter();
    await router.push("/");
    await router.isReady();
  });

  it("renders the menu item", () => {
    wrapper = mount(MenuItem, {
      props: {
        icon: { name: "Home" },
        label: "Dashboard",
        to: "/",
      },
      global: {
        plugins: [router],
        stubs: {
          RouterLink: true,
        },
      },
    });

    expect(wrapper.exists()).toBe(true);
  });

  it("accepts icon, label, and route props", () => {
    wrapper = mount(MenuItem, {
      props: {
        icon: { name: "Home" },
        label: "Dashboard",
        to: "/",
        badge: 5,
      },
      global: {
        plugins: [router],
        stubs: {
          RouterLink: true,
        },
      },
    });

    // Component should render without errors
    expect(wrapper.vm).toBeDefined();
    expect(wrapper.props("label")).toBe("Dashboard");
    expect(wrapper.props("to")).toBe("/");
    expect(wrapper.props("badge")).toBe(5);
  });

  it("applies transition classes", () => {
    wrapper = mount(MenuItem, {
      props: {
        icon: { name: "Home" },
        label: "Dashboard",
        to: "/",
      },
      global: {
        plugins: [router],
        stubs: {
          RouterLink: true,
        },
      },
    });

    expect(wrapper.find(".transition-all").exists()).toBe(true);
  });

  it("supports roles prop as array", () => {
    wrapper = mount(MenuItem, {
      props: {
        icon: { name: "Users" },
        label: "Users",
        to: "/users",
        roles: ["admin", "staff"],
      },
      global: {
        plugins: [router],
        stubs: {
          RouterLink: true,
        },
      },
    });

    expect(wrapper.exists()).toBe(true);
  });
});
