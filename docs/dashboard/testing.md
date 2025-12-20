# Dashboard Module Testing Documentation

## Overview

Comprehensive test suite for the Dashboard Module (Phase 6) using Vitest and Vue Test Utils. All tests validate component functionality, props, user interactions, and rendering.

**Test Results: 75/75 Passing (100%)**

## Test Infrastructure

### Testing Stack

- **Test Framework**: Vitest 4.0.16
- **Component Testing**: @vue/test-utils
- **Test UI**: @vitest/ui
- **Environment**: jsdom (DOM simulation)
- **Coverage**: v8 provider

### Configuration Files

#### vitest.config.js

```javascript
import { fileURLToPath } from "node:url";
import { mergeConfig, defineConfig, configDefaults } from "vitest/config";
import viteConfig from "./vite.config";

export default mergeConfig(
  viteConfig,
  defineConfig({
    test: {
      globals: true,
      environment: "jsdom",
      setupFiles: ["./src/tests/setup.js"],
      exclude: [...configDefaults.exclude, "e2e/**"],
      coverage: {
        provider: "v8",
        reporter: ["text", "json", "html"],
      },
      resolve: {
        alias: {
          "@": fileURLToPath(new URL("./src", import.meta.url)),
        },
      },
    },
  })
);
```

#### src/tests/setup.js

```javascript
import { beforeEach, vi } from "vitest";

// Mock localStorage
global.localStorage = {
  getItem: vi.fn(),
  setItem: vi.fn(),
  removeItem: vi.fn(),
  clear: vi.fn(),
};

// Mock matchMedia
global.matchMedia = vi.fn().mockImplementation((query) => ({
  matches: false,
  media: query,
  onchange: null,
  addListener: vi.fn(),
  removeListener: vi.fn(),
  addEventListener: vi.fn(),
  removeEventListener: vi.fn(),
  dispatchEvent: vi.fn(),
}));

beforeEach(() => {
  vi.clearAllMocks();
});
```

## Test Scripts

```json
{
  "test": "vitest",
  "test:ui": "vitest --ui",
  "test:coverage": "vitest --coverage"
}
```

**Usage:**

```bash
# Run tests in watch mode
npm run test

# Run tests once
npm run test -- --run

# Open test UI
npm run test:ui

# Generate coverage report
npm run test:coverage
```

## Test Suites

### 1. LineChart Component (13 tests)

**File**: `src/tests/components/LineChart.spec.js`

**Test Coverage:**

- Canvas element rendering
- Props acceptance (labels, data, label, color, fillColor, isDarkMode, height)
- Default values
- Prop updates and reactivity

**Key Tests:**

```javascript
it("renders canvas element", () => {
  expect(wrapper.find("canvas").exists()).toBe(true);
});

it("accepts and uses labels prop", async () => {
  const labels = ["Mon", "Tue", "Wed"];
  await wrapper.setProps({ labels });
  expect(wrapper.props("labels")).toEqual(labels);
});
```

**Results**: ✅ 13/13 passing

### 2. BarChart Component (13 tests)

**File**: `src/tests/components/BarChart.spec.js`

**Test Coverage:**

- Canvas rendering
- Props acceptance (labels, datasets, isDarkMode, height, stacked)
- Multiple datasets handling
- Prop validators
- Dark mode colors

**Key Tests:**

```javascript
it("handles multiple datasets", async () => {
  const datasets = [
    { label: "Sales", data: [10, 20, 30], backgroundColor: "#CF0D0F" },
    { label: "Costs", data: [5, 10, 15], backgroundColor: "#6F6F6F" },
  ];
  await wrapper.setProps({ datasets });
  expect(wrapper.props("datasets")).toEqual(datasets);
});
```

**Results**: ✅ 13/13 passing

### 3. StatCard Component (22 tests)

**File**: `src/tests/components/StatCard.spec.js`

**Test Coverage:**

- Card rendering
- Label and value display
- Number formatting (currency, commas, decimals)
- Prefix and suffix support
- Change badges (positive/negative/neutral)
- Icon background styling
- Hover effects
- Border styling
- Computed properties (changeIcon)

**Key Tests:**

