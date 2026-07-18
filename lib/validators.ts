import { z } from "zod";

// Auth Schemas
export const registerSchema = z.object({
  name: z.string().min(2, "Name must be at least 2 characters"),
  email: z.string().email("Invalid email address"),
  phone: z.string().min(10, "Invalid phone number"),
  password: z.string().min(8, "Password must be at least 8 characters"),
});

export const loginSchema = z.object({
  email: z.string().email("Invalid email address"),
  password: z.string().min(1, "Password is required"),
});

// Property Schemas
export const propertySchema = z.object({
  name: z.string().min(2, "Property name is required"),
  address: z.string().min(5, "Address is required"),
  city: z.string().min(2, "City is required"),
  description: z.string().optional(),
  propertyType: z.enum(["apartment", "house", "commercial", "land"]),
  numberOfUnits: z.number().min(1, "At least one unit required"),
});

export const unitSchema = z.object({
  unitNumber: z.string().min(1, "Unit number is required"),
  rentalAmount: z.string().regex(/^\d+(\.\d{2})?$/, "Invalid amount"),
  description: z.string().optional(),
});

// Tenant Schemas
export const tenantSchema = z.object({
  firstName: z.string().min(2, "First name is required"),
  lastName: z.string().min(2, "Last name is required"),
  email: z.string().email("Invalid email").optional().or(z.literal("")),
  phone: z.string().min(10, "Invalid phone number"),
  idNumber: z.string().optional(),
  emergencyContact: z.string().optional(),
});

// Payment Schemas
export const paymentSchema = z.object({
  amount: z.string().regex(/^\d+(\.\d{2})?$/, "Invalid amount"),
  dueDate: z.string().refine((date) => !isNaN(Date.parse(date)), {
    message: "Invalid date",
  }),
  notes: z.string().optional(),
});

// Type Exports
export type RegisterInput = z.infer<typeof registerSchema>;
export type LoginInput = z.infer<typeof loginSchema>;
export type PropertyInput = z.infer<typeof propertySchema>;
export type UnitInput = z.infer<typeof unitSchema>;
export type TenantInput = z.infer<typeof tenantSchema>;
export type PaymentInput = z.infer<typeof paymentSchema>;

// Helper Functions
export const formatCurrency = (amount: number | string): string => {
  const num = typeof amount === "string" ? parseFloat(amount) : amount;
  return new Intl.NumberFormat("en-US", {
    style: "currency",
    currency: "USD",
  }).format(num);
};

export const formatPhone = (phone: string): string => {
  const cleaned = phone.replace(/\D/g, "");
  if (cleaned.length !== 10) return phone;
  return `(${cleaned.slice(0, 3)}) ${cleaned.slice(3, 6)}-${cleaned.slice(6)}`;
};

export const formatDate = (date: string | Date): string => {
  const d = new Date(date);
  return new Intl.DateTimeFormat("en-US", {
    year: "numeric",
    month: "short",
    day: "numeric",
  }).format(d);
};

export const getPaymentStatusColor = (
  status: "pending" | "completed" | "failed" | "overdue"
): string => {
  switch (status) {
    case "completed":
      return "text-green-600";
    case "pending":
      return "text-yellow-600";
    case "overdue":
      return "text-red-600";
    case "failed":
      return "text-red-600";
    default:
      return "text-gray-600";
  }
};

export const daysOverdue = (dueDate: Date): number => {
  const now = new Date();
  const due = new Date(dueDate);
  const diff = now.getTime() - due.getTime();
  return Math.floor(diff / (1000 * 60 * 60 * 24));
};
