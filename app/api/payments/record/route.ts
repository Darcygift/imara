import { NextRequest, NextResponse } from "next/server";

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();

    // Validation
    if (!body.paymentId || !body.paidDate || !body.paymentMethod) {
      return NextResponse.json(
        {
          success: false,
          error: "Missing required fields: paymentId, paidDate, paymentMethod",
        },
        { status: 400 }
      );
    }

    // In production, this would:
    // 1. Update the payment status in database
    // 2. Send SMS/Email notification to tenant
    // 3. Log transaction
    // 4. Trigger MTN MoMo integration if needed

    console.log("[v0] Payment recorded:", {
      paymentId: body.paymentId,
      paidDate: body.paidDate,
      paymentMethod: body.paymentMethod,
      transactionId: body.transactionId,
    });

    return NextResponse.json(
      {
        success: true,
        message: "Payment recorded successfully",
        data: {
          paymentId: body.paymentId,
          status: "completed",
          recordedAt: new Date().toISOString(),
        },
      },
      { status: 200 }
    );
  } catch (error) {
    console.error("[v0] Error recording payment:", error);
    return NextResponse.json(
      {
        success: false,
        error: "Failed to record payment",
      },
      { status: 500 }
    );
  }
}
