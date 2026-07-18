import { NextRequest, NextResponse } from "next/server";

// Mock database
const paymentsDB: any[] = [
  {
    id: 1,
    tenantId: 1,
    propertyId: 1,
    amount: 1200,
    dueDate: "2024-08-01",
    paidDate: "2024-07-28",
    status: "completed",
    paymentMethod: "bank_transfer",
    reference: "PAY001",
  },
];

export async function GET(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  const paymentId = parseInt(params.id);
  const payment = paymentsDB.find((p) => p.id === paymentId);

  if (!payment) {
    return NextResponse.json(
      {
        success: false,
        error: "Payment not found",
      },
      { status: 404 }
    );
  }

  return NextResponse.json({
    success: true,
    data: payment,
  });
}

export async function PATCH(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const paymentId = parseInt(params.id);
    const paymentIndex = paymentsDB.findIndex((p) => p.id === paymentId);

    if (paymentIndex === -1) {
      return NextResponse.json(
        {
          success: false,
          error: "Payment not found",
        },
        { status: 404 }
      );
    }

    const body = await request.json();

    // Update payment record
    const updatedPayment = {
      ...paymentsDB[paymentIndex],
      ...body,
      id: paymentId, // Ensure ID doesn't change
    };

    paymentsDB[paymentIndex] = updatedPayment;

    return NextResponse.json({
      success: true,
      message: "Payment updated successfully",
      data: updatedPayment,
    });
  } catch (error) {
    return NextResponse.json(
      {
        success: false,
        error: "Failed to update payment",
      },
      { status: 500 }
    );
  }
}

export async function DELETE(
  request: NextRequest,
  { params }: { params: { id: string } }
) {
  try {
    const paymentId = parseInt(params.id);
    const paymentIndex = paymentsDB.findIndex((p) => p.id === paymentId);

    if (paymentIndex === -1) {
      return NextResponse.json(
        {
          success: false,
          error: "Payment not found",
        },
        { status: 404 }
      );
    }

    paymentsDB.splice(paymentIndex, 1);

    return NextResponse.json({
      success: true,
      message: "Payment deleted successfully",
    });
  } catch (error) {
    return NextResponse.json(
      {
        success: false,
        error: "Failed to delete payment",
      },
      { status: 500 }
    );
  }
}
