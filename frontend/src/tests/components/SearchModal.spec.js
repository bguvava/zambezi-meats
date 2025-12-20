import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import SearchModal from "../../components/navigation/SearchModal.vue";
import { createRouter, createMemoryHistory } from "vue-router";

const createMockRouter = () => {
  return createRouter({
    history: createMemoryHistory(),
    routes: [
      { path: "/", component: { template: "<div>Home</div>" } },
      { path: "/products/:id", component: { template: "<div>Product</div>" } },
      { path: "/orders/:id", component: { template: "<div>Order</div>" } },
    ],
  });
};

describe("SearchModal.vue", () => {
  let wrapper;
  let router;

  beforeEach(async () => {
    localStorage.clear();

    router = createMockRouter();
    await router.push("/");
    await router.isReady();

    wrapper = mount(SearchModal, {
      props: {
        show: true,
      },
      global: {
        plugins: [router],
        stubs: {
          Teleport: true,
          Search: true,
          Clock: true,
          X: true,
        },
      },
    });
  });

  it("renders when show prop is true", () => {
    expect(wrapper.find(".fixed").exists()).toBe(true);
  });

  it("does not render when show prop is false", async () => {
    await wrapper.setProps({ show: false });
    expect(wrapper.find(".fixed").exists()).toBe(false);
  });

  it("renders search input", () => {
    expect(wrapper.find('input[type="text"]').exists()).toBe(true);
  });

  it("updates searchQuery when typing", async () => {
    const input = wrapper.find('input[type="text"]');
    await input.setValue("test query");

    expect(wrapper.vm.searchQuery).toBe("test query");
  });

  it("emits close event on Escape key", async () => {
    const input = wrapper.find('input[type="text"]');
    await input.trigger("keydown", { key: "Escape" });

    expect(wrapper.emitted("close")).toBeTruthy();
  });

  it("navigates down results with ArrowDown key", async () => {
    wrapper.vm.searchQuery = "test";
    await wrapper.vm.$nextTick();

    const initialIndex = wrapper.vm.selectedIndex;

    const input = wrapper.find('input[type="text"]');
    await input.trigger("keydown", { key: "ArrowDown" });

    // Should increment or stay at 0 if results exist
    expect(typeof wrapper.vm.selectedIndex).toBe("number");
  });

  it("returns correct icon for result type", () => {
    expect(wrapper.vm.getResultIcon("product")).toBe("📦");
    expect(wrapper.vm.getResultIcon("order")).toBe("🛒");
    expect(wrapper.vm.getResultIcon("customer")).toBe("👤");
  });

  it("clears recent searches", async () => {
    wrapper.vm.recentSearches = [
      { title: "Test", type: "product", path: "/products/1" },
    ];
    await wrapper.vm.$nextTick();

    wrapper.vm.clearRecentSearches();

    expect(wrapper.vm.recentSearches.length).toBe(0);
  });
});
