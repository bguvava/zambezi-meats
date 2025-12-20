/**
 * StatCard.vue - Unit Tests
 */
import { describe, it, expect, beforeEach, vi } from "vitest";
import { mount } from "@vue/test-utils";
import StatCard from "@/components/dashboard/StatCard.vue";
import { DollarSign, TrendingUp, TrendingDown, Minus } from "lucide-vue-next";

describe("StatCard.vue", () => {
  let wrapper;

  const defaultProps = {
    icon: DollarSign,
    iconBackground: "linear-gradient(135deg, #CF0D0F 0%, #F6211F 100%)",
    label: "Total Revenue",
    value: 125000,
  };

  beforeEach(() => {
    wrapper = mount(StatCard, {
      props: defaultProps,
      global: {
        stubs: {
          DollarSign: true,
          TrendingUp: true,
          TrendingDown: true,
          Minus: true,
        },
      },
    });
  });

  it("renders the card", () => {
    expect(wrapper.find(".bg-white").exists()).toBe(true);
  });

  it("displays the label", () => {
    expect(wrapper.text()).toContain("Total Revenue");
  });

  it("displays the value", () => {
    expect(wrapper.text()).toContain("125,000");
  });

  it("renders icon component", () => {
    expect(wrapper.props("icon")).toBe(DollarSign);
  });

  it("applies icon background style", () => {
    const iconWrapper = wrapper.find('[style*="background"]');
    expect(iconWrapper.exists()).toBe(true);
    expect(iconWrapper.attributes("style")).toContain("background");
  });

  it("formats currency when isCurrency is true", async () => {
    await wrapper.setProps({ isCurrency: true });
    // Currency format adds $ symbol
    const text = wrapper.text();
    expect(text).toContain("125,000.00");
  });

  it("formats number with commas when formatNumber is true", async () => {
    await wrapper.setProps({ formatNumber: true });
    expect(wrapper.text()).toContain("125,000");
  });

  it("displays prefix when provided", async () => {
    await wrapper.setProps({ prefix: "$" });
    expect(wrapper.text()).toContain("$");
  });

  it("displays suffix when provided", async () => {
    await wrapper.setProps({ suffix: "kg" });
    expect(wrapper.text()).toContain("kg");
  });

  it("shows positive change badge when change > 0", async () => {
    await wrapper.setProps({ change: 12.5, showChange: true });
    expect(wrapper.text()).toContain("12.5%");
    // Badge uses inline styles, not classes for colors
    const badge = wrapper.find('span[style*="background-color"]');
    expect(badge.exists()).toBe(true);
  });

  it("shows negative change badge when change < 0", async () => {
    await wrapper.setProps({ change: -5.2, showChange: true });
    expect(wrapper.text()).toContain("5.2%");
    // Badge uses inline styles
    const badge = wrapper.find('span[style*="background-color"]');
    expect(badge.exists()).toBe(true);
  });

  it("shows neutral badge when change = 0", async () => {
    await wrapper.setProps({ change: 0, showChange: true });
    expect(wrapper.text()).toContain("0%");
    // Badge uses inline styles
    const badge = wrapper.find('span[style*="background-color"]');
    expect(badge.exists()).toBe(true);
  });

  it("displays comparison text when provided", async () => {
    await wrapper.setProps({
      change: 10,
      showChange: true,
      comparisonText: "vs yesterday",
    });
    expect(wrapper.text()).toContain("vs yesterday");
  });

  it("hides change badge when showChange is false", () => {
    expect(wrapper.find(".px-2.py-0\\.5").exists()).toBe(false);
  });

  it("applies hover effects", () => {
    const card = wrapper.find(".bg-white");
    expect(card.classes()).toContain("transition-all");
    expect(card.classes()).toContain("hover:shadow-xl");
    expect(card.classes()).toContain("hover:scale-105");
  });

  it("has red border", () => {
    const card = wrapper.find(".bg-white");
    expect(card.classes()).toContain("border-2");
    // Border color is applied via inline style
    expect(card.attributes("style")).toContain("border-color");
  });

  it("formats large numbers correctly", async () => {
    await wrapper.setProps({ value: 1234567, formatNumber: true });
    expect(wrapper.text()).toContain("1,234,567");
  });

  it("formats decimal values correctly", async () => {
    await wrapper.setProps({ value: 1234.56, isCurrency: true });
    expect(wrapper.text()).toContain("1,234.56");
  });

  it("handles string values", async () => {
    await wrapper.setProps({ value: "42 orders" });
    expect(wrapper.text()).toContain("42 orders");
  });

  it("computes correct change icon for positive change", async () => {
    await wrapper.setProps({ change: 15, showChange: true });
    // TrendingUp icon should be used
    expect(wrapper.vm.changeIcon).toBe(TrendingUp);
  });

  it("computes correct change icon for negative change", async () => {
    await wrapper.setProps({ change: -15, showChange: true });
    // TrendingDown icon should be used
    expect(wrapper.vm.changeIcon).toBe(TrendingDown);
  });

  it("computes correct change icon for zero change", async () => {
    await wrapper.setProps({ change: 0, showChange: true });
    // Minus icon should be used
    expect(wrapper.vm.changeIcon).toBe(Minus);
  });
});
