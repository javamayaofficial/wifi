<?php

namespace App\Http\Middleware;

use App\Models\Customer;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PortalAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $id = $request->session()->get('portal_customer_id');

        if (! $id || ! ($customer = Customer::with('plan')->find($id))) {
            return redirect('/portal/login');
        }

        // Bagikan ke controller & view tanpa query berulang.
        $request->attributes->set('portal_customer', $customer);
        view()->share('portalCustomer', $customer);

        return $next($request);
    }
}
