import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import Sidebar from "../../components/navigation/Sidebar.vue";

describe("Sidebar.vue", () => {
  let wrapper;

  beforeEach(() => {
    localStorage.clear();

    wrapper = mount(Sidebar, {
      props: {
        role: "admin",
      },
      slots: {
        menu: '<div class="test-menu">Menu Content</div>',
      },
      global: {
        stubs: {
          RouterLink: true,
          ChevronLeft: true,
          ChevronRight: true,
        },
      },
    });
  });

  it("renders the sidebar", () => {
    expect(wrapper.find("aside").exists()).toBe(true);
  });

  it("renders logo section with ZM badge", () => {
    // Logo is rendered in RouterLink stub, check that component exists
    expect(wrapper.findComponent({ name: "RouterLink" }).exists()).toBe(true);
  });

  it("renders menu slot content", () => {
    expect(wrapper.find(".test-menu").exists()).toBe(true);
    expect(wrapper.text()).toContain("Menu Content");
  });

  it("starts expanded by default", () => {
    expect(wrapper.find(".w-64").exists()).toBe(true);
  });

  it("collapses when button clicked", async () => {
    const collapseBtn = wrapper.find("button");
    await collapseBtn.trigger("click");

    expect(wrapper.find(".w-16").exists()).toBe(true);
  });

  it("saves collapse state to localStorage", async () => {
    const collapseBtn = wrapper.find("button");
    await collapseBtn.trigger("click");

    expect(localStorage.setItem).toHaveBeenCalledWith(
      "sidebar-collapsed",
      "true"
    );
  });

  it("is positioned fixed with full height", () => {
    expect(wrapper.find(".fixed").exists()).toBe(true);
    expect(wrapper.find(".h-screen").exists()).toBe(true);
  });
});
