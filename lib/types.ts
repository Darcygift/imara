export interface Landlord {
  id: number;
  name: string;
  email: string;
  phone: string;
  address?: string;
  city?: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface Property {
  id: number;
  landlordId: number;
  name: string;
  address: string;
  city: string;
  description?: string;
  propertyType: "apartment" | "house" | "commercial" | "land";
  numberOfUnits: number;
  createdAt: Date;
  updatedAt: Date;
}

export interface Unit {
  id: number;
  propertyId: number;
  unitNumber: string;
  rentalAmount: string;
  description?: string;
  isOccupied: boolean;
  createdAt: Date;
  updatedAt: Date;
}

export interface Tenant {
  id: number;
  unitId: number;
  firstName: string;
  lastName: string;
  email?: string;
  phone: string;
  idNumber?: string;
  leaseStartDate?: Date;
  leaseEndDate?: Date;
  emergencyContact?: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface Payment {
  id: number;
  tenantId: number;
  unitId: number;
  amount: string;
  dueDate: Date;
  paidDate?: Date;
  status: "pending" | "completed" | "failed" | "overdue";
  paymentMethod?: string;
  transactionReference?: string;
  notes?: string;
  createdAt: Date;
  updatedAt: Date;
}

export interface ApiResponse<T> {
  success: boolean;
  message?: string;
  data?: T;
  error?: string;
}

export interface AuthResponse {
  token: string;
  landlord: Landlord;
}

export interface DashboardStats {
  totalProperties: number;
  totalTenants: number;
  totalUnits: number;
  occupiedUnits: number;
  pendingPayments: number;
  totalRentCollected: number;
  outstandingAmount: number;
}
