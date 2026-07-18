import apiClient from "@/lib/api";
import { Property, Unit } from "@/lib/types";

export const propertyService = {
  getAll: async (): Promise<Property[]> => {
    return await apiClient.get("/properties");
  },

  getById: async (id: number): Promise<Property> => {
    return await apiClient.get(`/properties/${id}`);
  },

  create: async (data: Omit<Property, "id" | "createdAt" | "updatedAt">) => {
    return await apiClient.post("/properties", data);
  },

  update: async (
    id: number,
    data: Partial<Omit<Property, "id" | "createdAt" | "updatedAt">>
  ) => {
    return await apiClient.put(`/properties/${id}`, data);
  },

  delete: async (id: number) => {
    return await apiClient.delete(`/properties/${id}`);
  },

  getUnits: async (propertyId: number): Promise<Unit[]> => {
    return await apiClient.get(`/properties/${propertyId}/units`);
  },

  createUnit: async (
    propertyId: number,
    data: Omit<Unit, "id" | "propertyId" | "createdAt" | "updatedAt">
  ) => {
    return await apiClient.post(`/properties/${propertyId}/units`, data);
  },
};
