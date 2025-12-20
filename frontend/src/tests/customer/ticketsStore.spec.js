/**
 * Tickets Store Tests
 *
 * Comprehensive tests for the support tickets Pinia store.
 *
 * @requirement CUST-016 Support contact
 * @requirement CUST-017 Ticket submission
 * @requirement CUST-018 View ticket history
 */
import { describe, it, expect, beforeEach, vi, afterEach } from "vitest";
import { setActivePinia, createPinia } from "pinia";
import { useTicketsStore } from "@/stores/tickets";

// Mock API
vi.mock("@/services/api", () => ({
  default: {
    get: vi.fn(),
    post: vi.fn(),
    put: vi.fn(),
  },
}));

import api from "@/services/api";

describe("Tickets Store", () => {
  let store;

  const mockTickets = [
    {
      id: 1,
      subject: "Order Issue",
      message: "My order has not arrived",
      status: "open",
      priority: "high",
      created_at: "2024-01-15T10:00:00Z",
    },
    {
      id: 2,
      subject: "Refund Request",
      message: "I need a refund for my order",
      status: "in_progress",
      priority: "medium",
      created_at: "2024-01-14T09:00:00Z",
    },
    {
      id: 3,
      subject: "Account Question",
      message: "How do I update my email?",
      status: "resolved",
      priority: "low",
      created_at: "2024-01-13T08:00:00Z",
    },
    {
      id: 4,
      subject: "Old Issue",
      message: "This was resolved",
      status: "closed",
      priority: "low",
      created_at: "2024-01-01T08:00:00Z",
    },
  ];

  beforeEach(() => {
    setActivePinia(createPinia());
    vi.clearAllMocks();
    store = useTicketsStore();
  });

  afterEach(() => {
    vi.resetAllMocks();
  });

  describe("Initial State", () => {
    it("starts with empty tickets", () => {
      expect(store.tickets).toEqual([]);
    });

    it("starts with null currentTicket", () => {
      expect(store.currentTicket).toBeNull();
    });

    it("starts with isLoading false", () => {
      expect(store.isLoading).toBe(false);
    });

    it("starts with null error", () => {
      expect(store.error).toBeNull();
    });

    it("starts with isSaving false", () => {
      expect(store.isSaving).toBe(false);
    });
  });

  describe("Constants", () => {
    it("has TICKET_STATUSES defined", () => {
      expect(store.TICKET_STATUSES).toBeDefined();
      expect(store.TICKET_STATUSES.open).toBeDefined();
      expect(store.TICKET_STATUSES.closed).toBeDefined();
    });

    it("has TICKET_PRIORITIES defined", () => {
      expect(store.TICKET_PRIORITIES).toBeDefined();
      expect(store.TICKET_PRIORITIES.low).toBeDefined();
      expect(store.TICKET_PRIORITIES.high).toBeDefined();
    });
  });

  describe("Getters", () => {
    beforeEach(() => {
      store.tickets = mockTickets;
    });

    it("returns open tickets", () => {
      expect(store.openTickets).toHaveLength(1);
      expect(store.openTickets[0].id).toBe(1);
    });

    it("returns in progress tickets", () => {
      expect(store.inProgressTickets).toHaveLength(1);
      expect(store.inProgressTickets[0].id).toBe(2);
    });

    it("returns resolved tickets", () => {
      expect(store.resolvedTickets).toHaveLength(1);
      expect(store.resolvedTickets[0].id).toBe(3);
    });

    it("returns closed tickets", () => {
      expect(store.closedTickets).toHaveLength(1);
      expect(store.closedTickets[0].id).toBe(4);
    });

    it("returns correct ticketCount", () => {
      expect(store.ticketCount).toBe(4);
    });

    it("returns correct openTicketCount", () => {
      expect(store.openTicketCount).toBe(1);
    });

    it("hasTickets returns true", () => {
      expect(store.hasTickets).toBe(true);
    });

    it("hasTickets returns false when empty", () => {
      store.tickets = [];
      expect(store.hasTickets).toBe(false);
    });
  });

  describe("fetchTickets", () => {
    it("sets isLoading to true during fetch", async () => {
      api.get.mockImplementation(() => new Promise(() => {}));
      store.fetchTickets();
      expect(store.isLoading).toBe(true);
    });

    it("fetches tickets successfully", async () => {
      api.get.mockResolvedValue({
        data: { success: true, tickets: mockTickets },
      });

      const result = await store.fetchTickets();

      expect(api.get).toHaveBeenCalledWith("/customer/tickets", { params: {} });
      expect(result.success).toBe(true);
      expect(store.tickets).toEqual(mockTickets);
    });

    it("filters tickets by status", async () => {
      api.get.mockResolvedValue({
        data: { success: true, tickets: [mockTickets[0]] },
      });

      await store.fetchTickets({ status: "open" });

      expect(api.get).toHaveBeenCalledWith("/customer/tickets", {
        params: { status: "open" },
      });
    });

    it("handles fetch error", async () => {
      api.get.mockRejectedValue({
        response: { data: { message: "Failed to fetch tickets" } },
      });

      const result = await store.fetchTickets();

      expect(result.success).toBe(false);
      expect(store.error).toBe("Failed to fetch tickets");
    });
  });

  describe("fetchTicket", () => {
    it("fetches single ticket successfully", async () => {
      const ticketWithReplies = {
        ...mockTickets[0],
        replies: [
          { id: 1, message: "Reply 1", is_staff: true },
          { id: 2, message: "Reply 2", is_staff: false },
        ],
      };

      api.get.mockResolvedValue({
        data: { success: true, ticket: ticketWithReplies },
      });

      const result = await store.fetchTicket(1);

      expect(api.get).toHaveBeenCalledWith("/customer/tickets/1");
      expect(result.success).toBe(true);
      expect(store.currentTicket).toEqual(ticketWithReplies);
    });

    it("handles ticket not found", async () => {
      api.get.mockRejectedValue({
        response: { data: { message: "Ticket not found" } },
      });

      const result = await store.fetchTicket(999);

      expect(result.success).toBe(false);
      expect(store.error).toBe("Ticket not found");
    });
  });

  describe("createTicket", () => {
    it("sets isSaving to true during create", async () => {
      api.post.mockImplementation(() => new Promise(() => {}));
      store.createTicket({ subject: "Test", message: "Test message" });
      expect(store.isSaving).toBe(true);
    });

    it("creates ticket successfully", async () => {
      const newTicket = {
        id: 5,
        subject: "New Issue",
        message: "Help needed",
        status: "open",
        priority: "medium",
        created_at: "2024-01-20T10:00:00Z",
      };

      api.post.mockResolvedValue({
        data: { success: true, ticket: newTicket },
      });

      const result = await store.createTicket({
        subject: "New Issue",
        message: "Help needed",
        priority: "medium",
      });

      expect(api.post).toHaveBeenCalledWith("/customer/tickets", {
        subject: "New Issue",
        message: "Help needed",
        priority: "medium",
        order_id: null,
      });
      expect(result.success).toBe(true);
      expect(store.tickets[0]).toEqual(newTicket);
      expect(store.currentTicket).toEqual(newTicket);
    });

    it("creates ticket with order reference", async () => {
      api.post.mockResolvedValue({
        data: { success: true, ticket: { id: 5 } },
      });

      await store.createTicket({
        subject: "Order Issue",
        message: "Problem with order",
        orderId: 123,
      });

      expect(api.post).toHaveBeenCalledWith("/customer/tickets", {
        subject: "Order Issue",
        message: "Problem with order",
        priority: "medium",
        order_id: 123,
      });
    });

    it("handles validation error", async () => {
      api.post.mockRejectedValue({
        response: {
          data: {
            message: "Validation error",
            errors: { subject: ["Subject is required"] },
          },
        },
      });

      const result = await store.createTicket({ message: "No subject" });

      expect(result.success).toBe(false);
      expect(result.errors.subject).toEqual(["Subject is required"]);
    });
  });

  describe("replyToTicket", () => {
    beforeEach(() => {
      store.currentTicket = { id: 1, replies: [], status: "open" };
    });

    it("sends reply successfully", async () => {
      const newReply = {
        id: 1,
        message: "Thanks for the update",
        is_staff: false,
        created_at: "2024-01-20T11:00:00Z",
      };

      api.post.mockResolvedValue({
        data: { success: true, reply: newReply },
      });

      const result = await store.replyToTicket(1, "Thanks for the update");

      expect(api.post).toHaveBeenCalledWith("/customer/tickets/1/reply", {
        message: "Thanks for the update",
      });
      expect(result.success).toBe(true);
      expect(store.currentTicket.replies).toContainEqual(newReply);
    });

    it("handles reply to closed ticket error", async () => {
      api.post.mockRejectedValue({
        response: { data: { message: "Cannot reply to closed ticket" } },
      });

      const result = await store.replyToTicket(1, "Please help");

      expect(result.success).toBe(false);
      expect(result.message).toBe("Cannot reply to closed ticket");
    });
  });

  describe("getStatusConfig", () => {
    it("returns config for open status", () => {
      const config = store.getStatusConfig("open");
      expect(config.label).toBe("Open");
      expect(config.color).toBe("green");
    });

    it("returns config for closed status", () => {
      const config = store.getStatusConfig("closed");
      expect(config.label).toBe("Closed");
      expect(config.color).toBe("gray");
    });

    it("returns default for unknown status", () => {
      const config = store.getStatusConfig("unknown");
      expect(config.label).toBe("unknown");
    });
  });

  describe("getPriorityConfig", () => {
    it("returns config for high priority", () => {
      const config = store.getPriorityConfig("high");
      expect(config.label).toBe("High");
      expect(config.color).toBe("red");
    });

    it("returns config for low priority", () => {
      const config = store.getPriorityConfig("low");
      expect(config.label).toBe("Low");
      expect(config.color).toBe("gray");
    });
  });

  describe("clearCurrentTicket", () => {
    it("clears current ticket", () => {
      store.currentTicket = mockTickets[0];

      store.clearCurrentTicket();

      expect(store.currentTicket).toBeNull();
    });
  });

  describe("clearTickets", () => {
    it("clears all ticket state", () => {
      store.tickets = mockTickets;
      store.currentTicket = mockTickets[0];
      store.error = "Error";
      store.saveError = "Save error";

      store.clearTickets();

      expect(store.tickets).toEqual([]);
      expect(store.currentTicket).toBeNull();
      expect(store.error).toBeNull();
      expect(store.saveError).toBeNull();
    });
  });

  describe("clearErrors", () => {
    it("clears all errors", () => {
      store.error = "Error 1";
      store.saveError = "Error 2";

      store.clearErrors();

      expect(store.error).toBeNull();
      expect(store.saveError).toBeNull();
    });
  });
});
