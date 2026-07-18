import apiClient from "@/lib/api";
import { Payment } from "@/lib/types";

export const paymentService = {
  getAll: async (): Promise<Payment[]> => {
    return await apiClient.get("/payments");
  },

  getByTenant: async (tenantId: number): Promise<Payment[]> => {
    return await apiClient.get(`/payments?tenantId=${tenantId}`);
  },

  getById: async (id: number): Promise<Payment> => {
    return await apiClient.get(`/payments/${id}`);
  },

  create: async (data: Omit<Payment, "id" | "createdAt" | "updatedAt">) => {
    return await apiClient.post("/payments", data);
  },

  updateStatus: async (
    id: number,
    status: "pending" | "completed" | "failed" | "overdue"
  ) => {
    return await apiClient.patch(`/payments/${id}`, { status });
  },

  recordPayment: async (
    id: number,
    data: { transactionReference: string; paymentMethod: string }
  ) => {
    return await apiClient.post(`/payments/${id}/record`, data);
  },

  getPendingPayments: async (): Promise<Payment[]> => {
    return await apiClient.get("/payments/pending");
  },

  sendReminderSMS: async (paymentId: number) => {
    return await apiClient.post(`/payments/${paymentId}/send-reminder`);
  },
};
