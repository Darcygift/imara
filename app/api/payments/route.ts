import { NextRequest, NextResponse } from "next/server";

// Mock database - in production, this would connect to a real database
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
  {
    id: 2,
    tenantId: 2,
    propertyId: 1,
    amount: 1500,
    dueDate: "2024-08-01",
    paidDate: null,
    status: "pending",
    paymentMethod: null,
    reference: null,
  },
];

export async function GET(request: NextRequest) {
  const { searchParams } = new URL(request.url);
  const status = searchParams.get("status");
  const tenantId = searchParams.get("tenantId");
  const propertyId = searchParams.get("propertyId");

  let filtered = paymentsDB;

  if (status) {
    filtered = filtered.filter((p) => p.status === status);
  }
  if (tenantId) {
    filtered = filtered.filter((p) => p.tenantId === parseInt(tenantId));
  }
  if (propertyId) {
    filtered = filtered.filter((p) => p.propertyId === parseInt(propertyId));
  }

  return NextResponse.json({
    success: true,
    data: filtered,
  });
}

export async function POST(request: NextRequest) {
  try {
    const body = await request.json();

    // Validation
    if (!body.tenantId || !body.propertyId || !body.amount || !body.dueDate) {
      return NextResponse.json(
        {
          success: false,
          error: "Missing required fields",
        },
        { status: 400 }
      );
    }

    const newPayment = {
      id: paymentsDB.length + 1,
      ...body,
      paidDate: null,
      status: "pending",
      reference: `PAY${String(paymentsDB.length + 1).padStart(3, "0")}`,
    };

    paymentsDB.push(newPayment);

    return NextResponse.json(
      {
        success: true,
        message: "Payment record created successfully",
        data: newPayment,
      },
      { status: 201 }
    );
  } catch (error) {
    return NextResponse.json(
      {
        success: false,
        error: "Failed to create payment record",
      },
      { status: 500 }
    );
  }
}