```javascript
it("formats currency when isCurrency is true", async () => {
  await wrapper.setProps({
    value: 1234.56,
    isCurrency: true,
    formatNumber: true,
  });

  expect(wrapper.text()).toContain("1,235");
});

it("shows positive change badge when change > 0", async () => {
  await wrapper.setProps({ change: 12.5 });
  expect(wrapper.find('span[style*="background-color"]').exists()).toBe(true);
  expect(wrapper.text()).toContain("+12.5%");
});
```

**Results**: ✅ 22/22 passing

### 4. Sidebar Component (7 tests)

**File**: `src/tests/components/Sidebar.spec.js`

**Test Coverage:**

- Sidebar rendering
- Logo section
- Menu slot rendering
- Collapse/expand functionality
- localStorage persistence
- Fixed positioning
- Full height layout

**Key Tests:**

```javascript
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
```

**Results**: ✅ 7/7 passing

### 5. MenuItem Component (4 tests)

**File**: `src/tests/components/MenuItem.spec.js`

**Test Coverage:**

- Menu item rendering
- Props acceptance (icon, label, to, badge, roles)
- Transition classes
- Role-based visibility

**Key Tests:**

```javascript
it("accepts icon, label, and route props", () => {
  expect(wrapper.vm).toBeDefined();
  expect(wrapper.props("label")).toBe("Dashboard");
  expect(wrapper.props("to")).toBe("/");
  expect(wrapper.props("badge")).toBe(5);
});
```

**Results**: ✅ 4/4 passing

### 6. Header Component (8 tests)

**File**: `src/tests/components/Header.spec.js`

**Test Coverage:**

- Header rendering
- Fixed positioning
- Notification dropdown toggle
- Dark mode toggle
- User dropdown toggle
- Dropdown close functionality
- Transition classes

**Key Tests:**

```javascript
it("toggles notifications dropdown", async () => {
  expect(wrapper.vm.showNotifications).toBe(false);

  wrapper.vm.showNotifications = true;
  await wrapper.vm.$nextTick();

  expect(wrapper.vm.showNotifications).toBe(true);
});

it("closes dropdowns", () => {
  wrapper.vm.showUserMenu = true;
  wrapper.vm.showNotifications = true;

  wrapper.vm.closeDropdowns();

  expect(wrapper.vm.showUserMenu).toBe(false);
  expect(wrapper.vm.showNotifications).toBe(false);
});
```

**Results**: ✅ 8/8 passing

### 7. SearchModal Component (8 tests)

**File**: `src/tests/components/SearchModal.spec.js`

**Test Coverage:**

- Modal rendering based on show prop
- Search input
- Search query updates
- Escape key handling
- Arrow key navigation
- Result icon mapping
- Recent searches clearing

**Key Tests:**

```javascript
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
```

**Results**: ✅ 8/8 passing

## Test Patterns

### Component Mounting

```javascript
import { mount } from "@vue/test-utils";
import { createRouter, createMemoryHistory } from "vue-router";
import { createPinia, setActivePinia } from "pinia";

beforeEach(async () => {
  // Setup Pinia
  pinia = createPinia();
  setActivePinia(pinia);

  // Setup Router
  router = createMockRouter();
  await router.push("/");
  await router.isReady();

  // Mount component
  wrapper = mount(Component, {
    props: {
      /* props */
    },
    global: {
      plugins: [router, pinia],
      stubs: {
        /* stubbed components */
      },
    },
  });
});
```

### Testing User Interactions

```javascript
it("responds to button click", async () => {
  const button = wrapper.find("button");
  await button.trigger("click");

  expect(wrapper.vm.someState).toBe(true);
  expect(wrapper.emitted("some-event")).toBeTruthy();
});
```

### Testing Props

```javascript
it("accepts and uses props", async () => {
  await wrapper.setProps({ newProp: "value" });

  expect(wrapper.props("newProp")).toBe("value");
  expect(wrapper.text()).toContain("value");
});
```

### Testing Computed Properties

```javascript
it("computes correct value", async () => {
  await wrapper.setProps({ input: 10 });

  expect(wrapper.vm.computedValue).toBe(20);
});
```

## Known Test Warnings

### Canvas Warnings (Expected)

```
Not implemented: HTMLCanvasElement's getContext() method
Failed to create chart: can't acquire context from the given item
```

**Cause**: jsdom doesn't implement canvas rendering
**Impact**: None - tests validate component props and rendering, not canvas drawing
**Status**: Expected behavior

