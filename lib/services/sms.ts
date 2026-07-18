/**
 * SMS Notification Service
 * Handles sending SMS notifications to landlords and tenants
 */

interface SMSRequest {
  recipientPhone: string;
  message: string;
  type: "payment_reminder" | "payment_received" | "lease_expiring" | "alert";
}

interface SMSResponse {
  success: boolean;
  messageId?: string;
  status?: string;
  error?: string;
}

class SMSService {
  private apiKey: string;
  private senderName: string = "SmartRent";

  constructor() {
    this.apiKey = process.env.SMS_API_KEY || "";
  }

  /**
   * Send SMS notification
   */
  async sendSMS(request: SMSRequest): Promise<SMSResponse> {
    try {
      if (!this.apiKey) {
        console.warn(
          "[v0] SMS API key not configured. Running in demo mode."
        );
        return this.simulateSMS(request);
      }

      // In production, this would integrate with an SMS provider like:
      // - Africa's Talking
      // - Twilio
      // - Nexmo/Vonage
      // - Infobip
      return this.simulateSMS(request);
    } catch (error) {
      console.error("[v0] SMS sending error:", error);
      return {
        success: false,
        error: "Failed to send SMS",
      };
    }
  }

  /**
   * Send payment reminder SMS
   */
  async sendPaymentReminder(
    recipientPhone: string,
    tenantName: string,
    amount: number,
    dueDate: string
  ): Promise<SMSResponse> {
    const message = `Dear ${tenantName}, your rent of UGX ${amount.toLocaleString()} is due on ${dueDate}. Please pay on time. Reply PAID when you've made payment. - SmartRent`;

    return this.sendSMS({
      recipientPhone,
      message,
      type: "payment_reminder",
    });
  }

  /**
   * Send payment received confirmation
   */
  async sendPaymentConfirmation(
    recipientPhone: string,
    tenantName: string,
    amount: number,
    date: string
  ): Promise<SMSResponse> {
    const message = `Dear ${tenantName}, we've received your payment of UGX ${amount.toLocaleString()} on ${date}. Your rent is now settled. Thank you! - SmartRent`;

    return this.sendSMS({
      recipientPhone,
      message,
      type: "payment_received",
    });
  }

  /**
   * Send lease expiration warning
   */
  async sendLeaseExpiringWarning(
    recipientPhone: string,
    tenantName: string,
    expiryDate: string
  ): Promise<SMSResponse> {
    const message = `Dear ${tenantName}, your lease agreement expires on ${expiryDate}. Please contact us to renew or discuss your lease terms. - SmartRent`;

    return this.sendSMS({
      recipientPhone,
      message,
      type: "lease_expiring",
    });
  }

  /**
   * Send custom alert
   */
  async sendAlert(recipientPhone: string, alertMessage: string): Promise<SMSResponse> {
    return this.sendSMS({
      recipientPhone,
      message: alertMessage,
      type: "alert",
    });
  }

  /**
   * Simulate SMS sending for demo mode
   */
  private simulateSMS(request: SMSRequest): SMSResponse {
    const messageId = `SMS_${Date.now()}_${Math.random().toString(36).substring(7).toUpperCase()}`;

    console.log("[v0] SMS Sent (Simulated):", {
      to: request.recipientPhone,
      type: request.type,
      messageId,
      message: request.message.substring(0, 50) + "...",
    });

    return {
      success: true,
      messageId,
      status: "SENT",
    };
  }

  /**
   * Format phone number to international format
   */
  formatPhoneNumber(phone: string): string {
    // Remove any non-digit characters
    const cleaned = phone.replace(/\D/g, "");

    // If starts with country code, return as is
    if (cleaned.startsWith("256")) {
      return `+${cleaned}`;
    }

    // Assume Uganda country code if not provided
    if (cleaned.startsWith("0")) {
      return `+256${cleaned.substring(1)}`;
    }

    return `+256${cleaned}`;
  }
}

export const smsService = new SMSService();
