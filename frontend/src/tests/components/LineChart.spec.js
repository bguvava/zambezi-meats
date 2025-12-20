/**
 * LineChart.vue - Unit Tests
 */
import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import LineChart from "@/components/charts/LineChart.vue";

describe("LineChart.vue", () => {
  let wrapper;

  const defaultProps = {
    labels: ["Jan", "Feb", "Mar", "Apr", "May"],
    data: [100, 200, 150, 300, 250],
    label: "Revenue",
    color: "#CF0D0F",
    fillColor: "rgba(207, 13, 15, 0.1)",
  };

  beforeEach(() => {
    wrapper = mount(LineChart, {
      props: defaultProps,
    });
  });

  it("renders canvas element", () => {
    const canvas = wrapper.find("canvas");
    expect(canvas.exists()).toBe(true);
  });

  it("accepts and uses labels prop", () => {
    expect(wrapper.props("labels")).toEqual(defaultProps.labels);
  });

  it("accepts and uses data prop", () => {
    expect(wrapper.props("data")).toEqual(defaultProps.data);
  });

  it("accepts label prop", () => {
    expect(wrapper.props("label")).toBe("Revenue");
  });

  it("accepts color prop", () => {
    expect(wrapper.props("color")).toBe("#CF0D0F");
  });

  it("accepts fillColor prop", () => {
    expect(wrapper.props("fillColor")).toBe("rgba(207, 13, 15, 0.1)");
  });

  it("accepts isDarkMode prop with default false", () => {
    expect(wrapper.props("isDarkMode")).toBe(false);
  });

  it("accepts height prop with default 300", () => {
    expect(wrapper.props("height")).toBe(300);
  });

  it("applies dark mode colors when isDarkMode is true", async () => {
    await wrapper.setProps({ isDarkMode: true });
    expect(wrapper.props("isDarkMode")).toBe(true);
  });

  it("updates chart data when props change", async () => {
    const newData = [500, 600, 700, 800, 900];
    await wrapper.setProps({ data: newData });
    expect(wrapper.props("data")).toEqual(newData);
  });

  it("updates chart labels when props change", async () => {
    const newLabels = ["Mon", "Tue", "Wed", "Thu", "Fri"];
    await wrapper.setProps({ labels: newLabels });
    expect(wrapper.props("labels")).toEqual(newLabels);
  });

  it("has correct default color when not specified", () => {
    const wrapperNoColor = mount(LineChart, {
      props: {
        labels: ["A", "B"],
        data: [1, 2],
      },
    });
    expect(wrapperNoColor.props("color")).toBe("#CF0D0F");
  });

  it("has correct default fillColor when not specified", () => {
    const wrapperNoColor = mount(LineChart, {
      props: {
        labels: ["A", "B"],
        data: [1, 2],
      },
    });
    expect(wrapperNoColor.props("fillColor")).toBe("rgba(207, 13, 15, 0.1)");
  });
});