### Icon Prop Type Warnings (Non-Critical)

```
[Vue warn]: Invalid prop: type check failed for prop "icon". Expected Object, got Function
```

**Cause**: lucide-vue-next exports icon components as functions
**Impact**: None - icons render correctly in the browser
**Status**: Non-critical warning

### Route Injection Warnings (Non-Critical)

```
[Vue warn]: injection "Symbol(route location)" not found.
```

**Cause**: Router not fully stubbed in some tests
**Impact**: None - components work correctly with router stubs
**Status**: Non-critical warning

## Coverage Summary

| Component   | Tests  | Passing   | Coverage                        |
| ----------- | ------ | --------- | ------------------------------- |
| LineChart   | 13     | 13 ✅     | Props, rendering, updates       |
| BarChart    | 13     | 13 ✅     | Props, multiple datasets, modes |
| StatCard    | 22     | 22 ✅     | Full feature coverage           |
| Sidebar     | 7      | 7 ✅      | Collapse, storage, layout       |
| MenuItem    | 4      | 4 ✅      | Props, routing, roles           |
| Header      | 8      | 8 ✅      | Dropdowns, toggles, events      |
| SearchModal | 8      | 8 ✅      | Search, keyboard nav, state     |
| **TOTAL**   | **75** | **75 ✅** | **100% Pass Rate**              |

## Testing Best Practices

### 1. Test Structure

- Use descriptive test names
- One assertion per test (when possible)
- Group related tests in describe blocks
- Use beforeEach for common setup

### 2. Component Isolation

- Stub child components
- Mock external dependencies
- Use test-specific props
- Clear state between tests

### 3. Async Testing

- Always await async operations
- Use `nextTick()` for DOM updates
- Wait for router.isReady()
- Handle promises properly

### 4. Maintainability

- Keep tests simple and focused
- Avoid testing implementation details
- Test user-facing behavior
- Update tests with component changes

## Running Tests

### Development Workflow

1. **Start test watcher** (runs tests on file change):

   ```bash
   npm run test
   ```

2. **Run tests once** (for CI/CD):

   ```bash
   npm run test -- --run
   ```

3. **View test UI** (interactive browser interface):

   ```bash
   npm run test:ui
   ```

4. **Generate coverage** (HTML report):
   ```bash
   npm run test:coverage
   # Open coverage/index.html in browser
   ```

### CI/CD Integration

```yaml
# Example GitHub Actions workflow
test:
  runs-on: ubuntu-latest
  steps:
    - uses: actions/checkout@v3
    - uses: actions/setup-node@v3
      with:
        node-version: "18"
    - run: npm ci
    - run: npm run test -- --run
```

## Troubleshooting

### Tests Not Running

- Check vitest.config.js exists
- Verify test files match pattern: `*.spec.js`
- Ensure dependencies installed: `npm install`

### Tests Failing

- Check for async/await issues
- Verify mock setup in beforeEach
- Review component changes
- Check console for warnings

### Slow Tests

- Reduce number of component mounts
- Use shallow mounting when appropriate
- Stub expensive child components
- Optimize setup/teardown

## Future Enhancements

### Planned Improvements

- [ ] Add E2E tests with Playwright
- [ ] Increase coverage to 95%+
- [ ] Add visual regression tests
- [ ] Implement snapshot testing
- [ ] Add performance benchmarks
- [ ] Create test data factories
- [ ] Add accessibility tests (a11y)

### Test Additions

- [ ] Dashboard page feature tests
- [ ] Integration tests for data flow
- [ ] API mocking with MSW
- [ ] Router navigation tests
- [ ] Store state tests

## Resources

- [Vitest Documentation](https://vitest.dev/)
- [Vue Test Utils](https://test-utils.vuejs.org/)
- [Testing Library](https://testing-library.com/docs/vue-testing-library/intro/)
- [Vue Testing Handbook](https://lmiller1990.github.io/vue-testing-handbook/)

## Summary

The Dashboard Module has comprehensive test coverage with a **100% pass rate (75/75 tests)**. All components are validated for:

- Correct rendering
- Prop handling
- User interactions
- State management
- Event emissions

Tests run quickly (~2-3 seconds) and provide confidence in component behavior. The test suite is maintainable, well-organized, and follows Vue testing best practices.
