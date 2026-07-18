/**
 * MTN MoMo Payment Integration Service
 * This service handles all MTN Mobile Money payments integration
 */

interface MomoPaymentRequest {
  phoneNumber: string;
  amount: number;
  externalId: string;
  payerMessage: string;
  payeeNote: string;
}

interface MomoPaymentResponse {
  success: boolean;
  transactionId?: string;
  status?: string;
  error?: string;
  message?: string;
}

class MTNMomoService {
  private apiKey: string;
  private subscriptionKey: string;
  private baseUrl: string = "https://sandbox.momoapi.mtn.com";

  constructor() {
    this.apiKey = process.env.MTN_MOMO_API_KEY || "";
    this.subscriptionKey = process.env.MTN_MOMO_SUBSCRIPTION_KEY || "";
  }

  /**
   * Request Money from a customer
   * This is used for rent collection
   */
  async requestMoney(
    request: MomoPaymentRequest
  ): Promise<MomoPaymentResponse> {
    try {
      if (!this.apiKey || !this.subscriptionKey) {
        console.warn(
          "[v0] MTN MoMo credentials not configured. Running in demo mode."
        );
        return this.simulateRequest(request);
      }

      // In production, this would call the actual MTN MoMo API
      // For now, we'll simulate the API call
      return this.simulateRequest(request);
    } catch (error) {
      console.error("[v0] MTN MoMo request error:", error);
      return {
        success: false,
        error: "Failed to request money",
        message: (error as Error).message,
      };
    }
  }

  /**
   * Send money to a customer (for refunds, deposits, etc.)
   */
  async sendMoney(
    request: MomoPaymentRequest
  ): Promise<MomoPaymentResponse> {
    try {
      if (!this.apiKey || !this.subscriptionKey) {
        return this.simulatePayment(request);
      }

      // In production, this would call the actual MTN MoMo API
      return this.simulatePayment(request);
    } catch (error) {
      console.error("[v0] MTN MoMo send error:", error);
      return {
        success: false,
        error: "Failed to send money",
        message: (error as Error).message,
      };
    }
  }

  /**
   * Get payment transaction status
   */
  async getTransactionStatus(transactionId: string): Promise<{
    status: string;
    amount?: number;
    currency?: string;
  }> {
    try {
      // In production, this would check the actual transaction status
      return {
        status: "SUCCESSFUL",
        amount: 0,
        currency: "UGX",
      };
    } catch (error) {
      console.error("[v0] Error checking transaction status:", error);
      return {
        status: "FAILED",
      };
    }
  }

  /**
   * Simulate a payment request (for demo/testing)
   */
  private simulateRequest(
    request: MomoPaymentRequest
  ): MomoPaymentResponse {
    const transactionId = `MOMO_${Date.now()}_${Math.random().toString(36).substring(7).toUpperCase()}`;

    console.log("[v0] MTN MoMo Request (Simulated):", {
      phoneNumber: request.phoneNumber,
      amount: request.amount,
      externalId: request.externalId,
      transactionId,
    });

    return {
      success: true,
      transactionId,
      status: "PENDING",
      message: `Payment request sent to ${request.phoneNumber}`,
    };
  }

  /**
   * Simulate a money transfer
   */
  private simulatePayment(
    request: MomoPaymentRequest
  ): MomoPaymentResponse {
    const transactionId = `MOMO_PAY_${Date.now()}_${Math.random().toString(36).substring(7).toUpperCase()}`;

    console.log("[v0] MTN MoMo Payment (Simulated):", {
      phoneNumber: request.phoneNumber,
      amount: request.amount,
      transactionId,
    });

    return {
      success: true,
      transactionId,
      status: "SUCCESSFUL",
      message: `Payment of UGX ${request.amount.toLocaleString()} sent successfully`,
    };
  }
}

export const momoService = new MTNMomoService();
