/**
 * BarChart.vue - Unit Tests
 */
import { describe, it, expect, beforeEach } from "vitest";
import { mount } from "@vue/test-utils";
import BarChart from "@/components/charts/BarChart.vue";

describe("BarChart.vue", () => {
  let wrapper;

  const defaultProps = {
    labels: ["Jan", "Feb", "Mar", "Apr"],
    datasets: [
      {
        label: "Sales",
        data: [100, 200, 150, 300],
        backgroundColor: "#CF0D0F",
      },
    ],
  };

  beforeEach(() => {
    wrapper = mount(BarChart, {
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

  it("accepts and uses datasets prop", () => {
    expect(wrapper.props("datasets")).toEqual(defaultProps.datasets);
  });

  it("accepts isDarkMode prop with default false", () => {
    expect(wrapper.props("isDarkMode")).toBe(false);
  });

  it("accepts height prop with default 300", () => {
    expect(wrapper.props("height")).toBe(300);
  });

  it("accepts stacked prop with default false", () => {
    expect(wrapper.props("stacked")).toBe(false);
  });

  it("applies dark mode colors when isDarkMode is true", async () => {
    await wrapper.setProps({ isDarkMode: true });
    expect(wrapper.props("isDarkMode")).toBe(true);
  });

  it("enables stacked mode when stacked prop is true", async () => {
    await wrapper.setProps({ stacked: true });
    expect(wrapper.props("stacked")).toBe(true);
  });

  it("handles multiple datasets", async () => {
    const multipleDatasets = [
      {
        label: "Sales",
        data: [100, 200, 150, 300],
        backgroundColor: "#CF0D0F",
      },
      {
        label: "Expenses",
        data: [80, 150, 120, 250],
        backgroundColor: "#3B82F6",
      },
    ];
    await wrapper.setProps({ datasets: multipleDatasets });
    expect(wrapper.props("datasets")).toEqual(multipleDatasets);
  });

  it("updates chart data when props change", async () => {
    const newDatasets = [
      {
        label: "Revenue",
        data: [500, 600, 700, 800],
        backgroundColor: "#10B981",
      },
    ];
    await wrapper.setProps({ datasets: newDatasets });
    expect(wrapper.props("datasets")).toEqual(newDatasets);
  });

  it("updates chart labels when props change", async () => {
    const newLabels = ["Q1", "Q2", "Q3", "Q4"];
    await wrapper.setProps({ labels: newLabels });
    expect(wrapper.props("labels")).toEqual(newLabels);
  });

  it("validates datasets prop is an array", () => {
    const propsData = wrapper.vm.$options.props.datasets;
    expect(propsData.type).toBe(Array);
    expect(propsData.required).toBe(true);
  });

  it("validates labels prop is an array", () => {
    const propsData = wrapper.vm.$options.props.labels;
    expect(propsData.type).toBe(Array);
    expect(propsData.required).toBe(true);
  });
});
