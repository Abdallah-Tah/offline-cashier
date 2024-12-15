<?php

namespace AMohamed\OfflineCashier\Http\Controllers;

use AMohamed\OfflineCashier\Models\Plan;
use AMohamed\OfflineCashier\Models\Invoice;
use AMohamed\OfflineCashier\Facades\OfflineCashier;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SubscriptionController extends Controller
{
    /**
     * Display the user's current subscription.
     */
    public function index(Request $request)
    {
        return View::make('offline-cashier::subscriptions.index', [
            'subscription' => $request->user()->subscription(),
            'paymentMethods' => OfflineCashier::getPaymentMethods(),
        ]);
    }

    /**
     * Display available plans.
     */
    public function plans()
    {
        return View::make('offline-cashier::subscriptions.plans', [
            'plans' => OfflineCashier::getActivePlans(),
        ]);
    }

    /**
     * Subscribe to a plan.
     */
    public function subscribe(Request $request, Plan $plan)
    {
        $validated = $request->validate([
            'payment_method' => ['required', 'string'],
            'payment_reference' => ['required_if:payment_method,cash,check,bank_transfer'],
        ]);

        $subscription = OfflineCashier::createSubscription(
            $request->user(),
            $plan,
            $validated['payment_method']
        );

        if ($subscription && isset($validated['payment_reference'])) {
            $invoice = $subscription->invoices()->latest()->first();
            if ($invoice) {
                OfflineCashier::markInvoiceAsPaid($invoice, $validated['payment_reference']);
            }
        }

        return Redirect::route('subscriptions.index')
            ->with('success', 'Successfully subscribed to plan.');
    }

    /**
     * Cancel the subscription.
     */
    public function cancel(Request $request)
    {
        $request->validate([
            'immediately' => ['boolean'],
        ]);

        $immediately = $request->boolean('immediately', false);
        
        if ($request->user()->cancelSubscription($immediately)) {
            return Redirect::route('subscriptions.index')
                ->with('success', 'Subscription has been canceled.');
        }

        return Redirect::route('subscriptions.index')
            ->with('error', 'Unable to cancel subscription.');
    }

    /**
     * Resume the subscription.
     */
    public function resume(Request $request)
    {
        if ($request->user()->resumeSubscription()) {
            return Redirect::route('subscriptions.index')
                ->with('success', 'Subscription has been resumed.');
        }

        return Redirect::route('subscriptions.index')
            ->with('error', 'Unable to resume subscription.');
    }

    /**
     * Display user's invoices.
     */
    public function invoices(Request $request)
    {
        return View::make('offline-cashier::subscriptions.invoices', [
            'invoices' => $request->user()->invoices()->latest()->paginate(10),
        ]);
    }

    /**
     * Download an invoice.
     */
    public function downloadInvoice(Request $request, Invoice $invoice)
    {
        if ($request->user()->id !== $invoice->user_id) {
            abort(ResponseAlias::HTTP_FORBIDDEN);
        }

        $pdf = $invoice->generatePdf();

        return Response::make($pdf, ResponseAlias::HTTP_OK, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="' . $invoice->number . '.pdf"',
        ]);
    }
} 