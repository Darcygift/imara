/**
 * Payment Gateway Orchestration Service
 * Coordinates between MTN MoMo, SMS, and payment tracking
 */

import { momoService } from "./mtnMomo";
import { smsService } from "./sms";

export interface PaymentRequest {
  tenantId: number;
  tenantName: string;
  tenantPhone: string;
  propertyId: number;
  amount: number;
  dueDate: string;
  externalId: string;
}

export interface PaymentCollectionResponse {
  success: boolean;
  transactionId?: string;
  smsMessageId?: string;
  status?: string;
  message?: string;
  error?: string;
}

class PaymentGateway {
  /**
   * Request payment from tenant
   */
  async requestPayment(
    request: PaymentRequest
  ): Promise<PaymentCollectionResponse> {
    try {
      console.log("[v0] Initiating payment request for tenant:", request.tenantName);

      // Step 1: Request money via MTN MoMo
      const momoResponse = await momoService.requestMoney({
        phoneNumber: request.tenantPhone,
        amount: request.amount,
        externalId: request.externalId,
        payerMessage: `Rent payment for property ${request.propertyId}`,
        payeeNote: `Rent due on ${request.dueDate}`,
      });

      if (!momoResponse.success) {
        return {
          success: false,
          error: momoResponse.error,
          message: momoResponse.message,
        };
      }

      // Step 2: Send SMS reminder
      const smsResponse = await smsService.sendPaymentReminder(
        request.tenantPhone,
        request.tenantName,
        request.amount,
        request.dueDate
      );

      return {
        success: true,
        transactionId: momoResponse.transactionId,
        smsMessageId: smsResponse.messageId,
        status: momoResponse.status,
        message: "Payment request sent successfully",
      };
    } catch (error) {
      console.error("[v0] Payment request error:", error);
      return {
        success: false,
        error: "Failed to process payment request",
        message: (error as Error).message,
      };
    }
  }

  /**
   * Record payment receipt and send confirmation
   */
  async recordPaymentReceipt(
    tenantId: number,
    tenantName: string,
    tenantPhone: string,
    amount: number,
    transactionId: string
  ): Promise<PaymentCollectionResponse> {
    try {
      console.log(
        "[v0] Recording payment receipt for tenant:",
        tenantName
      );

      // Step 1: Verify transaction with MoMo (would call actual API in production)
      const transactionStatus = await momoService.getTransactionStatus(
        transactionId
      );

      if (transactionStatus.status !== "SUCCESSFUL") {
        return {
          success: false,
          error: "Transaction verification failed",
        };
      }

      // Step 2: Send SMS confirmation
      const smsResponse = await smsService.sendPaymentConfirmation(
        tenantPhone,
        tenantName,
        amount,
        new Date().toLocaleDateString()
      );

      return {
        success: true,
        transactionId,
        smsMessageId: smsResponse.messageId,
        status: "COMPLETED",
        message: "Payment recorded and confirmation sent",
      };
    } catch (error) {
      console.error("[v0] Payment recording error:", error);
      return {
        success: false,
        error: "Failed to record payment",
        message: (error as Error).message,
      };
    }
  }

  /**
   * Send bulk payment reminders
   */
  async sendBulkReminders(
    tenants: Array<{
      name: string;
      phone: string;
      amount: number;
      dueDate: string;
    }>
  ): Promise<{
    sent: number;
    failed: number;
    results: PaymentCollectionResponse[];
  }> {
    const results: PaymentCollectionResponse[] = [];
    let sent = 0;
    let failed = 0;

    for (const tenant of tenants) {
      try {
        const smsResponse = await smsService.sendPaymentReminder(
          tenant.phone,
          tenant.name,
          tenant.amount,
          tenant.dueDate
        );

        if (smsResponse.success) {
          sent++;
          results.push({
            success: true,
            smsMessageId: smsResponse.messageId,
            message: `Reminder sent to ${tenant.name}`,
          });
        } else {
          failed++;
          results.push({
            success: false,
            error: `Failed to send reminder to ${tenant.name}`,
          });
        }
      } catch (error) {
        failed++;
        results.push({
          success: false,
          error: `Error sending reminder to ${tenant.name}`,
        });
      }
    }

    return { sent, failed, results };
  }

  /**
   * Get payment statistics
   */
  async getPaymentStats(): Promise<{
    totalRequested: number;
    totalCollected: number;
    collectionRate: number;
    pendingAmount: number;
  }> {
    // In production, this would fetch real data from the database
    return {
      totalRequested: 85000,
      totalCollected: 72000,
      collectionRate: 84.7,
      pendingAmount: 13000,
    };
  }
}

export const paymentGateway = new PaymentGateway();
