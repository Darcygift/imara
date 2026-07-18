import apiClient from "@/lib/api";
import { AuthResponse, Landlord } from "@/lib/types";

export const authService = {
  register: async (
    email: string,
    password: string,
    name: string,
    phone: string
  ): Promise<AuthResponse> => {
    const response = await apiClient.post("/auth/register", {
      email,
      password,
      name,
      phone,
    });
    if (response.token) {
      localStorage.setItem("authToken", response.token);
    }
    return response;
  },

  login: async (email: string, password: string): Promise<AuthResponse> => {
    const response = await apiClient.post("/auth/login", {
      email,
      password,
    });
    if (response.token) {
      localStorage.setItem("authToken", response.token);
    }
    return response;
  },

  logout: () => {
    localStorage.removeItem("authToken");
  },

  getCurrentUser: async (): Promise<Landlord> => {
    return await apiClient.get("/auth/me");
  },

  updateProfile: async (data: Partial<Landlord>): Promise<Landlord> => {
    return await apiClient.put("/auth/profile", data);
  },
};
